<?php
/**
 * Runner de migrações automáticas (aplicadas no servidor, via .env, no deploy).
 *
 * Fluxo: eu adiciono um arquivo em `sql/migrations/NNN_descricao.sql` e faço
 * push → o deploy leva o arquivo → na primeira requisição depois, este runner
 * aplica as migrações ainda não registradas na tabela `schema_migrations`.
 * Não precisa de phpMyAdmin nem de conexão externa ao banco.
 *
 * Segurança/robustez:
 *  - guarda por arquivo de assinatura (uploads/.migrations.sig) → não bate no
 *    banco em toda requisição, só quando o conjunto de migrações muda;
 *  - trava via GET_LOCK do MySQL → nunca roda em paralelo;
 *  - registra cada arquivo aplicado em `schema_migrations` (não reaplica);
 *  - qualquer erro é logado (error_log) e NÃO quebra a página; o arquivo que
 *    falhou fica pendente e é tentado de novo na próxima requisição.
 */
class Migrator
{
    private const DIR       = APP_ROOT . '/sql/migrations';
    private const SIG_FILE  = APP_ROOT . '/uploads/.migrations.sig';
    private const LOCK_NAME = 'patrimonio_migrate';

    /** Chamado no bootstrap. Barato quando não há nada novo. */
    public static function maybeRun(): void
    {
        try {
            if (!is_dir(self::DIR)) return;
            $files = glob(self::DIR . '/*.sql') ?: [];
            sort($files);
            if (!$files) return;

            $sig = sha1(implode('|', array_map('basename', $files)));
            // Guarda barata: se a assinatura bate, tudo já foi aplicado.
            if (is_readable(self::SIG_FILE) && trim((string) @file_get_contents(self::SIG_FILE)) === $sig) {
                return;
            }

            if (self::run($files)) {
                @file_put_contents(self::SIG_FILE, $sig);
            }
        } catch (\Throwable $e) {
            error_log('[MIGRATE] erro inesperado: ' . $e->getMessage());
        }
    }

    /** Aplica as migrações pendentes. Retorna true se todas passaram. */
    private static function run(array $files): bool
    {
        $pdo = db();

        // Trava: se outro processo já está migrando, sai (ele cuida).
        $got = $pdo->query("SELECT GET_LOCK('" . self::LOCK_NAME . "', 2)")->fetchColumn();
        if ((int) $got !== 1) return false;

        try {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS schema_migrations (
                    versao      VARCHAR(255) NOT NULL PRIMARY KEY,
                    aplicada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $aplicadas = $pdo->query('SELECT versao FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $aplicadas = array_flip($aplicadas);

            $tudoOk = true;
            foreach ($files as $file) {
                $versao = basename($file);
                if (isset($aplicadas[$versao])) continue;

                try {
                    foreach (self::statements((string) file_get_contents($file)) as $sql) {
                        $pdo->exec($sql);
                    }
                    $st = $pdo->prepare('INSERT INTO schema_migrations (versao) VALUES (?)');
                    $st->execute([$versao]);
                    error_log('[MIGRATE] aplicada: ' . $versao);
                } catch (\Throwable $e) {
                    // Para na primeira falha para preservar a ordem; tenta de novo depois.
                    error_log('[MIGRATE] FALHOU em ' . $versao . ': ' . $e->getMessage());
                    $tudoOk = false;
                    break;
                }
            }
            return $tudoOk;
        } finally {
            $pdo->query("SELECT RELEASE_LOCK('" . self::LOCK_NAME . "')");
        }
    }

    /** Quebra um arquivo .sql em statements (remove comentários e divide por ';'). */
    private static function statements(string $sql): array
    {
        // Remove comentários de bloco /* ... */ e de linha -- ... (até o fim da linha).
        $sql = preg_replace('#/\*.*?\*/#s', '', $sql);
        $sql = preg_replace('/^\s*--.*$/m', '', (string) $sql);

        $out = [];
        foreach (explode(';', (string) $sql) as $trecho) {
            $trecho = trim($trecho);
            if ($trecho !== '') $out[] = $trecho;
        }
        return $out;
    }
}

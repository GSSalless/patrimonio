<?php
/**
 * Model da integração Notion — persistência do vínculo por usuário e o motor de
 * sincronização das pendências (tarefas) do sistema para o board no Notion.
 * Só ENVIO (sistema → Notion). Idempotente via `notion_tarefas_map`.
 */
class NotionIntegracao
{
    /** Vínculo do usuário (ou null se não conectado). */
    public static function doUsuario(int $usuarioId): ?array
    {
        $s = db()->prepare('SELECT * FROM notion_integracoes WHERE usuario_id = ?');
        $s->execute([$usuarioId]);
        return $s->fetch() ?: null;
    }

    public static function conectado(int $usuarioId): bool
    {
        return self::doUsuario($usuarioId) !== null;
    }

    /** Grava/atualiza o vínculo (UPSERT por usuário). */
    public static function salvar(int $usuarioId, array $d): void
    {
        db()->prepare(
            'INSERT INTO notion_integracoes
                (usuario_id, access_token, workspace_id, workspace_name, workspace_icon, bot_id, database_id, database_url)
             VALUES (:uid,:tok,:wid,:wname,:wicon,:bot,:db,:dburl)
             ON DUPLICATE KEY UPDATE
                access_token=VALUES(access_token), workspace_id=VALUES(workspace_id),
                workspace_name=VALUES(workspace_name), workspace_icon=VALUES(workspace_icon),
                bot_id=VALUES(bot_id), database_id=VALUES(database_id), database_url=VALUES(database_url)'
        )->execute([
            ':uid' => $usuarioId,
            ':tok' => $d['access_token'] ?? '',
            ':wid' => $d['workspace_id'] ?? null,
            ':wname' => $d['workspace_name'] ?? null,
            ':wicon' => $d['workspace_icon'] ?? null,
            ':bot' => $d['bot_id'] ?? null,
            ':db'  => $d['database_id'] ?? null,
            ':dburl' => $d['database_url'] ?? null,
        ]);
    }

    public static function definirDatabase(int $usuarioId, string $databaseId, ?string $url): void
    {
        db()->prepare('UPDATE notion_integracoes SET database_id=?, database_url=? WHERE usuario_id=?')
            ->execute([$databaseId, $url, $usuarioId]);
    }

    public static function marcarSync(int $usuarioId): void
    {
        db()->prepare('UPDATE notion_integracoes SET ultimo_sync = NOW() WHERE usuario_id = ?')->execute([$usuarioId]);
    }

    /** Remove o vínculo e o mapa de páginas (desvincular). */
    public static function remover(int $usuarioId): void
    {
        db()->prepare('DELETE FROM notion_tarefas_map WHERE usuario_id = ?')->execute([$usuarioId]);
        db()->prepare('DELETE FROM notion_integracoes WHERE usuario_id = ?')->execute([$usuarioId]);
    }

    // ---- Sincronização --------------------------------------------------

    /** Chave estável de uma pendência (para não duplicar no Notion). */
    private static function chave(array $a): string
    {
        $base = ($a['cliente_id'] ?? '') . '|' . ($a['categoria'] ?? '') . '|'
              . ($a['link'] ?? '') . '|' . ($a['data'] ?? '') . '|' . ($a['titulo'] ?? '');
        return substr(md5($base), 0, 40);
    }

    private static function situacao(?string $data): string
    {
        if (!$data) return 'Em dia';
        $dias = dias_ate($data);
        if ($dias < 0)  return 'Vencida';
        if ($dias <= 30) return 'Próxima';
        return 'Em dia';
    }

    private static function categoriaLabel(string $cat): string
    {
        $map = [
            'iptu' => 'IPTU', 'licenciamento' => 'Licenciamento', 'seguro' => 'Seguro',
            'contrato' => 'Contrato', 'revisao' => 'Revisão', 'investimento' => 'Investimento',
            'colaborador' => 'Colaborador', 'documento' => 'Documento',
        ];
        return $map[$cat] ?? ucfirst($cat);
    }

    /** Monta as propriedades da página Notion a partir de uma pendência. */
    private static function props(array $a): array
    {
        $titulo = trim((string) ($a['titulo'] ?? 'Pendência')) ?: 'Pendência';
        $bem    = trim((string) ($a['bem'] ?? ''));
        $cliente = trim((string) ($a['cliente_nome'] ?? ''));
        $host   = $_SERVER['HTTP_HOST'] ?? '';
        $url    = ($a['link'] ?? '') !== '' && $host ? ('https://' . $host . base_url($a['link'])) : null;

        $props = [
            'Tarefa'    => ['title' => [['text' => ['content' => mb_substr($titulo, 0, 200)]]]],
            'Categoria' => ['select' => ['name' => self::categoriaLabel((string) ($a['categoria'] ?? 'outro'))]],
            'Situação'  => ['select' => ['name' => self::situacao($a['data'] ?? null)]],
        ];
        if ($cliente !== '') $props['Cliente'] = ['rich_text' => [['text' => ['content' => mb_substr($cliente, 0, 200)]]]];
        if ($bem !== '')     $props['Detalhe'] = ['rich_text' => [['text' => ['content' => mb_substr($bem, 0, 200)]]]];
        if (!empty($a['data'])) $props['Vencimento'] = ['date' => ['start' => $a['data']]];
        if ($url) $props['Link'] = ['url' => $url];
        return $props;
    }

    /**
     * Envia as pendências para o Notion (cria as novas, atualiza as existentes).
     * @return array{criadas:int,atualizadas:int,erros:int}
     */
    public static function sincronizar(int $usuarioId, ?int $clienteId = null): array
    {
        $r = ['criadas' => 0, 'atualizadas' => 0, 'erros' => 0];
        $conn = self::doUsuario($usuarioId);
        if (!$conn || empty($conn['database_id'])) return $r;

        $token = $conn['access_token'];
        $dbId  = $conn['database_id'];

        // Mapa atual (chave => page_id) do usuário.
        $mapa = [];
        $st = db()->prepare('SELECT chave, notion_page_id FROM notion_tarefas_map WHERE usuario_id = ?');
        $st->execute([$usuarioId]);
        foreach ($st->fetchAll() as $m) $mapa[$m['chave']] = $m['notion_page_id'];

        $insMap = db()->prepare('INSERT INTO notion_tarefas_map (usuario_id, chave, notion_page_id) VALUES (?,?,?)
                                 ON DUPLICATE KEY UPDATE notion_page_id = VALUES(notion_page_id)');

        foreach (alertas_consolidado($clienteId) as $a) {
            $chave = self::chave($a);
            $props = self::props($a);
            try {
                if (isset($mapa[$chave])) {
                    NotionClient::atualizarPagina($token, $mapa[$chave], $props);
                    $r['atualizadas']++;
                } else {
                    $pg = NotionClient::criarPagina($token, $dbId, $props);
                    if (!empty($pg['id'])) {
                        $insMap->execute([$usuarioId, $chave, $pg['id']]);
                        $r['criadas']++;
                    }
                }
            } catch (\Throwable $e) {
                $r['erros']++;
            }
        }
        self::marcarSync($usuarioId);
        return $r;
    }
}

<?php
/**
 * Tratamento global de erros/exceções.
 *
 * Objetivo: nunca mais mostrar o "HTTP ERROR 500" cru da hospedagem.
 *  - Em `local` (XAMPP)      → mostra a mensagem completa + arquivo/linha + trace.
 *  - Em produção             → mostra uma página amigável (tema preto & dourado)
 *                              e grava o erro real no log do PHP (`error_log`).
 *
 * Cobre os três caminhos pelos quais um erro chega no PHP:
 *  set_exception_handler   → exceções não capturadas (ex.: PDOException de tabela ausente)
 *  set_error_handler       → warnings/notices convertidos em exceção
 *  register_shutdown_function → erros fatais (parse/memória/timeout)
 */
class ErrorHandler
{
    public static function register(): void
    {
        // Não vazar detalhes na tela quando estivermos em produção;
        // o log continua registrando tudo.
        error_reporting(E_ALL);
        ini_set('display_errors', self::isLocal() ? '1' : '0');
        ini_set('log_errors', '1');

        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /** Descobre o ambiente sem depender de constante já definida. */
    private static function isLocal(): bool
    {
        if (defined('APP_ENV')) {
            return APP_ENV === 'local';
        }
        $env = getenv('APP_ENV');
        if ($env !== false && $env !== '') {
            return $env === 'local';
        }
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return str_contains($host, 'localhost')
            || str_contains($host, '127.0.0.1')
            || str_contains($host, '.test');
    }

    /** Converte warnings/notices em ErrorException (respeita o @ do PHP). */
    public static function handleError(int $tipo, string $msg, string $arquivo = '', int $linha = 0): bool
    {
        if (!(error_reporting() & $tipo)) {
            return false; // erro suprimido com @ — deixa o PHP seguir
        }
        throw new ErrorException($msg, 0, $tipo, $arquivo, $linha);
    }

    /** Exceção não capturada. */
    public static function handleException(\Throwable $e): void
    {
        error_log('[App] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
        self::render($e);
    }

    /** Erro fatal (não passa pelos handlers acima). */
    public static function handleShutdown(): void
    {
        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            error_log('[App-fatal] ' . $e['message'] . ' em ' . $e['file'] . ':' . $e['line']);
            self::render(new ErrorException($e['message'], 0, $e['type'], $e['file'], $e['line']));
        }
    }

    /** Página de erro (amigável em produção, detalhada no local). */
    private static function render(\Throwable $e): void
    {
        // Se algo já foi enviado, evita corromper a saída.
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        // Dica específica para o caso clássico "tabela não existe em produção".
        $dica = '';
        if (self::isLocal() && str_contains($e->getMessage(), 'Base table or view not found')) {
            $dica = 'Parece que falta uma tabela no banco. Rode as migrações pendentes '
                  . '(ex.: <code>sql/update_producao_modulos_567.sql</code>) no banco deste ambiente.';
        }

        $detalhe = '';
        if (self::isLocal()) {
            $detalhe = '<div class="err-tec">'
                . '<strong>' . self::h(get_class($e)) . '</strong>: ' . self::h($e->getMessage())
                . '<br><span>' . self::h($e->getFile()) . ':' . $e->getLine() . '</span>'
                . ($dica ? '<div class="err-dica">' . $dica . '</div>' : '')
                . '<pre>' . self::h($e->getTraceAsString()) . '</pre>'
                . '</div>';
        }

        echo self::pagina($detalhe);
        exit;
    }

    /** Página 404 amigável (usada pelo Router). Não é erro fatal. */
    public static function renderNotFound(string $rota = ''): void
    {
        if (!headers_sent()) {
            http_response_code(404);
            header('Content-Type: text/html; charset=utf-8');
        }
        $detalhe = self::isLocal() && $rota !== ''
            ? '<div class="err-tec">Rota não encontrada: <code>' . self::h($rota) . '</code></div>'
            : '';
        echo self::pagina($detalhe, '404', 'Página não encontrada',
            'O endereço que você tentou abrir não existe ou foi movido.');
        exit;
    }

    private static function h(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    /** HTML autocontido (não depende de header.php/CSS/DB, que podem ter falhado). */
    private static function pagina(
        string $detalhe = '',
        string $codigo = '500',
        string $titulo = 'Algo deu errado',
        string $texto = 'Não conseguimos concluir esta operação agora. Nossa equipe já foi avisada. Tente novamente em instantes.'
    ): string {
        $voltar = htmlspecialchars(($_SERVER['HTTP_REFERER'] ?? '') ?: 'javascript:history.back()', ENT_QUOTES, 'UTF-8');
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR"><head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$codigo} — {$titulo}</title>
<style>
  :root{--dourado:#d4af37;--dourado-2:#b8912f}
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(180deg,#16161a 0%,#0e0e10 100%);color:#f4f1e8;
    font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;padding:1.5rem}
  .box{max-width:520px;width:100%;text-align:center}
  .cod{font-family:Georgia,'Times New Roman',serif;font-size:5rem;font-weight:700;line-height:1;
    background:linear-gradient(90deg,var(--dourado),var(--dourado-2));-webkit-background-clip:text;
    background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:.5rem}
  h1{font-family:Georgia,serif;font-weight:600;font-size:1.5rem;margin:.2rem 0 .6rem}
  p{color:rgba(244,241,232,.7);line-height:1.6;margin:0 auto 1.6rem;max-width:400px}
  .acoes{display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap}
  a.btn{display:inline-block;padding:.65rem 1.3rem;border-radius:8px;font-size:.92rem;font-weight:600;
    text-decoration:none;transition:.15s}
  .btn-o{background:linear-gradient(180deg,#d4af37,#b8912f);color:#16141a}
  .btn-o:hover{filter:brightness(1.08)}
  .btn-g{border:1px solid rgba(212,175,55,.4);color:rgba(244,241,232,.85)}
  .btn-g:hover{background:rgba(212,175,55,.1)}
  .err-tec{margin-top:2rem;text-align:left;background:rgba(0,0,0,.35);border:1px solid rgba(212,175,55,.25);
    border-radius:8px;padding:1rem;font-size:.82rem;color:#f0d98a;overflow:auto}
  .err-tec span{color:rgba(244,241,232,.55)}
  .err-tec code{color:#f4f1e8}
  .err-dica{margin:.7rem 0;padding:.6rem .7rem;background:rgba(212,175,55,.12);border-radius:6px;color:#f4f1e8}
  .err-tec pre{margin:.7rem 0 0;white-space:pre-wrap;color:rgba(244,241,232,.6);font-size:.75rem}
</style></head><body>
  <div class="box">
    <div class="cod">{$codigo}</div>
    <h1>{$titulo}</h1>
    <p>{$texto}</p>
    <div class="acoes">
      <a class="btn btn-o" href="{$voltar}">Voltar</a>
      <a class="btn btn-g" href="/">Ir para o início</a>
    </div>
    {$detalhe}
  </div>
</body></html>
HTML;
    }
}

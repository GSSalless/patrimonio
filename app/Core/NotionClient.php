<?php
/**
 * Cliente mínimo da API do Notion (OAuth + páginas/databases) via cURL.
 * Sem biblioteca externa — mantém o projeto leve. Credenciais no .env:
 *   NOTION_CLIENT_ID, NOTION_CLIENT_SECRET, NOTION_REDIRECT (opcional).
 *
 * Fluxo: authUrl() → usuário autoriza → callback com ?code → exchangeCode()
 * devolve o access_token do workspace. Depois: search() acha páginas
 * acessíveis, createDatabase() cria o board, createPage()/updatePage() lançam
 * as tarefas. Só ENVIO (sistema → Notion).
 */
class NotionClient
{
    private const API = 'https://api.notion.com/v1';
    private const VERSION = '2022-06-28';

    public static function configurado(): bool
    {
        return (Env::get('NOTION_CLIENT_ID') ?? '') !== ''
            && (Env::get('NOTION_CLIENT_SECRET') ?? '') !== '';
    }

    public static function redirectUri(): string
    {
        $cfg = Env::get('NOTION_REDIRECT');
        if ($cfg) return $cfg;
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return 'https://' . $host . rtrim(BASE_URL, '/') . '/agenda/notion/callback';
    }

    /** URL de consentimento do Notion. */
    public static function authUrl(string $state): string
    {
        $q = http_build_query([
            'client_id'     => Env::get('NOTION_CLIENT_ID'),
            'response_type' => 'code',
            'owner'         => 'user',
            'redirect_uri'  => self::redirectUri(),
            'state'         => $state,
        ]);
        return 'https://api.notion.com/v1/oauth/authorize?' . $q;
    }

    /** Troca o code pelo access_token (Basic auth com client_id:secret). */
    public static function exchangeCode(string $code): array
    {
        $basic = base64_encode(Env::get('NOTION_CLIENT_ID') . ':' . Env::get('NOTION_CLIENT_SECRET'));
        return self::http('POST', '/oauth/token', [
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => self::redirectUri(),
        ], ['Authorization: Basic ' . $basic]);
    }

    /** Páginas que a integração pode acessar (o usuário escolhe no consentimento). */
    public static function buscarPaginas(string $token): array
    {
        $r = self::http('POST', '/search', [
            'filter' => ['property' => 'object', 'value' => 'page'],
            'page_size' => 20,
        ], self::authHeader($token));
        return $r['results'] ?? [];
    }

    /** Cria o board padrão (database) dentro de uma página. Retorna o database. */
    public static function criarDatabase(string $token, string $parentPageId): array
    {
        return self::http('POST', '/databases', [
            'parent' => ['type' => 'page_id', 'page_id' => $parentPageId],
            'title'  => [['type' => 'text', 'text' => ['content' => 'Tarefas & Pendências — CZR']]],
            'properties' => [
                'Tarefa'     => ['title' => new stdClass()],
                'Cliente'    => ['rich_text' => new stdClass()],
                'Categoria'  => ['select' => new stdClass()],
                'Vencimento' => ['date' => new stdClass()],
                'Situação'   => ['select' => ['options' => [
                    ['name' => 'Vencida',  'color' => 'red'],
                    ['name' => 'Próxima',  'color' => 'yellow'],
                    ['name' => 'Em dia',   'color' => 'green'],
                ]]],
                'Detalhe'    => ['rich_text' => new stdClass()],
                'Link'       => ['url' => new stdClass()],
            ],
        ], self::authHeader($token));
    }

    public static function criarPagina(string $token, string $databaseId, array $props): array
    {
        return self::http('POST', '/pages', [
            'parent'     => ['database_id' => $databaseId],
            'properties' => $props,
        ], self::authHeader($token));
    }

    public static function atualizarPagina(string $token, string $pageId, array $props): array
    {
        return self::http('PATCH', '/pages/' . $pageId, ['properties' => $props], self::authHeader($token));
    }

    private static function authHeader(string $token): array
    {
        return ['Authorization: Bearer ' . $token];
    }

    /** Executa a chamada HTTP e devolve o JSON decodificado (lança em erro). */
    private static function http(string $metodo, string $path, array $body, array $headers = []): array
    {
        $ch = curl_init(self::API . $path);
        $headers = array_merge($headers, [
            'Content-Type: application/json',
            'Notion-Version: ' . self::VERSION,
        ]);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $metodo,
            CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            throw new RuntimeException('Falha de conexão com o Notion: ' . $err);
        }
        $json = json_decode($resp, true) ?: [];
        if ($http < 200 || $http >= 300) {
            throw new RuntimeException('Notion respondeu ' . $http . ': ' . ($json['message'] ?? $resp));
        }
        return $json;
    }
}

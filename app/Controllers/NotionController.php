<?php
/**
 * Integração com o Notion (Módulo de automação — só admin/gestor).
 * Fluxo OAuth + criação do board padrão + sincronização das pendências.
 * Só ENVIO (sistema → Notion).
 */
class NotionController extends Controller
{
    /** GET agenda/notion/conectar — inicia o OAuth do Notion. */
    public function conectar(): void
    {
        exige_admin();
        if (!NotionClient::configurado()) {
            $this->redirect('agenda?notion=sem_config');
        }
        $state = bin2hex(random_bytes(16));
        $_SESSION['notion_state'] = $state;
        header('Location: ' . NotionClient::authUrl($state));
        exit;
    }

    /** GET agenda/notion/callback — recebe o code, salva o token e cria o board. */
    public function callback(): void
    {
        exige_admin();
        $u = usuario_logado();

        // Erro/negação do usuário no Notion.
        if (!empty($_GET['error'])) $this->redirect('agenda?notion=negado');

        // CSRF: o state tem que bater com o guardado na sessão.
        $state = $_GET['state'] ?? '';
        if (!$state || ($_SESSION['notion_state'] ?? '') !== $state) {
            $this->redirect('agenda?notion=state');
        }
        unset($_SESSION['notion_state']);

        $code = $_GET['code'] ?? '';
        if (!$code) $this->redirect('agenda?notion=sem_code');

        try {
            $tok = NotionClient::exchangeCode($code);
            NotionIntegracao::salvar((int) $u['id'], [
                'access_token'   => $tok['access_token'] ?? '',
                'workspace_id'   => $tok['workspace_id'] ?? null,
                'workspace_name' => $tok['workspace_name'] ?? null,
                'workspace_icon' => $tok['workspace_icon'] ?? null,
                'bot_id'         => $tok['bot_id'] ?? null,
            ]);

            // Cria o board padrão na primeira página que o usuário compartilhou.
            $paginas = NotionClient::buscarPaginas($tok['access_token'] ?? '');
            if (!empty($paginas[0]['id'])) {
                $db = NotionClient::criarDatabase($tok['access_token'], $paginas[0]['id']);
                if (!empty($db['id'])) {
                    NotionIntegracao::definirDatabase((int) $u['id'], $db['id'], $db['url'] ?? null);
                    NotionIntegracao::sincronizar((int) $u['id']); // sobe as pendências atuais
                    $this->redirect('agenda?notion=conectado');
                }
            }
            // Conectou, mas nenhuma página foi compartilhada com a integração.
            $this->redirect('agenda?notion=sem_pagina');
        } catch (\Throwable $e) {
            error_log('[NOTION] callback: ' . $e->getMessage());
            $this->redirect('agenda?notion=erro');
        }
    }

    /** POST agenda/notion/sincronizar — reenvia as pendências. */
    public function sincronizar(): void
    {
        exige_admin();
        $u = usuario_logado();
        try {
            $r = NotionIntegracao::sincronizar((int) $u['id']);
            $this->redirect('agenda?notion=sync&c=' . $r['criadas'] . '&a=' . $r['atualizadas'] . '&e=' . $r['erros']);
        } catch (\Throwable $e) {
            error_log('[NOTION] sync: ' . $e->getMessage());
            $this->redirect('agenda?notion=erro');
        }
    }

    /** POST agenda/notion/desvincular — remove o vínculo. */
    public function desvincular(): void
    {
        exige_admin();
        $u = usuario_logado();
        NotionIntegracao::remover((int) $u['id']);
        $this->redirect('agenda?notion=desvinculado');
    }
}

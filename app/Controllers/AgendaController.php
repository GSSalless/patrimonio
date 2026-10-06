<?php
/**
 * Agenda e Alertas (Módulo 14).
 * Reúne todos os vencimentos dos módulos (IPTU, seguros, licenciamento,
 * contratos, revisões, documentos) numa única linha do tempo.
 *
 * Escopo:
 *  - admin com cliente selecionado → agenda desse cliente
 *  - admin sem cliente             → agenda geral (todos os clientes)
 *  - cliente                       → apenas a própria agenda
 */
class AgendaController extends Controller
{
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();

        // Escopo: um cliente ou todos (modo gestor) — mesmo menu, dois modos.
        $cli        = $this->escopoCliente($usuario);
        $cliente_id = $cli['id'] ?? null;

        $alertas = alertas_consolidado($cliente_id);   // já vem em ordem cronológica
        foreach ($alertas as &$a) $a['dias'] = dias_ate($a['data']);
        unset($a);

        // Agrupamento: no modo gestor o padrão é POR CLIENTE (ordem cronológica
        // dentro de cada um — pedido da reunião de 01/10/2026); dá para alternar
        // para POR PRAZO. Com cliente selecionado, sempre por prazo.
        $agrupar = (!$cli && ($_GET['agrupar'] ?? 'cliente') !== 'prazo') ? 'cliente' : 'prazo';

        $baldes = [];
        if ($agrupar === 'cliente') {
            foreach ($alertas as $a) {
                $k = 'c' . (int) $a['cliente_id'];
                $baldes[$k] ??= ['titulo' => $a['cliente_nome'], 'cliente_id' => (int) $a['cliente_id'], 'itens' => []];
                $baldes[$k]['itens'][] = $a;
            }
            uasort($baldes, fn($x, $y) => strcasecmp($x['titulo'], $y['titulo']));
        } else {
            $baldes = [
                'vencido' => ['titulo' => 'Vencidos',         'itens' => []],
                'semana'  => ['titulo' => 'Próximos 7 dias',  'itens' => []],
                'mes'     => ['titulo' => 'Próximos 30 dias', 'itens' => []],
                'depois'  => ['titulo' => 'Mais adiante',     'itens' => []],
            ];
            foreach ($alertas as $a) {
                if ($a['dias'] < 0)       $baldes['vencido']['itens'][] = $a;
                elseif ($a['dias'] <= 7)  $baldes['semana']['itens'][]  = $a;
                elseif ($a['dias'] <= 30) $baldes['mes']['itens'][]     = $a;
                else                      $baldes['depois']['itens'][]  = $a;
            }
        }

        $resumo = [
            'vencidos' => count(array_filter($alertas, fn($a) => $a['dias'] < 0)),
            'proximos' => count(array_filter($alertas, fn($a) => $a['dias'] >= 0 && $a['dias'] <= 30)),
            'total'    => count($alertas),
        ];

        $escopo_nome = $cli['nome'] ?? null; // null = agenda geral

        // Integração Notion (só admin): status do vínculo + mensagem de retorno.
        $notion = null; $notion_msg = $_GET['notion'] ?? null;
        if (($usuario['nivel'] ?? '') === 'admin') {
            $notion = NotionIntegracao::doUsuario((int) $usuario['id']);
        }

        $this->view('agenda/index', compact('baldes', 'resumo', 'escopo_nome', 'cli', 'notion', 'notion_msg', 'agrupar'));
    }
}

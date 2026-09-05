<?php
/**
 * Controller de condomínios: cadastro, edição e vínculo com imóveis.
 */
class CondominiosController extends Controller
{
    private function str(string $k): ?string
    {
        $v = trim($_POST[$k] ?? '');
        return $v !== '' ? $v : null;
    }

    private function dec(string $k): ?float
    {
        $v = trim($_POST[$k] ?? '');
        if ($v === '') return null;
        return (float) str_replace(',', '.', str_replace('.', '', $v));
    }

    private function intv(string $k): ?int
    {
        $v = trim($_POST[$k] ?? '');
        return $v !== '' ? (int) $v : null;
    }

    /** Todos os campos do condomínio (compartilhados por cadastro e edição). */
    private function camposComuns(): array
    {
        $campos = [
            'nome'                    => $this->str('nome'),
            'cnpj'                    => $this->str('cnpj'),
            'endereco'                => $this->str('endereco'),
            'cep'                     => $this->str('cep'),
            'bairro'                  => $this->str('bairro'),
            'cidade'                  => $this->str('cidade'),
            'estado'                  => $this->str('estado'),
            'numero_blocos'           => $this->intv('numero_blocos'),
            'numero_unidades'         => $this->intv('numero_unidades'),
            'sindico_nome'            => $this->str('sindico_nome'),
            'sindico_telefone'        => $this->str('sindico_telefone'),
            'sindico_email'           => $this->str('sindico_email'),
            'mandato_fim'             => $this->str('mandato_fim'),
            'administradora'          => $this->str('administradora'),
            'administradora_telefone' => $this->str('administradora_telefone'),
            'valor_taxa'              => $this->dec('valor_taxa'),
            'valor_fundo_reserva'     => $this->dec('valor_fundo_reserva'),
            'dia_vencimento'          => $this->intv('dia_vencimento'),
        ];
        foreach (array_keys(condominio_comodidades()) as $col) {
            $campos[$col] = isset($_POST[$col]) ? 1 : 0;
        }
        $campos['observacoes'] = $this->str('observacoes');
        return $campos;
    }

    private function salvarDocumentos(int $condId, int $clienteDoc): void
    {
        if (!$clienteDoc) return;
        salvar_upload_documentos(
            ['cartao_cnpj' => 'cnpj', 'contrato_doc' => 'contrato'],
            'condominio', $condId, $clienteDoc, 'condominios/condominio_' . $condId
        );
    }

    /** GET/POST condominios/novo?imovel_id= */
    public function novo(): void
    {
        exige_admin();
        $imovel_id = (int) ($_GET['imovel_id'] ?? $_POST['imovel_id'] ?? 0);
        $imv = $imovel_id ? Imovel::buscar($imovel_id) : null;

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome do condomínio é obrigatório.';
            } else {
                $condId = Condominio::criar($this->camposComuns());
                $this->salvarDocumentos($condId, (int) ($imv['cliente_id'] ?? 0));
                if ($imovel_id) {
                    Condominio::vincular($imovel_id, $condId);
                    $this->redirect('imoveis/ficha?id=' . $imovel_id . '&aba=condominio');
                }
                $this->redirect('imoveis');
            }
        }

        $d = $_POST;
        $this->view('condominios/novo', compact('imv', 'imovel_id', 'erro', 'd'));
    }

    /** GET/POST condominios/editar?id=&imovel_id= */
    public function editar(): void
    {
        exige_admin();
        $id = (int) ($_GET['id'] ?? 0);
        $imovel_id = (int) ($_GET['imovel_id'] ?? $_POST['imovel_id'] ?? 0);
        $co = Condominio::buscar($id);
        if (!$co) $this->redirect('imoveis');

        $cliente_doc = Condominio::clienteParaDocumentos($imovel_id, $id);

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome do condomínio é obrigatório.';
            } else {
                Condominio::atualizar($id, $this->camposComuns());
                $this->salvarDocumentos($id, $cliente_doc);
                $this->redirect($imovel_id ? 'imoveis/ficha?id=' . $imovel_id . '&aba=condominio' : 'imoveis');
            }
        }

        $d = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $co;
        $docs = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = "condominio" AND referencia_id = ? ORDER BY criado_em DESC');
        $docs->execute([$id]);
        $docs = $docs->fetchAll();

        $this->view('condominios/editar', compact('co', 'id', 'imovel_id', 'erro', 'd', 'docs'));
    }

    /** GET condominios/vincular?imovel_id=&condominio_id=  (condominio_id=0 desvincula) */
    public function vincular(): void
    {
        exige_admin();
        $imovel_id     = (int) ($_GET['imovel_id'] ?? $_POST['imovel_id'] ?? 0);
        $condominio_id = (int) ($_GET['condominio_id'] ?? $_POST['condominio_id'] ?? 0);

        if (!Imovel::buscar($imovel_id)) $this->redirect('imoveis');

        if ($condominio_id) {
            if (!Condominio::buscar($condominio_id)) {
                $this->redirect('imoveis/ficha?id=' . $imovel_id . '&aba=condominio');
            }
            Condominio::vincular($imovel_id, $condominio_id);
        } else {
            Condominio::desvincular($imovel_id);
        }

        $this->redirect('imoveis/ficha?id=' . $imovel_id . '&aba=condominio');
    }
}

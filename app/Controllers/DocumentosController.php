<?php
/**
 * Controller de documentos — upload avulso vinculado a um imóvel (via ficha).
 */
class DocumentosController extends Controller
{
    /** GET/POST documentos/upload?tipo=imovel&ref= */
    public function upload(): void
    {
        exige_admin();
        $tipo_ref = $_GET['tipo'] ?? 'imovel';
        $ref_id   = (int) ($_GET['ref'] ?? 0);

        $im = ($tipo_ref === 'imovel') ? Imovel::buscar($ref_id) : null;
        if (!$im) $this->redirect('imoveis');

        $back_url = base_url('imoveis/ficha?id=' . $ref_id . '&aba=documentos');
        $erro = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_FILES['arquivo']['name'])) {
                $erro = 'Selecione um arquivo.';
            } else {
                $nome_orig = $_FILES['arquivo']['name'];
                $ext       = strtolower(pathinfo($nome_orig, PATHINFO_EXTENSION));
                $allowed   = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($ext, $allowed)) {
                    $erro = 'Tipo de arquivo não permitido. Use PDF, JPG ou PNG.';
                } elseif ($_FILES['arquivo']['size'] > 20 * 1024 * 1024) {
                    $erro = 'Arquivo muito grande (máximo 20 MB).';
                } else {
                    $dir = APP_ROOT . '/uploads/' . $im['cliente_id'] . '/docs/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $nome_salvo = $tipo_ref . '_' . $ref_id . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $dir . $nome_salvo)) {
                        Documento::criar([
                            'cliente_id'      => $im['cliente_id'],
                            'tipo_referencia' => $tipo_ref,
                            'referencia_id'   => $ref_id,
                            'categoria'       => $_POST['categoria'] ?? 'outro',
                            'nome_arquivo'    => $nome_orig,
                            'caminho'         => 'uploads/' . $im['cliente_id'] . '/docs/' . $nome_salvo,
                            'mime_type'       => $_FILES['arquivo']['type'],
                            'tamanho_bytes'   => $_FILES['arquivo']['size'],
                            'data_emissao'    => ($_POST['data_emissao'] ?? '') ?: null,
                            'data_validade'   => ($_POST['data_validade'] ?? '') ?: null,
                            'descricao'       => trim($_POST['descricao'] ?? '') ?: null,
                        ]);
                        $this->redirect('imoveis/ficha?id=' . $ref_id . '&aba=documentos');
                    } else {
                        $erro = 'Erro ao salvar o arquivo. Verifique as permissões da pasta uploads/.';
                    }
                }
            }
        }

        $this->view('documentos/upload', compact('im', 'tipo_ref', 'ref_id', 'back_url', 'erro'));
    }
}

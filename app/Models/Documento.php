<?php
/**
 * Model de documento — tabela `documentos` (polimórfica).
 */
class Documento
{
    public static function criar(array $c): void
    {
        db()->prepare('INSERT INTO documentos (cliente_id, tipo_referencia, referencia_id, categoria, nome_arquivo, caminho, mime_type, tamanho_bytes, data_emissao, data_validade, descricao) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([
                $c['cliente_id'], $c['tipo_referencia'], $c['referencia_id'], $c['categoria'],
                $c['nome_arquivo'], $c['caminho'], $c['mime_type'], $c['tamanho_bytes'],
                $c['data_emissao'], $c['data_validade'], $c['descricao'],
            ]);
    }
}

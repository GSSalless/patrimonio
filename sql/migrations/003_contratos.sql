-- 003 — Módulo 12: Contratos. Cria a tabela `contratos` e libera 'contrato'
-- em documentos.tipo_referencia (troca o ENUM por VARCHAR — sem mais ALTER de
-- enum a cada módulo novo). Idempotente. Aplicada pelo Migrator no deploy.

CREATE TABLE IF NOT EXISTS contratos (
  id                   INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id           INT NOT NULL,
  codigo               VARCHAR(20)  NOT NULL,              -- CT-0001
  numero               VARCHAR(80)  NULL,                  -- nº do contrato (do documento)
  tipo                 ENUM('locacao','prestacao_servico','fornecimento','compra_venda','sociedade','emprestimo','financiamento','seguro','trabalho','outro') NOT NULL DEFAULT 'outro',
  objeto               VARCHAR(255) NULL,                  -- objeto / descrição
  contraparte_nome     VARCHAR(180) NULL,
  contraparte_doc      VARCHAR(30)  NULL,                  -- CPF/CNPJ da outra parte

  -- Vínculo polimórfico opcional (a que o contrato se refere)
  vinculo_tipo         ENUM('nenhum','imovel','veiculo','outro_bem','fornecedor','colaborador','empresa') NOT NULL DEFAULT 'nenhum',
  vinculo_id           INT NULL,

  -- Vigência e renovação
  data_inicio          DATE NULL,
  data_fim             DATE NULL,
  renovacao_automatica TINYINT(1) NOT NULL DEFAULT 0,
  prazo_renovacao      VARCHAR(60) NULL,                   -- ex.: "12 meses"

  -- Valores
  valor                DECIMAL(15,2) NULL,
  periodicidade        ENUM('unico','mensal','trimestral','semestral','anual','outro') NULL,
  indice_reajuste      VARCHAR(40) NULL,                   -- IGPM, IPCA, INPC...

  status               ENUM('ativo','encerrado','suspenso','em_negociacao','rescindido') NOT NULL DEFAULT 'ativo',
  observacoes          TEXT NULL,
  ativo                TINYINT(1) NOT NULL DEFAULT 1,
  criado_em            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY (cliente_id),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE documentos MODIFY tipo_referencia VARCHAR(30) NOT NULL;

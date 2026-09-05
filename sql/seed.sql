-- ============================================================
-- Dados de teste — cliente-piloto Marcos Borges
-- ============================================================

USE gestao_patrimonial;

-- Usuário César (admin) — senha: cesar123
INSERT INTO usuarios (nome, email, senha_hash, nivel) VALUES
('César Cordeiro', 'cesar@gestaopatrimonial.com.br',
 '$2y$12$bK9jpNL2tASuso9GuMBuAuSU9CCVXatns1B4l9q0OLEIF2pEcTvF2', -- cesar123
 'admin');

-- Usuário do cliente Marcos Borges — senha: marcos123
INSERT INTO usuarios (nome, email, senha_hash, nivel) VALUES
('Marcos Borges', 'marcos@road.com.br',
 '$2y$12$UJxQoZVTn65JnQtW5z03I.KxPxQpTSUUNHxPCvfkqp1ry1Ty6LR..', -- marcos123
 'cliente');

-- Cliente Marcos Borges (PF)
INSERT INTO clientes (usuario_id, tipo_pessoa, nome, cpf_cnpj, email, telefone) VALUES
(2, 'PF', 'Marcos Borges', '123.456.789-00', 'marcos@road.com.br', '(11) 99999-0001');

-- Condomínio do imóvel de exemplo
INSERT INTO condominios (nome, endereco, cep, bairro, cidade, estado) VALUES
('4D Complex', 'Av. Exemplo, 4000', '01310-100', 'Jardins', 'São Paulo', 'SP');

-- Imóvel de exemplo: "4D Complex – unid. 321"
INSERT INTO imoveis (
  cliente_id, condominio_id, codigo, nome_referencia, tipo, finalidade, situacao,
  logradouro, numero, complemento, cep, bairro, cidade, estado,
  inscricao_municipal,
  data_aquisicao, forma_aquisicao,
  valor_compra, valor_mercado, data_avaliacao_mercado,
  custo_condominio, custo_iptu_mensal
) VALUES (
  1, 1, 'IM-0001', '4D Complex – unid. 321', 'apartamento', 'residencial', 'pronto',
  'Av. Exemplo', '4000', 'Unid. 321, Torre A', '01310-100', 'Jardins', 'São Paulo', 'SP',
  'IPTU-2024-001234',
  '2020-03-15', 'compra',
  850000.00, 1200000.00, '2026-06-01',
  1200.00, 450.00
);

-- IPTU 2026
INSERT INTO iptu (imovel_id, ano, inscricao_iptu, valor_total, parcelas, data_vencimento_1, pago) VALUES
(1, 2026, 'IPTU-2024-001234', 5400.00, 10, '2026-02-10', 0);

-- Faturas de condomínio (últimos 3 meses)
INSERT INTO condominio_faturas (imovel_id, competencia, valor, pago, data_pagamento) VALUES
(1, '2026-04-01', 1150.00, 1, '2026-04-10'),
(1, '2026-05-01', 1200.00, 1, '2026-05-09'),
(1, '2026-06-01', 1350.00, 0, NULL);  -- junho em aberto

-- Reforma exemplo
INSERT INTO reformas (imovel_id, descricao, status, data_inicio, data_fim_prevista, custo_previsto, custo_realizado, fornecedor) VALUES
(1, 'Reforma da cozinha — substituição de revestimento e armários', 'em_andamento',
 '2026-05-10', '2026-07-30', 45000.00, 22000.00, 'Construtora ABC Ltda');

-- Contrato de locação (não aplicável ao 4D Complex por ser residencial do Marcos,
-- mas deixamos um exemplo comentado)
-- INSERT INTO contratos_locacao ...

-- Lançamentos financeiros de exemplo
INSERT INTO lancamentos_financeiros (imovel_id, cliente_id, tipo, categoria, descricao, valor, data_competencia, pago) VALUES
(1, 1, 'despesa', 'condominio', 'Condomínio Abril/2026',   1150.00, '2026-04-01', 1),
(1, 1, 'despesa', 'condominio', 'Condomínio Maio/2026',    1200.00, '2026-05-01', 1),
(1, 1, 'despesa', 'condominio', 'Condomínio Junho/2026',   1350.00, '2026-06-01', 0),
(1, 1, 'despesa', 'reforma',    'Reforma cozinha — 1ª parcela', 22000.00, '2026-05-15', 1);

# Controle de Tarefas — Sistema de Gestão Patrimonial

> Atualizar este arquivo a cada sessão. Marcar tarefas concluídas com ✅ e registrar a data.  
> Ler sempre **antes** de começar a codar.

---

## Status Geral

| Fase | Status | Progresso |
|------|--------|-----------|
| F1 — MVP | 🟡 Em andamento | 90% (blocos A–J concluídos · falta K: testes com César) |
| F2 — Cadastro completo | 🟡 Iniciada | Matrícula+site cartório, características físicas, co-propriedade já adiantados no módulo Imóveis |
| F3 — Family Office | ⏳ Aguardando F2 | — |

> **Visão expandida (26/06/2026):** o sistema deixou de ser "cadastros separados" e passou a ser
> uma **Arquitetura de Dados de ERP Patrimonial / Family Office** com 15 módulos relacionáveis entre si.
> Ver seção **"Arquitetura ERP"** abaixo. Acompanhamento visual em **`tarefas.html`**
> (`http://127.0.0.1/cezar/tarefas.html`).

---

## FASE 1 — MVP (substituir o Excel)

### Bloco A — Infraestrutura e banco de dados

- [x] **A1** — Criar `sql/schema.sql` com todas as tabelas da F1:
  - `usuarios` (César + clientes com nível de acesso)
  - `clientes` (PF/PJ — centro do sistema)
  - `imoveis` (blocos 1, 2, 3-parcial, 4, 6)
  - `reformas`
  - `contratos_locacao`
  - `lancamentos_financeiros`
  - `documentos` (polimórfico: `tipo_referencia` + `referencia_id`)
  - `condominios`
  - `iptu` (registro anual por imóvel)
  - `condominio_faturas` (competência mensal + boleto)

- [x] **A2** — Criar `sql/seed.sql` com dados de teste:
  - Usuário: César (admin)
  - Cliente-piloto: Marcos Borges (CPF)
  - 1 imóvel de exemplo: "4D Complex – unid. 321"
  - 1 registro de IPTU
  - 1 fatura de condomínio
  - 1 reforma de exemplo

- [x] **A3** — Criar `includes/db.php` (conexão PDO)
- [x] **A4** — Criar `includes/auth.php` (sessão, login, logout, verificação de permissão)
- [x] **A5** — Criar `includes/functions.php` (helpers: formata moeda, formata data, gera código IM-XXXX)
- [x] **A6** — Criar `includes/header.php` e `includes/footer.php` (layout base + menu + seletor de cliente)
- [x] **A7** — Criar `assets/css/style.css` (layout limpo, responsivo, sem Bootstrap)
- [x] **A8** — Criar `assets/js/main.js` (funções utilitárias JS: máscara CPF/CNPJ, CEP autopreenchimento)

---

### Bloco B — Autenticação

- [x] **B1** — `index.php` — tela de login (email + senha)
- [x] **B2** — `logout.php` — encerrar sessão
- [x] **B3** — Validação de sessão em todas as páginas protegidas

---

### Bloco C — Gestão de Clientes

- [x] **C1** — `clientes/lista.php` — lista de clientes do César (cards ou tabela)
- [x] **C2** — `clientes/novo.php` — cadastro de cliente (nome, CPF/CNPJ, e-mail, telefone)
- [x] **C3** — `clientes/editar.php` — edição de cliente
- [x] **C4** — Seletor de cliente no `header.php` (select com todos os clientes do César)

---

### Bloco D — Módulo de Imóveis (F1)

- [x] **D1** — `imoveis/lista.php` — lista de imóveis do cliente selecionado
  - Cards com: nome referência, tipo, cidade, valor de mercado, situação, custo mensal
  - Botão "+ Cadastrar imóvel"
  - Filtros por tipo / cidade / situação

- [x] **D2** — `imoveis/novo.php` — formulário de cadastro de imóvel (campos F1)
  - Seção 1: Identificação (código auto, nome referência, tipo, finalidade, situação)
  - Seção 2: Localização (endereço, CEP com autopreenchimento, link Google Maps automático)
  - Seção 3: Titularidade (proprietário/cliente, CPF/CNPJ, data aquisição, forma aquisição)
  - Seção 4: Financeiro (valor compra, entrada, financiamento, banco, valor de mercado, custos mensais)
  - Seção 5: Upload de documentos (escritura, matrícula, IPTU, fotos)

- [x] **D3** — `imoveis/ficha.php` — ficha do imóvel com abas:
  - Aba **Resumo**: dados principais + valor de mercado + custo mensal total
  - Aba **Financeiro**: IPTU anual, faturas de condomínio por competência, outros lançamentos
  - Aba **Reformas**: lista de reformas com custo previsto × realizado
  - Aba **Aluguel**: contrato vigente, locatário, valor, vencimento
  - Aba **Documentos**: galeria/lista de arquivos anexados

- [x] **D4** — `imoveis/editar.php` — edição dos dados do imóvel

---

### Bloco E — Controle de IPTU

- [x] **E1** — Formulário dentro da ficha (aba Financeiro) para registrar IPTU:
  - Nº inscrição municipal, valor anual, data vencimento, opção parcelamento
  - Upload do carnê/boleto

- [x] **E2** — Listagem histórica de IPTU por imóvel (evolução anual)

---

### Bloco F — Controle de Condomínio

- [x] **F1** — Registro mensal de condomínio (competência + valor + upload do boleto)
- [x] **F2** — Listagem histórica com variação de valores mês a mês

---

### Bloco G — Reformas

- [x] **G1** — `reformas/nova.php` — cadastro de reforma:
  - Descrição, datas início/fim, status (planejado/em andamento/concluído), custo previsto, custo realizado, fornecedor
  - Upload de NF, cupom fiscal, contrato

- [x] **G2** — Listagem de reformas na aba da ficha do imóvel
- [x] **G3** — Edição de reforma

---

### Bloco H — Locação (F1 básico)

- [x] **H1** — `locacao/novo.php` — contrato de locação básico:
  - Locatário (nome + CPF/CNPJ), início/fim do contrato, valor do aluguel
  - Upload do contrato

- [x] **H2** — Exibição do contrato vigente na aba Aluguel da ficha do imóvel

---

### Bloco I — Lançamentos Financeiros

- [x] **I1** — Registro de lançamentos avulsos (gasto, receita) vinculados ao imóvel
- [x] **I2** — Listagem de lançamentos na aba Financeiro

---

### Bloco J — Upload de Documentos

- [x] **J1** — `api/upload.php` — endpoint para upload de arquivos (PDF/imagem)
  - Salvar em `uploads/{cliente_id}/{tipo}/`
  - Registrar na tabela `documentos` com tipo_referencia, referencia_id, data emissão, validade
- [x] **J2** — Galeria/lista de documentos na aba Documentos da ficha do imóvel
- [x] **J3** — Preview inline de imagens e link para abrir PDF

---

### Bloco K — Testes com dados reais

- [ ] **K1** — Testar cadastro completo do imóvel "4D Complex – unid. 321" (Marcos Borges)
- [ ] **K2** — Testar IPTU + condomínio com boleto
- [ ] **K3** — Testar reforma
- [ ] **K4** — Revisão com César (campo a campo) — aprovação para avançar à F2

---

### Bloco L — UX, Erros e Produção (14/07/2026)

- [x] **L1** — Tratamento global de erro (`app/Core/ErrorHandler.php`): página amigável em
  produção (500/404) + detalhes completos em `local`; loga sempre em `error_log`.
  Substitui o "HTTP ERROR 500" genérico da hospedagem.
  *(Diagnóstico: ficha do imóvel dava 500 em produção porque a tabela `manutencoes`
  não existia lá — faltou rodar `sql/update_producao_modulos_567.sql` no banco de produção.)*
- [x] **L2** — Menu lateral esquerdo (drawer suspenso com botão ☰) no lugar do menu no topo-direito.
- [x] **L3** — Layout responsivo (celular): topo, tabelas com rolagem, grids de formulário
  em coluna única, cards e abas adaptados via `@media`.

---

## FASE 2 — Cadastro Patrimonial Completo
*(inicia após aprovação da F1)*

- [ ] Matrícula completa (cartório, comarca, livro, folha, data)
- [ ] Características físicas (áreas em m², ambientes, estrutura)
- [ ] Co-propriedade e percentual de participação
- [ ] Regime de bens / holding / beneficiário final
- [ ] Condomínio detalhado (CNPJ, síndico, administradora, estrutura)
- [x] Seguros (apólice, coberturas, valor segurado, vencimento) — Módulo 11 (11/08)
- [ ] Manutenção (histórico, equipamentos, garantias)
- [ ] Checklist de documentação (matrícula atualizada, certidão negativa, habite-se…)
- [ ] Locação completa (índice de reajuste, garantias, caução, seguro-fiança)

---

## FASE 3 — Family Office / Inteligência
*(inicia após aprovação da F2)*

- [ ] Indicadores: yield bruto/líquido, ROI, TIR, ganho de capital, vacância
- [ ] Histórico de avaliações (gráfico de valorização)
- [ ] Valorização automática por IA/API
- [ ] Alertas de vencimento (IPTU, seguro, financiamento, renovação de aluguel)
- [ ] Relacionamentos (corretor, construtora, advogado, contador)
- [ ] Integração com Drive/OneDrive/Dropbox
- [ ] PWA (manifest.json + service worker)
- [ ] Área de visualização do cliente final

---

## Arquitetura ERP Patrimonial / Family Office (visão expandida)

> Documento funcional de desenvolvimento. Todos os módulos devem ser **relacionáveis entre si**
> (um imóvel tem N contratos/seguros/documentos; um investimento pertence a uma conta financeira;
> uma conta pertence a uma pessoa/empresa/holding; um fornecedor atende vários imóveis/veículos/empresas).

### Módulo 01 — Pessoas  🟢 *(implementado 28/07 — pedido direto do César na reunião de 21/07)*
- [x] **Card do cliente** com o básico: nome completo, telefone, e-mail + **botão WhatsApp** (clica → abre wa.me direto) — clicar no card abre o cadastro completo (`clientes/lista.php` em cards)
- [x] Exibir **nome completo** (ex.: "Marcos Vinícius Machado Borges", não "Marcos Borges") + CPF + endereço no topo do cliente (resumo em `clientes/editar.php`)
- [x] Pessoa Física: nome, CPF, RG, data nasc., estado civil, regime de bens, tipo sanguíneo, profissão, filiação, telefones, e-mails (**múltiplos**), endereços, documentos, contatos de emergência — *"pecar pelo excesso"* (César)
- [x] Família / sucessão: nome do pai, nome da mãe, cônjuge, **ex-cônjuge(s)**, filhos/dependentes, herdeiros (`pessoa_familiares` com enum de parentesco)
- [x] **Testamento**: existe? (sim/não) + upload do documento + dados do testamento (categoria `testamento` em `documentos`)
- [x] Observações
- [ ] Relacionamentos: proprietário de imóveis/veículos, titular de investimentos, sócio de empresas, responsável por contratos *(→ F3, depende dos módulos 08/10)*

> **Implementação (28/07):** `sql/migration_modulo_01_pessoas.sql` amplia `clientes` (nome_completo,
> apelido, RG, nascimento, naturalidade, nacionalidade, estado civil, regime de bens, profissão,
> tipo sanguíneo, filiação, endereço principal, testamento, observações) + 5 sub-tabelas 1:N
> (`pessoa_telefones`, `pessoa_emails`, `pessoa_enderecos`, `pessoa_familiares`,
> `pessoa_contatos_emergencia`). `Cliente` model migrado p/ INSERT/UPDATE genérico; novo model
> `Pessoa` (allow-list de sub-tabelas, DELETE restrito a cliente_id → sem IDOR). `ClientesController`
> com lista em cards, `novo` (base → segue p/ completo), `editar` (dados + sub-seções + upload de
> testamento) e `itemRemover`. Partial `clientes/_campos.php` compartilhado (novo ↔ editar). Enum
> `documentos.categoria` estendido (testamento/identidade/comprovante_residencia/certidao/procuracao).
> ✅ testado HTTP+banco (criação, edição, sub-tabelas, remoção, IDOR).

### Módulo 02 — Colaboradores
- [ ] Dados pessoais (cadastro completo)
- [ ] RH: cargo, salário, departamento, gestor
- [ ] Dependentes (cônjuge, filhos)
- [ ] Escolaridade: formação, cursos, certificações
- [ ] Saúde: tipo sanguíneo, convênios, alergias
- [ ] Uniformes: camiseta, camisa, calça, calçado
- [ ] Benefícios: vale alimentação, plano saúde, seguro vida
- [ ] Histórico: salários, promoções, benefícios, advertências, avaliações
- [ ] Controle: férias, histórico de férias, faltas, atestados, treinamentos

### Módulo 03 — Empresas  🟢 *(implementado 12/08 — testado HTTP+banco)*
- [x] Tabela `empresas` + CRUD completo (`Empresa` model, `EmpresasController`, views `empresas/{lista,novo,editar,_campos}.php`, rotas) · código EM-XXXX · `buscarDoCliente` sem IDOR
- [x] Dados: razão social, nome fantasia, CNPJ, natureza (operacional/holding patrimonial/holding participação/SPE), natureza jurídica, regime tributário, situação, inscrições estadual/municipal, CNAE principal + secundários, capital social, data de abertura, endereço, contato, contabilidade
- [x] Estrutura: **quadro societário** (`empresa_socios`, 1:N) — sócios/administradores/procuradores com participação % e conferência da soma (100%); add/remove com escopo por cliente (sem IDOR)
- [x] Documentos: contrato social, cartão CNPJ, outros (`tipo_referencia` `empresa` + categoria `contrato_social`)
- [x] Integração: app 🏢 no dashboard com badge + item "Empresas" no menu lateral · lista com filtros + capital social somado
- [ ] Relacionamentos: holdings ↔ imóveis/veículos/investimentos *(→ F3)*
- [x] ✅ `sql/migration_modulo_03_empresas.sql` já aplicada em produção (verificado 12/08)

### Módulo 04 — Fornecedores e Parceiros
- [ ] Cadastro: razão social, nome fantasia, CNPJ, endereço, contatos
- [ ] Classificação: contabilidade, jurídico, seguros, marina, saúde, tecnologia, RH, imobiliária, outros
- [ ] Contratos: vigência, valor, reajustes
- [ ] Financeiro: banco, PIX, pagamentos
- [ ] Avaliação: SLA, qualidade, prazo, custo-benefício
- [ ] Arquivos: contratos, notas fiscais, certidões

### Módulo 05 — Imóveis  🟢 *(base implementada na F1)*
- [x] Identificação: código, tipo, finalidade
- [x] Localização: endereço completo, CEP, coordenadas/links de mapa
- [x] Documentação: matrícula, escritura, IPTU, habite-se *(matrícula + site do cartório ✅)*
- [x] Propriedade: proprietário, participação % *(+ coproprietários quando < 100%)*
- [x] Dados físicos: área privativa, área total, quartos, vagas
- [x] Financeiro: valor aquisição, valor atual, valor de mercado, valor m² automático
- [x] Custos: condomínio, IPTU, seguro
- [x] Locação: contrato, locatário, reajustes *(básico)*
- [x] Histórico: reformas, avaliações *(manutenções → F2)*
- [x] Arquivos: matrícula, escritura, contratos, fotos
- [x] Ficha em hub central (modais) com ícones (Bootstrap Icons)
- [x] **Condomínio vinculado** (N:1): cadastro/edição com síndico, administradora, valores e comodidades — `condominios/*.php`
- [x] **Manutenções (histórico completo)** — tabela `manutencoes`, 7º nó "Manutenções" no hub da ficha + modal, `ManutencoesController` + `manutencoes/nova|editar.php`; valor gera lançamento no caixa (categoria `manutencao`). ✅ testado HTTP+banco
- [x] **Checklist documental completo** — painel na modal Documentos (matrícula, escritura, contrato compra, IPTU, habite-se, fotos) com ✓/✗ e contador; categoria `habite_se` adicionada ao enum + form de upload. ✅ testado

### Módulo 06 — Veículos  🟢 *(cadastro implementado)*
- [x] Tabela `veiculos` + lista/novo/editar (veiculos/*.php) + código VE-XXXX
- [x] Identificação: marca, modelo, ano fab./modelo, cor, combustível, placa, Renavam, chassi
- [x] Documentação: vencimento licenciamento, multas
- [x] Seguro: seguradora, apólice, franquia, vencimento
- [x] Financeiro: aquisição, FIPE, mercado
- [x] Controle: KM atual, consumo médio, observações
- [x] Upload de documentos (CRLV, apólice, NF serviços/peças, fotos)
- [x] Alerta de campos vazios antes de salvar + modal de pendências + PDF + WhatsApp
- [x] **Abastecimentos (sub-tabela)** — `abastecimentos`, `Abastecimento` model, `VeiculosController::abastecimento`, `veiculos/abastecimento.php`, seção no rodapé do `editar.php`. ✅ testado HTTP+banco
- [x] **Manutenções: revisões, óleo, pneus, freios, bateria, peças (sub-tabela)** — `veiculo_manutencoes`, `VeiculoManutencao` model, `VeiculosController::manutencao`, `veiculos/manutencao.php`, seção no `editar.php` (garantia + próxima revisão data/km). ✅ testado
- [x] **Histórico de sinistros e custos** — `sinistros`, `Sinistro` model, `VeiculosController::sinistro`, `veiculos/sinistro.php`, seção no `editar.php` (tipo, BO, acionou seguro, prejuízo, franquia, status). ✅ testado

### Módulo 07 — Outros Bens (Embarcações / Joias / Obras de Arte)  🟢 *(cadastro implementado; unificado em `outros_bens`)*
- [x] Cadastro unificado: embarcação (jet ski, lancha, barco), joia, obra de arte, outro
- [x] Dados por tipo: embarcação (motor, horímetro, marina, vaga, mensalidade, registro), joia (material, quilates, certificado), obra (artista, técnica, dimensões)
- [x] Seguro: seguradora, apólice, franquia, vencimento
- [x] Upload de documentos (foto, laudo, apólice, outros)
- [x] **Manutenções de embarcação (sub-tabela)** — `bem_manutencoes`, `BemManutencao` model, `OutrosController::manutencao`, `outros/manutencao.php`, seção no `editar.php` (só para tipo embarcação). ✅ testado HTTP+banco (21/07)
- [x] **Avaliações periódicas / histórico de valor** — `avaliacoes_bem`, `AvaliacaoBem` model, `OutrosController::avaliacao`, `outros/avaliacao.php`, seção no `editar.php` com variação entre avaliações; a mais recente atualiza `valor_mercado` do bem. ✅ testado HTTP+banco (21/07) — inclusive regra "avaliação mais recente vence" com data retroativa

### Módulo 08 — Bancos e Instituições Financeiras
- [ ] Cadastro: banco, corretora, family office, gestora
- [ ] Dados: país, moeda
- [ ] Contatos: gerente, banker, assessor
- [ ] Arquivos: contratos, KYC, procurações

### Módulo 09 — Contas Financeiras  🟢 *(cadastro implementado — 21/07)*
- [x] Tabela `contas_financeiras` + lista/novo/editar (`app/Views/contas/*`, partial `_campos.php`) + código CF-XXXX + ícone "Contas" 🏦 no dashboard
- [x] Dados: banco (instituição + código COMPE), agência, conta, tipo (corrente/poupança/pagamento/investimento/internacional/outro), PIX (tipo + chave)
- [x] Internacional: SWIFT, IBAN, routing
- [x] Titularidade: titular alternativo (empresa/holding) com CPF/CNPJ + gerente/assessor e contato
- [x] Saldos: moeda + **histórico de saldos** (`conta_saldos`, `ContaSaldo` model, `ContasController::saldo`) — o mais recente espelha `saldo_atual`/`saldo_data`; saldo consolidado (BRL) na lista; aceita saldo negativo
- [x] Upload de documentos (contrato de abertura, extrato, outros) — enum `documentos` +'conta_financeira'/'extrato'
- [x] **Preparação Asaas** (https://docs.asaas.com): campos `integracao` ('nenhuma'/'asaas'), `asaas_account_id`, `asaas_wallet_id`, `asaas_api_key` + origem 'asaas' em `conta_saldos` para a futura sincronização automática de saldo/extrato/cobranças via API
- [ ] **Integração Asaas efetiva** (F3): sincronizar saldo/extrato, emitir cobranças, webhooks

### Módulo 10 — Investimentos
- [ ] Cadastro: nome do investimento, instituição
- [ ] Classificação: renda fixa, fundos, ações, previdência, offshore, cripto
- [ ] Aplicação: data, valor aplicado, valor atual
- [ ] Rentabilidade: mensal, anual, acumulada
- [ ] Liquidez: D0, D1, D30, D90
- [ ] Tributação: IR, IOF, come-cotas
- [ ] Custos: taxa de administração, taxa de performance
- [ ] Histórico: aplicações, resgates, rendimentos
- [ ] Documentos: propostas, regulamentos, extratos

### Módulo 11 — Seguros  🟢 *(implementado 11/08 — testado HTTP+banco)*
- [x] Tabela `seguros` + **vínculo polimórfico** (item_tipo+item_id) a imóvel/veículo/outro bem/pessoa · código SG-XXXX · `Seguro` model (INSERT/UPDATE genérico, `buscarDoCliente` sem IDOR, `itensSeguraveis`/`descreverVinculo`)
- [x] CRUD completo: `SegurosController`, views `seguros/{lista,novo,editar,_campos}.php`, rotas
- [x] Tipos: vida, saúde, veículo, residencial, imóvel, embarcação, empresarial, viagem, outro · situação (vigente/em cotação/vencida/cancelada)
- [x] Dados: seguradora, corretora, corretor+contato, apólice, vigência (início/fim), valor segurado, prêmio, franquia, forma de pagamento, cobertura, beneficiários
- [x] Arquivos: apólice, boleto, outros (`tipo_referencia` `seguro` add ao enum de `documentos`)
- [x] **Integração:** alimenta a **Agenda** (Mód. 14) pela `vigencia_fim` (status vigente) · app 🛡️ no dashboard com badge · item "Seguros" no menu lateral · lista com filtros + prêmio total (vigentes)
- [x] ✅ `sql/migration_modulo_11_seguros.sql` já aplicada em produção (verificado 12/08)

### Módulo 12 — Contratos
- [ ] Cadastro: número, tipo
- [ ] Relacionamentos: imóvel, veículo, fornecedor, colaborador, empresa
- [ ] Controle: início, término, renovação, reajuste

### Módulo 13 — Documentos (repositório central)  🟡 *(parcial)*
- [x] Upload polimórfico (tipo_referencia + referencia_id) + categorias
- [ ] Categorias completas: pessoais, imóveis, veículos, empresas, investimentos, contratos, seguros
- [ ] Campos: arquivo, categoria, data emissão, data vencimento, observações

### Módulo 14 — Agenda e Alertas  🟢 *(implementado 11/08 — testado HTTP+navegador)*
- [x] **Motor de alertas** — helper `alertas_consolidado(?cliente_id)` em `functions.php` (UNION varrendo todas as datas de vencimento) + `dias_ate()`, `alerta_status()`, `alertas_resumo()`. `AgendaController` + rota `agenda` + view `agenda/index.php` em baldes (Vencidos / 7 dias / 30 dias / Mais adiante) com cor por urgência e chips de resumo. Escopo: admin com cliente = agenda do cliente; admin sem cliente = agenda geral (todos); cliente = a própria.
- [x] Imóveis: **IPTU em aberto** (pago=0) · **fim de contrato de locação** (ativo)
- [x] Veículos: **licenciamento**, **seguro**, **revisão prevista** (próxima_data futura)
- [x] Outros bens: **seguro** + **manutenção prevista** de embarcação
- [x] **Documentos com validade**
- [x] Integração: item "Agenda" no menu lateral · app "Agenda" 📅 no dashboard com **badge de urgentes** · **faixa de alertas** clicável na Gestão Geral
- [x] Fix de collation: `veiculos` é `utf8mb4_general_ci` e as demais `_unicode_ci` → `COLLATE utf8mb4_unicode_ci` nas colunas de texto do UNION (sem ALTER, funciona em produção)
- [ ] Pessoas: CNH/passaporte vencendo *(depende de campos de validade no Módulo 01)*
- [ ] Colaboradores: férias, cursos vencendo *(depende do Módulo 02)*
- [ ] Investimentos: vencimento, carência *(depende do Módulo 10)*

### Módulo 15 — Dashboard Executivo  🟡 *(parcial — patrimônio consolidado feito 11/08)*
- [x] Hub de módulos no dashboard (menu estilo apps)
- [x] **Patrimônio total consolidado** — helper `patrimonio_consolidado(?cliente_id)` em `functions.php` (imóveis `valor_mercado` + veículos `valor_mercado→fipe→aquisição` + outros bens `valor_mercado→aquisição` + contas `saldo_atual` só BRL). **Gestão Geral:** herói preto&dourado com total sob gestão + barra de composição empilhada + legenda (valor/qtd/%) + patrimônio por cliente nos cards. **Dashboard do cliente:** painel com total + quebra por categoria com barras de participação. ✅ testado HTTP+navegador (total R$ 4.048.750,00 p/ Marcos)
- [ ] Financeiro: caixa consolidado, bancos, aplicações *(depende de Caixa/Investimentos)*
- [ ] RH: nº de colaboradores, férias, treinamentos *(depende do Módulo 02)*
- [ ] Contratos: ativos, vencendo *(depende do Módulo 12)*
- [ ] Seguros: vigentes, vencendo *(depende do Módulo 11)*

---

## Reunião com César — 21/07/2026 (2 chamadas · transcrições 6 e 7)

> César viu a plataforma em produção ("Patri Control") e o local (com Módulo 09 — Contas).
> Combinado geral: **primeiro finalizar módulos/campos** (validação manual campo a campo),
> **depois** rodada dedicada de usabilidade/layout. César vai testar os cadastros
> (veículo, imóvel, joia) e mandar ajustes + referências de ícones pelo WhatsApp.

### R1 — Navegação e estrutura (usabilidade)  🟢 *(chassi implementado 28/07)*
- [x] **Menu lateral como navegação principal**, em grupos: *Geral* (Gestão Geral + Clientes) e, quando há cliente selecionado, um grupo com o nome do cliente (Dashboard, Patrimônios, Contas). Item ativo destacado (Patrimônios acende também em imóveis/veículos/outros).
- [x] **Removido o seletor de cliente do topo** (duplicidade) → vira **chip do cliente** no topo (link para trocar) + seleção pela lista de Clientes / cards da Gestão Geral. Seleção via `?cliente_id` movida para `app/bootstrap.php` (antes dos controllers).
- [x] **Menu lateral fixo no desktop** (≥1024px, conteúdo desloca à direita) e drawer no mobile.
- [x] **"Gestão Geral"** *(nome provisório)*: tela própria (`GestaoGeralController` + `gestao_geral/index.php`, rota `gestao-geral`) com KPIs consolidados (nº de clientes, patrimônio imobiliário por valor de mercado, saldo consolidado em contas) + grade de clientes clicável. Admin sem cliente selecionado cai aqui.
- [x] Separação clara **visão gestor × visão cliente**: Gestão Geral (todos os clientes) × Dashboard (sempre um cliente).
- [x] **Botão "Voltar" global** no topo (todas as telas; `history.back()` com fallback p/ Gestão Geral/Dashboard).
- [ ] **Gestão Geral — caixa geral consolidado + tarefas/lembretes multi-cliente** ("3 tarefas de 2 clientes diferentes") — depende dos módulos Caixa/Tarefas.
- [x] **Dashboard do cliente com patrimônio real** (11/08) — painel de patrimônio consolidado (total + quebra por categoria com barras) acima do hub. Falta caixa/tarefas/alertas quando os módulos existirem; César escolhe o restante que aparece
- [ ] Testar hover/animação dos cards do hub **no celular**

### R2 — Identidade visual (aguardar referências do César)
- [x] **Padronização em emoji realista (28/07):** hub da ficha do imóvel (`imoveis/ficha.php`) migrado de Bootstrap Icons (flat) para emoji (📋💰🔨🛠️🏢🔑📁, `.hub-no-emoji` no CSS) e `/patrimonio` alinhado ao dashboard (Imóveis 🏠→🏛️). *Paliativo até as referências do César.*
- [x] **Animação "segundo cérebro" (28/07):** hub da ficha do imóvel virou **grafo interativo d3-force** (`assets/js/hub-grafo.js` + D3 v7 via CDN): nós flutuam e reagem ao arraste, segurar/hover realça em **lilás** o nó e os conectados (esmaece o resto), clique abre o modal. Fallback para grade de botões se o D3 não carregar. Falta replicar no dashboard/hub principal.
- [ ] **Ícones realistas** customizados no lugar dos atuais (estilo lápis realista do WhatsApp/ícones do Instagram; não infantil/colorido demais) — César manda referências
- [ ] **Trocar a fonte** — César vai escolher
- [ ] **Sons de interface** (clique/transição ao navegar) + **botão para desligar o som** (preferência salva)
- [ ] **Animação de fluidez estilo "segundo cérebro"** (linhas conectadas reagindo ao mouse, tipo graph do Obsidian) no hub/dashboard — dar "vida" à interface

### R3 — Automação e IA (após validação manual dos campos — redundância sempre: manual continua existindo)
- [ ] **Cadastro assistido por IA**: botão em cada cadastro para colar texto/print/PDF (matrícula do imóvel, dados bancários copiados do app do banco, contrato) → IA pré-preenche o formulário → César confere manualmente e salva (fluxo híbrido que ele descreveu)
- [ ] **E-mail → n8n → Contas a Pagar**: conta chega no e-mail (ex.: conta de luz) → n8n identifica → cria pendência no sistema com vencimento → alerta "conta a pagar vence dia X"
- [ ] **Baixa automática por comprovante/extrato**: upload do comprovante ou extrato → IA identifica o pagamento → dá baixa na pendência (vinculada à conta financeira cadastrada)
- [ ] **Integração Conta Azul e/ou Asaas** para conciliação: puxar extrato/saldo sob demanda ou diário automático; baixa vinda do Conta Azul/Asaas
- [ ] Módulo **Contas a Pagar/Receber** (pré-requisito dos 3 itens acima): pendências com vencimento, status (pendente/pago/atrasado), comprovante, origem (manual/e-mail/n8n), vínculo com conta financeira e caixa
- [ ] n8n vinculado a todos os módulos — só após finalizar os módulos

### R4 — Pendências de decisão / follow-up
- [x] ✅ **Migrations em produção (09/01/11/03)** — verificado em 12/08/2026 que o banco `u250260449_cezar_db` já está **sincronizado** (foi por dump completo do local, não migration a migration): tabelas `contas_financeiras`, `conta_saldos`, `pessoa_*`, `seguros`, `empresas`, `empresa_socios` presentes; enums de `documentos` com `seguro`/`empresa`/`contrato_social`. Nada pendente. *(Novas migrations futuras continuam sendo à mão no phpMyAdmin — deploy FTP não roda SQL.)*
- [ ] César: testar cadastros (veículo, imóvel, joia — joia ainda sem dados reais) e devolver ajustes campo a campo
- [ ] César: mandar referências de ícones + escolher fonte + definir nome do botão "Gestão Geral"
- [ ] Avaliar vínculo das tarefas do César com o **Notion** dele (assunto retomado, sem decisão)
- [ ] ⚠️ Produção no ar com acesso do César: **trocar as senhas seed** (`cesar123`/`marcos123`) por senhas fortes

---

## Melhorias de UX/Plataforma (sessão 26/06/2026)
- [x] Dashboard reformulado como hub de módulos (ícones estilo iPhone: Patrimônios, Caixa, Tarefas, Caixa Geral)
- [x] Campo "Site do cartório" + inscrição municipal movida para o bloco Identificação
- [x] Coproprietários condicionais (participação < 100%)
- [x] Remoção de regime de bens / holding / beneficiário final (form + banco)
- [x] Valor do m² automático (compra ÷ área total), calculado também no servidor
- [x] Fluxo pós-cadastro: redirect para lista + modal de pendências (campos vazios)
- [x] Relatório de pendências em PDF (impressão) + envio por WhatsApp (texto)
- [x] Confirmação antes de salvar com nº de campos em aberto
- [x] Cache-busting do CSS (filemtime) no header
- [x] Lista de imóveis estilizada
- [ ] Replicar modal de pendências no `editar.php`
- [ ] Confirmação de campos em aberto no `editar.php`

---

## Histórico de Sessões

| Data | O que foi feito |
|------|----------------|
| 23/06/2026 | Leitura do PDF · Exclusão do HTML anterior · CLAUDE.md e TAREFAS.md criados · Estrutura de pastas definida |
| 23/06/2026 | Banco criado (gestao_patrimonial) · schema.sql + seed.sql executados · Blocos A e B completos: db.php, auth.php, functions.php, header/footer, style.css, main.js, index.php, logout.php, dashboard.php |
| 26/06/2026 | Lista de imóveis estilizada · campos do imóvel reorganizados (site cartório, inscrição no bloco 1, coproprietários, remoção de regime/holding/beneficiário) · valor m² automático · dashboard como hub de apps · modal de pendências + PDF + WhatsApp · confirmação antes de salvar · cache-busting CSS · **Arquitetura ERP de 15 módulos** definida + `tarefas.html` criado |
| 26/06/2026 | Página intermediária `patrimonio.php` (Imóveis/Carros/Embarcações/Joias) · **Módulo 06 — Veículos** completo: tabela `veiculos`, cadastro com upload de docs, edição, lista, alerta de campos vazios, modal de pendências + PDF + WhatsApp; ícone Carros ligado |
| 26/06/2026 | Lista de imóveis estilo iOS · ficha redesenhada como **hub central** (modais Cadastro/Financeiro/Reformas/Aluguel/Documentos) com Bootstrap Icons · **Condomínio** vinculado ao imóvel: tabela estendida (síndico, administradora, valores, comodidades), `condominios/novo|editar|vincular.php`, modal na ficha com criar/vincular/desvincular |
| 08/07/2026 | **Finalização dos Módulos 05/06/07** (sub-tabelas de histórico). Migração `sql/migration_modulos_567.sql`: `manutencoes`, `abastecimentos`, `veiculo_manutencoes`, `sinistros`, `bem_manutencoes`, `avaliacoes_bem` (schema.sql sincronizado; enums de `documentos` estendidos: tipo_referencia +manutencao/veiculo_manutencao/sinistro/bem_manutencao, categoria +habite_se). **M05:** Manutenções (7º nó no hub da ficha + modal + geração de caixa) e Checklist documental na modal Documentos — testados. **M06:** Abastecimentos, Manutenções e Sinistros como seções no `veiculos/editar.php` + telas dedicadas — testados HTTP+banco. **M07:** Manutenções de embarcação + Avaliações (histórico de valor, atualiza valor_mercado) no `outros/editar.php` — código pronto/lintado, **pendente teste HTTP+banco**. Rotas adicionadas em `routes/web.php`. ⏸️ Pausado antes de testar o M07 (inserir uma embarcação e validar os 2 formulários). |
| 21/07/2026 | **Retomada do teste do M07** (embarcação "Lancha Ferretti 460" já inserida em 20/07 — duplicata id 4 removida). Teste HTTP+banco via curl (login César → cliente Marcos): GET/POST de `outros/manutencao` e `outros/avaliacao` (criação e edição) OK; registros gravados em `bem_manutencoes` e `avaliacoes_bem`; `valor_mercado` atualizado pela avaliação mais recente (validado com avaliação retroativa que não sobrescreve); seções renderizando no `outros/editar.php`. **Módulos 05/06/07 100% testados.** |
| 21/07/2026 | **Módulo 09 — Contas Financeiras** implementado e testado HTTP+banco: `sql/migration_modulo_09_contas.sql` (`contas_financeiras` + `conta_saldos`; enums de `documentos` +'conta_financeira'/'extrato'), `ContaFinanceira`/`ContaSaldo` models, `ContasController` (index/novo/editar/saldo), views `contas/*` com partial `_campos.php`, rotas, `proximo_codigo_conta()` (CF-XXXX), ícone "Contas" 🏦 ativo no dashboard com badge. Campos de integração Asaas preparados (integracao/account_id/wallet_id/api_key + origem 'asaas' no histórico de saldos). Corrigido `uploads/` sem permissão de escrita p/ Apache (causa dos 500 + cadastros duplicados de 20/07) — `chmod -R 777` local. |
| 21/07/2026 | **2 reuniões com César** (transcrições analisadas). Novas seções R1–R4 no TAREFAS: navegação pelo menu lateral + "Gestão Geral" consolidada, ícones realistas/fonte/sons/animação (aguardando referências), IA para cadastro assistido, fluxo e-mail→n8n→Contas a Pagar com baixa por comprovante/IA e integração Conta Azul/Asaas. **Módulo 01 — Pessoas virou prioridade** (card com WhatsApp, cadastro completo com família/sucessão/testamento). Combinado: finalizar módulos/campos primeiro, usabilidade depois; César testa cadastros e manda ajustes. |
| 28/07/2026 | **R2 — Animação "segundo cérebro":** hub da ficha do imóvel (`imoveis/ficha.php`) deixou de ser layout estático e virou **grafo interativo d3-force** (`assets/js/hub-grafo.js`, D3 v7 via CDN, `.hg-*` no CSS): nós flutuam/reagem ao arraste, hover/segurar realça em lilás os conectados, clique abre o modal (fallback p/ grade de botões sem D3). Testado HTTP (render 200, container+dados+scripts presentes; interação é client-side). |
| 28/07/2026 | **R1 — Chassi de navegação:** menu lateral virou navegação principal em grupos (Geral: Gestão Geral+Clientes · [Cliente]: Dashboard/Patrimônios/Contas), fixo no desktop (≥1024px) e drawer no mobile; seletor do topo removido → chip do cliente + seleção via bootstrap (`?cliente_id` antes dos controllers, corrige regressão do redirect); botão Voltar global; nova tela **Gestão Geral** (`GestaoGeralController` + view + rota) com KPIs consolidados + grade de clientes = visão gestor separada do Dashboard (visão cliente). `DashboardController` redireciona admin-sem-cliente p/ Gestão Geral. Testado HTTP (todas as telas 200, seleção/ativo/chip OK). Falta: caixa geral+tarefas multi-cliente (dep. módulos), dashboard do cliente com dados reais, teste hover no celular. |
| 28/07/2026 | **Padronização de ícones (R2, paliativo até refs do César):** hub da ficha do imóvel migrado de Bootstrap Icons flat p/ emoji realista igual ao dashboard (📋💰🔨🛠️🏢🔑📁 + `.hub-no-emoji` no CSS); `/patrimonio` alinhado (Imóveis 🏠→🏛️). |
| 12/08/2026 | **Módulo 03 — Empresas:** cadastro de empresas/holdings do cliente. `sql/migration_modulo_03_empresas.sql` (tabelas `empresas` + `empresa_socios`; `documentos` tipo_referencia += 'empresa', categoria += 'contrato_social'; schema.sql sincronizado). `Empresa` model (principal + quadro societário com escopo por cliente/sem IDOR, `participacaoTotal`), `EmpresasController` (index/novo/editar/socio/socioRemover), views `empresas/{lista,novo,editar,_campos}.php`, rotas, `proximo_codigo_empresa()` (EM-XXXX) + labels natureza/regime/função. Quadro societário 1:N na edição (add/remove sócios, conferência do 100%). App 🏢 no dashboard com badge + item no menu. Testado HTTP+banco (holding "Road Empreendimentos e Participações" EM-0001 com 2 sócios 70%/30% = 100%) + navegador. ⚠️ Migration pendente em produção. |
| 11/08/2026 | **Módulo 11 — Seguros:** cadastro central de apólices. `sql/migration_modulo_11_seguros.sql` (tabela `seguros` com vínculo polimórfico item_tipo+item_id, vigência, valores, cobertura/beneficiários, status; `tipo_referencia` de `documentos` += 'seguro'; schema.sql sincronizado). `Seguro` model (INSERT/UPDATE genérico, `buscarDoCliente` sem IDOR, `itensSeguraveis`/`descreverVinculo`), `SegurosController`, views `seguros/{lista,novo,editar,_campos}.php`, rotas, `proximo_codigo_seguro()` (SG-XXXX) + `seguro_tipo_label()`. Vínculo opcional por select único "tipo:id" com optgroups (imóveis/veículos/outros). **Alimenta a Agenda** (novo UNION branch pela `vigencia_fim`/status vigente), app 🛡️ no dashboard com badge + item no menu. Testado HTTP+banco (criar/editar seguro do veículo do Marcos → SG-0001, aparece na lista/agenda/badges) + navegador. ⚠️ Migration pendente em produção. |
| 11/08/2026 | **Módulo 14 — Agenda e Alertas:** motor de alertas `alertas_consolidado(?cliente_id)` em `includes/functions.php` (UNION varrendo IPTU em aberto, licenciamento/seguro/revisão de veículos, seguro/manutenção de outros bens, fim de contrato de locação e documentos com validade) + helpers `dias_ate`/`alerta_status`/`alertas_resumo`. Novo `AgendaController` + rota `agenda` + view `agenda/index.php` (baldes Vencidos/7d/30d/Mais adiante, cores por urgência, chips de resumo). Escopo por cliente ou geral. Integrado: item "Agenda" no menu lateral (`header.php`), app 📅 no dashboard com badge de urgentes, faixa de alertas clicável na Gestão Geral. Fix de collation no UNION (`veiculos` general_ci × demais unicode_ci → `COLLATE utf8mb4_unicode_ci`, sem ALTER). Testado HTTP (curl) + navegador: 4 baldes renderizando, badges corretos (3 urgentes p/ Marcos com datas de teste, revertidas depois). |
| 11/08/2026 | **Módulo 15 — Dashboard Executivo (patrimônio consolidado):** novo helper `patrimonio_consolidado(?cliente_id)` em `includes/functions.php` (soma valor de mercado de imóveis/veículos/outros bens + saldo BRL de contas, com fallback de valor por bem). **Gestão Geral** (`GestaoGeralController` + view) ganhou herói "Patrimônio total sob gestão" (preto&dourado) com barra de composição empilhada + legenda (valor/qtd/%) e passou a exibir o patrimônio de cada cliente nos cards. **Dashboard do cliente** (`DashboardController` + view) ganhou painel de patrimônio total + quebra por categoria com barras de participação, acima do hub de apps. Testado HTTP (curl, login César→Marcos) + navegador: total R$ 4.048.750,00, sem erros PHP, layout consistente com o tema. Substitui os KPIs antigos (imóveis/contas soltos) da Gestão Geral. |
| 28/07/2026 | **Commit + deploy do Módulo 09 — Contas Financeiras** (estava pronto mas sem versionar). **Módulo 01 — Pessoas implementado e testado HTTP+banco:** `migration_modulo_01_pessoas.sql` (amplia `clientes` + 5 sub-tabelas 1:N + enum de documentos), `Cliente` migrado p/ INSERT/UPDATE genérico, novo model `Pessoa` (allow-list + DELETE por cliente_id, sem IDOR), `ClientesController` reescrito (cards com WhatsApp, cadastro completo, sub-seções, upload de testamento, itemRemover), partial `clientes/_campos.php`, rota `clientes/item-remover`, helpers `link_whatsapp`/`estado_civil_label`/`parentesco_label`. Falta só "Relacionamentos" (→ F3). ⚠️ Pendente rodar as 2 migrations (09 e 01) no banco de **produção**. |

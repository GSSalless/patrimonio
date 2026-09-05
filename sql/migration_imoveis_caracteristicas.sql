-- ============================================================================
-- MIGRATION — Sincroniza a tabela `imoveis` com o que a aplicação grava
--
-- Motivo: o `ImoveisController` grava colunas de matrícula, características
-- físicas, co-propriedade, avaliação e status documental que foram adicionadas
-- em produção ao longo do tempo, mas NUNCA entraram no `sql/schema.sql` nem em
-- migration. Resultado: um banco criado do zero a partir do schema antigo
-- quebrava (erro 500) ao salvar/editar imóvel, e a futura criação de bancos
-- novos (staging, multi-tenant) herdaria o mesmo problema.
--
-- Esta migration deixa qualquer banco igual ao que o código espera.
-- Idempotente (ADD COLUMN IF NOT EXISTS — MariaDB/Hostinger): rodar de novo não
-- quebra. Em produção provavelmente já está tudo aplicado (o app funciona lá);
-- rodar aqui apenas confirma. Fresh/staging: cria o que falta.
--
-- COMO RODAR (phpMyAdmin): selecione o banco (ex.: u250260449_cezar_db),
-- aba SQL, cole este arquivo e Executar. NÃO há linha USE.
-- ============================================================================

ALTER TABLE imoveis
  -- Localização (links de mapa)
  ADD COLUMN IF NOT EXISTS link_maps        VARCHAR(800) NULL AFTER pais,
  ADD COLUMN IF NOT EXISTS link_street_view VARCHAR(800) NULL AFTER link_maps,

  -- Matrícula / cartório
  ADD COLUMN IF NOT EXISTS numero_matricula VARCHAR(60)  NULL AFTER inscricao_municipal,
  ADD COLUMN IF NOT EXISTS cartorio         VARCHAR(180) NULL AFTER numero_matricula,
  ADD COLUMN IF NOT EXISTS comarca          VARCHAR(120) NULL AFTER site_cartorio,
  ADD COLUMN IF NOT EXISTS livro            VARCHAR(40)  NULL AFTER comarca,
  ADD COLUMN IF NOT EXISTS folha            VARCHAR(40)  NULL AFTER livro,
  ADD COLUMN IF NOT EXISTS data_matricula   DATE         NULL AFTER folha,

  -- Titularidade / co-propriedade
  ADD COLUMN IF NOT EXISTS percentual_participacao DECIMAL(5,2) NULL AFTER forma_aquisicao,

  -- Características físicas
  ADD COLUMN IF NOT EXISTS area_privativa   DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS area_total       DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS area_construida  DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS area_comum       DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS area_terreno     DECIMAL(10,2) NULL,
  ADD COLUMN IF NOT EXISTS quartos          INT          NULL,
  ADD COLUMN IF NOT EXISTS suites           INT          NULL,
  ADD COLUMN IF NOT EXISTS banheiros        INT          NULL,
  ADD COLUMN IF NOT EXISTS lavabos          INT          NULL,
  ADD COLUMN IF NOT EXISTS vagas_garagem    INT          NULL,
  ADD COLUMN IF NOT EXISTS andar            INT          NULL,
  ADD COLUMN IF NOT EXISTS numero_unidade   VARCHAR(30)  NULL,
  ADD COLUMN IF NOT EXISTS face_solar       VARCHAR(30)  NULL,
  ADD COLUMN IF NOT EXISTS posicao_imovel   VARCHAR(60)  NULL,
  ADD COLUMN IF NOT EXISTS vista            VARCHAR(120) NULL,

  -- Avaliação / financeiro
  ADD COLUMN IF NOT EXISTS valor_contabil   DECIMAL(15,2) NULL AFTER data_avaliacao_mercado,
  ADD COLUMN IF NOT EXISTS empresa_avaliadora VARCHAR(180) NULL AFTER valor_contabil,
  ADD COLUMN IF NOT EXISTS valor_m2         DECIMAL(15,2) NULL AFTER empresa_avaliadora,
  ADD COLUMN IF NOT EXISTS custo_seguro     DECIMAL(10,2) NULL AFTER custo_internet,

  -- Status documental (checklist do cadastro)
  ADD COLUMN IF NOT EXISTS doc_status_matricula_atualizada TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS doc_status_certidao_negativa    TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS doc_status_habite_se            TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS doc_status_convencao_condominio TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS doc_status_planta_aprovada      TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS doc_status_alvara               TINYINT(1) NOT NULL DEFAULT 0;

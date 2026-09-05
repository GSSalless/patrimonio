# Inventário de Campos por Categoria de Ativo — Revisão do MVP

> **Data:** 05/09/2026 · **Origem:** M1 do MVP (reunião 27/08) — "revisar os campos necessários por
> categoria (jet-ski, carro, imóvel…), enxugar excesso".
> **Método:** análise estática cruzando, para cada coluna, se ela é **escrita** (Controller),
> **lida/exibida** (views) e se tem **campo no formulário** (`name=`). Coluna sem escrita nem
> leitura = **morta**. Nenhuma decisão altera dados existentes sem SQL versionado.

---

## Resumo executivo

| Categoria | Tabela | Colunas | Situação |
|-----------|--------|---------|----------|
| Imóveis | `imoveis` | 67 *(era 74)* | Limpo após remover 7 campos mortos · schema versionado corrigido |
| Veículos | `veiculos` | 29 | **Sem excesso** — todos os campos usados |
| Outros bens (embarcação/joia/obra) | `outros_bens` | 36 | Tabela unificada com campos **condicionais por tipo** — sem morto |

**Alterações de banco geradas (rodar no phpMyAdmin — ver ordem no fim):**
1. `sql/migration_imoveis_caracteristicas.sql` — **adiciona** as colunas que o app já usa e faltavam no schema (matrícula, áreas, co-propriedade, avaliação, `custo_seguro`, links de mapa).
2. `sql/migration_imoveis_remover_campos_mortos.sql` — **remove** 7 colunas mortas (`doc_status_*` ×6 + `pais`).

---

## 1) Imóveis (`imoveis`)

### ✂️ Removido (campos mortos — código + banco)
| Campo | Por quê |
|-------|---------|
| `doc_status_matricula_atualizada` | Mecanismo abandonado. Eram gravadas sempre como `0` no cadastro e **nenhuma tela lia**. O checklist documental da ficha é calculado pelas categorias presentes na tabela `documentos`, não por estas colunas. |
| `doc_status_certidao_negativa` | idem |
| `doc_status_habite_se` | idem |
| `doc_status_convencao_condominio` | idem |
| `doc_status_planta_aprovada` | idem |
| `doc_status_alvara` | idem |
| `pais` | Nunca escrita nem lida pelo app; MVP é Brasil. Recriável na fase de internacionalização. |

> Código: método `docStatus()` e seu `array_merge` removidos do `ImoveisController`. Sem referência órfã.

### 🩹 Corrigido antes (defasagem de schema)
Colunas que o app **já gravava** mas faltavam no `schema.sql` (banco novo quebrava no cadastro):
matrícula (`numero_matricula`, `cartorio`, `comarca`, `livro`, `folha`, `data_matricula`),
características físicas (`area_privativa/total/construida/comum/terreno`, `quartos`, `suites`,
`banheiros`, `lavabos`, `vagas_garagem`, `andar`, `numero_unidade`, `face_solar`, `posicao_imovel`, `vista`),
co-propriedade (`percentual_participacao`), avaliação (`valor_contabil`, `empresa_avaliadora`, `valor_m2`),
`custo_seguro`, `link_maps`, `link_street_view`. → Adicionadas ao schema + migration.

### 💡 Recomendações (dependem do César — não alteradas)
- **Exibir na ficha** os campos que hoje são capturados no formulário mas **não aparecem na ficha**
  de leitura: `quartos`, `suites`, `banheiros`, `lavabos`, `vagas_garagem` e `custo_seguro`
  (o dado é salvo e editável, só falta mostrar no resumo). *Enhancement — não é bug.*
- Avaliar se `area_construida` **e** `area_comum` **e** `area_terreno` são todas necessárias no MVP
  (possível excesso conforme o tipo — casa × apartamento × terreno).

---

## 2) Veículos (`veiculos`) — sem alteração

Módulo enxuto e coerente: **todos os 29 campos** são escritos, lidos e têm campo no formulário.
Identificação (marca, modelo, ano fab./modelo, cor, combustível, placa, renavam, chassi),
documentação (licenciamento, multas), seguro (seguradora, apólice, franquia, vencimento),
financeiro (aquisição, FIPE, mercado), controle (km, consumo). **Nada a remover.**

Sub-tabelas relacionadas (já OK): `abastecimentos`, `veiculo_manutencoes`, `sinistros`.

---

## 3) Outros bens (`outros_bens`) — sem alteração

Tabela **unificada** para embarcação (jet-ski/lancha/barco), joia e obra de arte. Cada tipo usa um
subconjunto dos campos (mostrados/ocultados por JS conforme o tipo — `_campos_tipo.php`). Não há
coluna morta; o "excesso" é natural da unificação. Mapa por categoria:

| Bloco | Campos |
|-------|--------|
| **Comum (todos)** | `tipo`, `nome`, `descricao`, `marca`, `modelo`, `ano`, `cor`, `seguradora`, `apolice`, `franquia`, `vencimento_seguro`, `data_aquisicao`, `valor_aquisicao`, `valor_mercado`, `foto_principal`, `observacoes` |
| **Embarcação / jet-ski** | `tipo_embarcacao`, `comprimento_m`, `motor`, `horimetro`, `registro_embarcacao`, `marina`, `vaga_marina`, `mensalidade_marina` |
| **Joia** | `material`, `quilates`, `certificado` |
| **Obra de arte** | `artista_autor`, `dimensoes`, `tecnica`, `certificado` |

Sub-tabelas relacionadas (já OK): `bem_manutencoes` (embarcação), `avaliacoes_bem` (histórico de valor).

> **Decisão de produto (César):** se quiser tratar **jet-ski, lancha e barco** como categorias
> separadas (cada uma com seus próprios campos, como ele citou na reunião), o caminho é dividir o
> `tipo` atual em subtipos com campos específicos — mudança maior, fica para uma próxima rodada.

---

## Ordem para aplicar no banco (phpMyAdmin — banco `u250260449_cezar_db`)

> Publique o **código novo** (deste commit) **antes** de rodar o SQL de remoção, para não haver
> janela em que o código tente gravar numa coluna já removida.

1. `sql/migration_imoveis_caracteristicas.sql` *(adiciona; idempotente — em produção provavelmente já existe, confirma)*
2. `sql/migration_imoveis_remover_campos_mortos.sql` *(remove os 7 campos mortos)*

Ambos são idempotentes (`IF NOT EXISTS` / `IF EXISTS`) e **não têm linha `USE`** — selecione o banco antes.

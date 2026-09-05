# Sistema de Gestão Patrimonial — César Cordeiro
**Responsável técnico:** Gilson Sales  
**Cliente:** César Cordeiro (Family Office — gestão de clientes de alto patrimônio)  
**Cliente-piloto:** Marcos Borges (CPF + CNPJ "Road")  
**Versão spec:** 1.0 — 23/06/2026  
**Stack:** PHP + MySQL (XAMPP) · HTML/CSS/JS · sem framework pesado · Web/PWA

---

## Contexto do Projeto

Sistema web modular para substituir planilhas Excel do César. Centrado no **cliente (pessoa)**: o patrimônio (imóveis, carros, joias) fica pendurado na pessoa. A navegação sempre parte do cliente.

Dois níveis de acesso:
- **César** — vê todos os clientes que gerencia (seletor no topo)
- **Cliente** — vê apenas o próprio patrimônio

---

## Arquitetura / Decisões Técnicas

- **Linguagem backend:** PHP (XAMPP local → produção em servidor compartilhado)
- **Banco:** MySQL — charset `utf8mb4`
- **Frontend:** HTML semântico + CSS (sem Bootstrap, sem React) + JS vanilla
- **Upload de arquivos:** PDF e imagem — armazenados em `/uploads/{cliente_id}/`
- **Sem frameworks JS pesados** — manter simples e manutenível
- **PWA** — `manifest.json` + `service-worker.js` entram na F2/F3
- **URL base local:** `http://localhost/cezar/`

---

## Modelo de Dados (entidades e relações)

```
Cliente (PF/PJ)
   └─1:N─ Imovel
            ├─1:N─ Reforma            (custo previsto x realizado, status, docs)
            ├─1:N─ ContratoLocacao    (locatário, aluguel, vigência, índice)
            ├─1:N─ LancamentoFinanceiro (IPTU / condomínio / gasto / receita)
            │         └─ N:1 Caixa do cliente
            ├─1:N─ Documento          (polimórfico: imóvel, reforma, contrato, pessoa)
            ├─1:N─ Avaliacao          (data + valor — histórico de valorização)
            ├─1:N─ Seguro             (apólice, cobertura, vencimento)
            ├─1:N─ Manutencao         (data, serviço, fornecedor, valor, garantia)
            └─N:1─ Condominio         (vários imóveis no mesmo condomínio)
```

---

## Faseamento

| Fase | Foco | Entrega |
|------|------|---------|
| **F1** | MVP — substituir Excel | Login · Seletor de cliente · Lista de imóveis · Cadastro de imóvel (blocos 1‑4,6) · Abas: Resumo / Financeiro / Reformas / Aluguel / Documentos · IPTU · Condomínio com boleto |
| **F2** | Cadastro patrimonial completo | Matrícula, características físicas, co-propriedade, condomínio detalhado, seguros, manutenção, checklist documental |
| **F3** | Family Office / inteligência | Indicadores (yield, ROI, TIR), valorização automática por IA/API, alertas de vencimento, relacionamentos, integração Drive |

---

## Estrutura de Pastas do Projeto (MVC — sem framework)

> Migrado em 07/2026 do modelo procedural (uma pasta por módulo com `lista/novo/editar.php`)
> para **MVC leve, sem framework**.
>
> **Estrutura achatada (09/07/2026):** a hospedagem serve a própria pasta do projeto — **não há
> subpasta `public/`**. O `index.php` da raiz é o front controller; as pastas de código
> (`app`, `config`, `includes`, `routes`, `sql`) e o `.env` são bloqueados por `.htaccess`
> (`Require all denied`) já que ficam dentro da raiz web. A **URL base vem do `.env`**
> (`BASE_URL`) — local `/cezar/`, produção `/` (raiz do domínio) ou `/cesar/` (subpasta).

```
/cezar/                    ← RAIZ WEB (a própria pasta é servida)
├── index.php              ← front controller (bootstrap → Router → Controller)
├── .htaccess              ← rotas limpas → index.php · bloqueia .env / *.sql / *.md
├── routes/
│   ├── web.php            ← tabela de rotas (URLs limpas: /imoveis, /imoveis/ficha?id=…)
│   └── .htaccess          ← Require all denied
├── config/
│   ├── app.php            ← config geral (base_url via Env, env)
│   └── .htaccess          ← Require all denied
├── app/                   ← (+ .htaccess Require all denied)
│   ├── bootstrap.php      ← config + autoload (Core/Controllers/Models) + sessão
│   ├── config.php         ← define APP_ROOT, APP_PATH, BASE_URL; carrega db/functions/auth
│   ├── Core/              ← Router, Controller (base), View, Env
│   ├── Controllers/       ← Auth, Dashboard, Clientes, Imoveis, Veiculos, Outros, Reformas,
│   │                          Manutencoes, Locacao, Financeiro, Condominios, Documentos, Patrimonio
│   ├── Models/            ← acesso a dados (INSERT/UPDATE genérico por array coluna=>valor)
│   └── Views/             ← <modulo>/<acao>.php (+ partials _campos*.php compartilhados)
├── includes/              ← infra compartilhada (+ .htaccess Require all denied)
│   ├── db.php             ← conexão PDO (lê credenciais do .env via Env)
│   ├── auth.php           ← sessão e permissões (login, exige_login, exige_admin)
│   ├── functions.php      ← helpers (h, moeda, base_url, pendências, uploads…)
│   └── header.php / footer.php  ← layout, reaproveitado pelas Views
├── .env                   ← credenciais + BASE_URL (fora do git) · .env.example versionável
├── uploads/               ← arquivos enviados (servida direto) · fora do git
├── assets/  (css/js)   ·  sql/ (schema.sql, seed.sql, migration_*.sql — bloqueada via .htaccess)
├── CLAUDE.md · TAREFAS.md · tarefas.html
```

**Padrão para migrar/criar um módulo:** Model (`app/Models`) + Controller (`app/Controllers`,
chama `exige_login`/`exige_admin`) + Views (`app/Views/<mod>/`, reusam header/footer via
`APP_ROOT`) + rota em `routes/web.php`. Formulário compartilhado entre novo/editar vai num
partial `_campos.php`. **Testar as URLs limpas no Apache** (`curl http://localhost/cezar/…`) —
o `php -S` embutido NÃO processa `.htaccess`.

---

## Regras de Negócio Críticas

1. Todo imóvel pertence a um único proprietário (Cliente). Co-propriedade usa percentual (F2).
2. Custos mensais e reformas **geram lançamentos no caixa** do cliente.
3. Condomínio é registrado **por competência** (valor variável + boleto).
4. IPTU é **anual**, podendo ser parcelado, com controle de vencimento.
5. Documentos aceitam **PDF ou imagem** — armazenar com data de emissão e validade.
6. Navegação **sempre parte do cliente** (centrado na pessoa).
7. **Trabalho sempre completo:** toda alteração em formulário de cadastro deve ser espelhada na tela de edição correspondente (`app/Views/<mod>/novo.php` ↔ `editar.php`, ou o partial `_campos.php` quando compartilhado) e **toda mudança precisa ser analisada quanto ao impacto no banco**. Se adicionar/remover/renomear campo: aplicar o `ALTER TABLE` no banco `gestao_patrimonial`, atualizar `sql/schema.sql`, incluir a coluna no array de campos do Model/Controller (o INSERT/UPDATE é montado por `array_keys`, então basta o array bater com as colunas), e considerar a exibição na ficha (`app/Views/imoveis/ficha.php`).

---

## Fora de Escopo (não implementar ainda)

- Conciliação bancária automática
- Integração Conta Azul / XP Investimentos
- Área de visualização do cliente final (vem depois)
- Módulos de carros, joias, caixas

---

## Convenções de Código

- PHP: `snake_case` para variáveis e funções, `CamelCase` para classes
- Banco: tabelas no plural, `snake_case` (ex.: `lancamentos_financeiros`)
- Sempre usar **PDO com prepared statements** — nunca concatenar SQL com input do usuário
- Datas no banco em `DATE` / `DATETIME` (formato ISO) — exibir em `d/m/Y` no front
- Valores monetários: `DECIMAL(15,2)` no banco, formatar como `R$ 1.234,56` no front
- IDs: `INT AUTO_INCREMENT PRIMARY KEY` — prefixo de exibição gerado na aplicação (ex.: IM-0001)

---

## Como Iniciar uma Nova Sessão

1. Ler este arquivo (`CLAUDE.md`)
2. Ler `TAREFAS.md` — verificar qual tarefa está em andamento
3. Checar `sql/schema.sql` para entender o estado atual do banco
4. Confirmar com o usuário qual tarefa atacar

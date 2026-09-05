# 📘 Guia rápido de Git & Deploy — Patrimônio

Cola de bolso pro dia a dia. Repositório: `github.com/GSSalless/patrimonio`

---

## 🔁 O ciclo do dia a dia (o que você repete sempre)

Depois de mexer em qualquer arquivo do projeto:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/cezar

git status            # 1. vê o que mudou (opcional, só pra conferir)
git add -A            # 2. separa TODAS as mudanças
git commit -m "descrição do que você fez"   # 3. "tira a foto"
git push              # 4. envia pro GitHub (nuvem)
```

> **Pense assim:** `commit` = salvar no seu Mac · `push` = mandar cópia pra nuvem.

**Boas mensagens de commit** (diga *o que* mudou):
- ✅ `git commit -m "Ajusta cor do botão de salvar no cadastro de imóvel"`
- ✅ `git commit -m "Corrige cálculo do IPTU parcelado"`
- ❌ `git commit -m "mudanças"` (não ajuda a lembrar depois)

---

## 🌐 Ambientes (local x produção)

O arquivo `.env` guarda as senhas e **nunca vai pro git**. Cada lugar tem o seu:

| Onde | Arquivo modelo | Banco | BASE_URL |
|------|----------------|-------|----------|
| Seu Mac (XAMPP) | `.env.local` | `gestao_patrimonial` (local) | `/cezar/` |
| Servidor (Hostinger) | `.env.producao` | `u250260449_cezar_db` | `/` |

**Trocar o ambiente do seu Mac:**
```bash
cp .env.local .env       # volta pro banco LOCAL (o normal do dia a dia)
```

⚠️ Se o site local abrir a página "Welcome to XAMPP", quase sempre é o `.env`
com `BASE_URL=/` (config de produção). Rode `cp .env.local .env` e recarregue.

---

## 🚀 Deploy pra produção (subir pro site por FTP)

O site é atualizado por FTP / Gerenciador de Arquivos da Hostinger. O `deploy.sh`
te diz **só** o que mudou, pra você não subir o projeto inteiro à toa:

```bash
./deploy.sh            # monta a pasta deploy_pacote/ só com os arquivos alterados
# → suba o CONTEÚDO de deploy_pacote/ pro servidor (mesma estrutura de pastas)
./deploy.sh marcar     # depois de subir tudo, marca "este ponto já está no ar"
```

> **Nunca** suba o arquivo `.env` — o servidor tem o `.env` de produção dele.

---

## 🆘 Comandos de socorro

```bash
git log --oneline           # histórico de commits
git diff                     # o que mudou desde o último commit (ainda não commitado)
git checkout -- arquivo.php  # DESFAZ mudanças não commitadas de um arquivo
git pull                     # baixa do GitHub (se mexeu em outro lugar/máquina)
```

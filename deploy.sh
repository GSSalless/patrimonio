#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
# deploy.sh — ajudante de deploy manual (FTP / Gerenciador de Arquivos)
#
# O git sabe exatamente o que mudou. Este script usa isso para você
# só precisar subir os arquivos ALTERADOS, e não o projeto inteiro.
#
#   ./deploy.sh            → mostra o que mudou desde o último deploy
#                            e monta a pasta  deploy_pacote/  (+ .zip)
#                            com SÓ esses arquivos, prontos pra subir.
#
#   ./deploy.sh marcar     → depois de subir tudo no servidor, rode isto.
#                            Marca "este ponto já está publicado".
# ─────────────────────────────────────────────────────────────
set -e
cd "$(dirname "$0")"

TAG="deploy"                     # marcador do último ponto publicado
PACOTE="deploy_pacote"           # pasta temporária com os arquivos a subir

# --- subcomando: marcar o ponto atual como já publicado ---------
if [ "$1" = "marcar" ]; then
  git tag -f "$TAG" >/dev/null
  echo "✅ Ponto atual marcado como publicado (tag '$TAG' movida para o commit atual)."
  echo "   A próxima vez que rodar ./deploy.sh, ele mostra só o que mudar a partir daqui."
  exit 0
fi

# --- descobre a base de comparação ------------------------------
if git rev-parse -q --verify "$TAG" >/dev/null; then
  BASE="$TAG"
  echo "Comparando com o último deploy (tag '$TAG')…"
else
  BASE=""
  echo "⚠️  Ainda não existe um deploy marcado."
  echo "   Nesta primeira vez, considere TODOS os arquivos versionados como 'a subir'."
fi

# --- lista de arquivos alterados (Adicionados/Modificados) ------
if [ -n "$BASE" ]; then
  ALTERADOS=$(git diff --name-only --diff-filter=ACMR "$BASE" HEAD)
  APAGADOS=$(git diff --name-only --diff-filter=D "$BASE" HEAD)
else
  ALTERADOS=$(git ls-files)      # primeira vez: tudo
  APAGADOS=""
fi

if [ -z "$ALTERADOS" ] && [ -z "$APAGADOS" ]; then
  echo "Nada mudou desde o último deploy. 👍"
  exit 0
fi

echo
echo "── Arquivos a SUBIR (novos/alterados) ─────────────────────"
echo "${ALTERADOS:-  (nenhum)}"

if [ -n "$APAGADOS" ]; then
  echo
  echo "── Arquivos APAGADOS (remova estes no servidor à mão) ─────"
  echo "$APAGADOS"
fi

# --- monta a pasta deploy_pacote/ preservando os caminhos -------
rm -rf "$PACOTE" "$PACOTE.zip"
if [ -n "$ALTERADOS" ]; then
  while IFS= read -r f; do
    [ -z "$f" ] && continue
    mkdir -p "$PACOTE/$(dirname "$f")"
    cp "$f" "$PACOTE/$f"
  done <<< "$ALTERADOS"
  ( cd "$PACOTE" && zip -qr "../$PACOTE.zip" . )
  echo
  echo "📦 Pronto: pasta '$PACOTE/' e '$PACOTE.zip' criados com os arquivos acima."
  echo "   Suba o CONTEÚDO de '$PACOTE/' para a pasta do projeto no servidor,"
  echo "   mantendo a mesma estrutura de pastas."
fi

echo
echo "➡️  Depois de subir tudo e conferir no site, rode:  ./deploy.sh marcar"

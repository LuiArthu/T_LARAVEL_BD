#!/usr/bin/env bash
# =====================================================================
# BACKUP do banco do sistema (Neon) com pg_dump.
#
#   bash database/backup/backup.sh
#
# Gera em storage/backups/:
#   academia_<data>.dump        backup completo (formato custom, compactado)
#   academia_<data>.log         saída detalhada do pg_dump (evidência)
#   academia_<data>.origem.txt  linhas + checksum de cada tabela no momento
#                               do backup (para comparar após restaurar)
# A conexão vem do DB_URL do .env.
# =====================================================================
set -euo pipefail
cd "$(dirname "$0")/../.."

PG_BIN=/usr/lib/postgresql/18/bin
if [ ! -x "$PG_BIN/pg_dump" ]; then
    echo "pg_dump 18 não encontrado. O Neon usa PostgreSQL 18: instale o postgresql-client-18." >&2
    exit 1
fi

URL=$(grep '^DB_URL=' .env | cut -d= -f2- | tr -d '"')
[ -n "$URL" ] || { echo "DB_URL vazio no .env" >&2; exit 1; }

DIR=storage/backups
mkdir -p "$DIR"
BASE="$DIR/academia_$(date +%Y-%m-%d_%H%M%S)"

echo "== BACKUP  $(date '+%d/%m/%Y %H:%M:%S')"
echo "   servidor: $("$PG_BIN/psql" "$URL" -XAtc 'SHOW server_version')  |  banco: $("$PG_BIN/psql" "$URL" -XAtc 'SELECT current_database()')"
echo "   ferramenta: $("$PG_BIN/pg_dump" --version)"

"$PG_BIN/pg_dump" "$URL" \
    --format=custom --no-owner --no-privileges --verbose \
    --file "$BASE.dump" 2> "$BASE.log"

"$PG_BIN/psql" "$URL" -X -q -f database/backup/verificar.sql > "$BASE.origem.txt"

echo "   arquivo:  $BASE.dump ($(du -h "$BASE.dump" | cut -f1))"
echo "   sha256:   $(sha256sum "$BASE.dump" | cut -d' ' -f1)"
echo "   conteúdo: $(grep -c '^tabela' "$BASE.origem.txt") tabelas, $(grep -c '^rotina' "$BASE.origem.txt") functions/procedures, $(grep -c '^trigger' "$BASE.origem.txt") triggers, $(grep -c '^indice' "$BASE.origem.txt") índices"
echo "   log:      $BASE.log"
echo "OK. Para restaurar: bash database/backup/restaurar.sh $BASE.dump"

#!/usr/bin/env bash
# =====================================================================
# RESTAURAÇÃO de um backup com pg_restore, em um banco SEPARADO do Neon
# (padrão: neondb_restore), sem tocar no banco que o sistema usa.
# No fim compara linhas + checksum de cada tabela com a origem.
#
#   bash database/backup/restaurar.sh storage/backups/academia_<data>.dump [banco_destino]
# =====================================================================
set -euo pipefail
cd "$(dirname "$0")/../.."

FILE=${1:?Informe o arquivo .dump}
DEST=${2:-neondb_restore}
PG_BIN=/usr/lib/postgresql/18/bin
[ -x "$PG_BIN/pg_restore" ] || { echo "Instale o postgresql-client-18." >&2; exit 1; }
[ -f "$FILE" ] || { echo "Arquivo não encontrado: $FILE" >&2; exit 1; }
[[ "$DEST" =~ ^[a-z_][a-z0-9_]*$ ]] || { echo "Nome de banco inválido: $DEST" >&2; exit 1; }

URL=$(grep '^DB_URL=' .env | cut -d= -f2- | tr -d '"')
ORIGEM=$("$PG_BIN/psql" "$URL" -XAtc 'SELECT current_database()')
if [ "$DEST" = "$ORIGEM" ]; then
    echo "Recusado: o destino ($DEST) é o banco do sistema. Use outro nome." >&2
    exit 1
fi
DEST_URL=$(echo "$URL" | sed -E "s#(@[^/]+/)[^?]+#\1$DEST#")
BASE="${FILE%.dump}"

echo "== RESTAURAÇÃO  $(date '+%d/%m/%Y %H:%M:%S')"
echo "   arquivo:  $FILE"
echo "   sha256:   $(sha256sum "$FILE" | cut -d' ' -f1)"
echo "   destino:  $DEST (o sistema continua usando $ORIGEM)"

# Banco de destino limpo a cada restauração.
"$PG_BIN/psql" "$URL" -X -q -c "DROP DATABASE IF EXISTS $DEST" -c "CREATE DATABASE $DEST"

"$PG_BIN/pg_restore" --dbname "$DEST_URL" \
    --no-owner --no-privileges --exit-on-error --verbose \
    "$FILE" 2> "$BASE.restore.log"

"$PG_BIN/psql" "$DEST_URL" -X -q -f database/backup/verificar.sql > "$BASE.restaurado.txt"

echo "   log:      $BASE.restore.log"
echo "== COMPARAÇÃO origem x restaurado"
if diff "$BASE.origem.txt" "$BASE.restaurado.txt" > "$BASE.diff.txt"; then
    echo "   IDÊNTICO: $(grep -c '^tabela' "$BASE.restaurado.txt") tabelas com as mesmas linhas e checksums, mesmas functions, procedures, triggers e índices."
else
    echo "   DIFERENÇAS encontradas (ver $BASE.diff.txt):"
    head -20 "$BASE.diff.txt"
    exit 1
fi

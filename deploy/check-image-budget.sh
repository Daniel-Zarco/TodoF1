#!/usr/bin/env bash
#
# Preflight de almacenamiento para las fotografías de TodoF1.
# Uso: bash deploy/check-image-budget.sh
# Variables: IMAGE_MAX_KB (def. 250), IMAGE_BUDGET_MB (def. 35).
set -euo pipefail

MAX_KB="${IMAGE_MAX_KB:-250}"
BUDGET_MB="${IMAGE_BUDGET_MB:-35}"
DIRS=(Images/monoplazas Images/heroes)

total=0
count=0
over=0
largest=()

while IFS= read -r -d '' file; do
  size=$(stat -c '%s' "$file")
  kb=$(( (size + 1023) / 1024 ))
  total=$(( total + size ))
  count=$(( count + 1 ))
  largest+=("$kb $file")
  if (( kb > MAX_KB )); then
    echo "::warning file=$file::Imagen de ${kb} KB supera el límite de ${MAX_KB} KB"
    over=$(( over + 1 ))
  fi
done < <(find "${DIRS[@]}" -type f \( -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.png' -o -iname '*.webp' -o -iname '*.avif' \) -print0)

echo "Imágenes revisadas: ${count}"
echo "Peso total: $(( total / 1024 / 1024 )) MB (${total} bytes)"
echo "Presupuesto: ${BUDGET_MB} MB; límite por imagen: ${MAX_KB} KB"

printf '%s\n' "${largest[@]}" | sort -nr | head -5 | while read -r kb file; do
  echo "  ${kb} KB  ${file}"
done

if (( over > 0 )); then
  echo "::error::${over} imagen(es) superan ${MAX_KB} KB"
  exit 1
fi

if (( total > BUDGET_MB * 1024 * 1024 )); then
  echo "::error::El conjunto de fotografías supera el presupuesto de ${BUDGET_MB} MB"
  exit 1
fi

echo "Presupuesto de imágenes: OK"

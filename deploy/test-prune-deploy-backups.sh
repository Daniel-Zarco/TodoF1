#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PRUNE="$SCRIPT_DIR/prune-deploy-backups.sh"
TMP="$(mktemp -d)"
trap 'rm -rf -- "$TMP"' EXIT

BACKUPS="$TMP/backups"
WWW="$TMP/www"
LOCK="$TMP/deploy.lock"
mkdir -p "$BACKUPS" "$WWW"
printf 'web activa\n' > "$WWW/index.html"

make_backup() {
  local name="$1"
  local dir="$BACKUPS/$name"
  mkdir -p "$dir/index.html" "$dir/Images" "$dir/api"
  printf 'backup %s\n' "$name" > "$dir/index.html/PagePrincipal.php"
  printf 'imagen\n' > "$dir/Images/foto.webp"
  printf 'api\n' > "$dir/api/health.php"
}

for name in \
  20260101-120001 \
  20260102-120002 \
  20260103-120003 \
  20260104-120004 \
  20260105-120005; do
  make_backup "$name"
done

mkdir -p "$BACKUPS/directorio-desconocido"
printf 'no borrar\n' > "$BACKUPS/directorio-desconocido/dato.txt"
mkdir -p "$BACKUPS/20260106-120006/index.html"
printf 'copia parcial\n' > "$BACKUPS/20260106-120006/index.html/PagePrincipal.php"
ln -s "$WWW" "$BACKUPS/20260107-120007"

run_prune() {
  TODOF1_BACKUP_DIR="$BACKUPS" \
  TODOF1_WWW_DIR="$WWW" \
  TODOF1_LOCK_FILE="$LOCK" \
  bash "$PRUNE" "$@"
}

fail() {
  echo "FALLO: $*" >&2
  exit 1
}

assert_all_original_backups() {
  for name in \
    20260101-120001 \
    20260102-120002 \
    20260103-120003 \
    20260104-120004 \
    20260105-120005; do
    [[ -d "$BACKUPS/$name" ]] || fail "falta la copia $name"
  done
}

dry_run_output="$(run_prune --dry-run --keep 2)"
grep -q 'Copias válidas encontradas: 5' <<< "$dry_run_output" || fail "dry-run no detectó 5 copias"
grep -q 'Copias a eliminar: 3' <<< "$dry_run_output" || fail "dry-run no detectó 3 candidatas"
assert_all_original_backups

exec 8>"$LOCK"
flock -n 8 || fail "no se pudo bloquear el lock de despliegue"
lock_output="$(run_prune --apply --keep 2)"
exec 8>&-
grep -q 'Hay un despliegue en curso' <<< "$lock_output" || fail "no se omitió con despliegue en curso"
assert_all_original_backups

apply_output="$(run_prune --apply --keep 2)"
grep -q 'Copias a eliminar: 3' <<< "$apply_output" || fail "apply no detectó 3 candidatas"

for name in 20260101-120001 20260102-120002 20260103-120003; do
  [[ ! -e "$BACKUPS/$name" ]] || fail "no se eliminó la copia antigua $name"
done
for name in 20260104-120004 20260105-120005; do
  [[ -d "$BACKUPS/$name" ]] || fail "no se conservó la copia reciente $name"
done

[[ -d "$BACKUPS/directorio-desconocido" ]] || fail "se eliminó un directorio desconocido"
[[ -f "$BACKUPS/directorio-desconocido/dato.txt" ]] || fail "se modificó un directorio desconocido"
[[ -d "$BACKUPS/20260106-120006" ]] || fail "se eliminó una copia parcial"
[[ -L "$BACKUPS/20260107-120007" ]] || fail "se eliminó un enlace simbólico"
[[ -f "$WWW/index.html" ]] || fail "se modificó la web activa"
[[ "$(cat "$WWW/index.html")" == "web activa" ]] || fail "cambió el contenido de la web activa"
grep -q 'applied=1' "$BACKUPS/prune.log" || fail "no se registró la limpieza aplicada"

second_output="$(run_prune --apply --keep 2)"
grep -q 'No hay copias antiguas que eliminar' <<< "$second_output" || fail "la segunda limpieza no fue idempotente"
[[ -d "$BACKUPS/20260104-120004" && -d "$BACKUPS/20260105-120005" ]] || fail "la segunda limpieza alteró las conservadas"

echo "OK: retención segura, 2 copias conservadas, web y elementos ajenos intactos."

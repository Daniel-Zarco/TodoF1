#!/usr/bin/env bash
#
# Retención segura de copias de despliegue de TodoF1.
#
# Por defecto SOLO simula (dry-run). Para borrar:
#   bash deploy/prune-deploy-backups.sh --apply
#
# Conserva las N copias válidas más recientes (def. 2) y nunca toca:
#   - la web activa (TODOF1_WWW_DIR)
#   - private/, bases de datos, correos, secretos ni la caché de Jolpica
#   - directorios que no sean copias de despliegue completas
#   - enlaces simbólicos
set -euo pipefail

REPO_DIR="${TODOF1_REPO_DIR:-/home/todof1/todof1-app}"
WWW_DIR="${TODOF1_WWW_DIR:-/home/todof1/www}"
BACKUP_DIR="${TODOF1_BACKUP_DIR:-/home/todof1/deploy-backups}"
KEEP="${TODOF1_BACKUP_KEEP:-2}"
MODE="dry-run"

usage() {
  cat <<'EOF'
Uso: prune-deploy-backups.sh [--dry-run|--apply] [--keep N] [--help]

  --dry-run   Solo muestra qué se conservaría y qué se eliminaría (defecto).
  --apply     Elimina las copias válidas más antiguas.
  --keep N    Copias válidas a conservar (defecto 2).
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run) MODE="dry-run" ;;
    --apply) MODE="apply" ;;
    --keep)
      if [[ $# -lt 2 ]]; then
        echo "Falta el valor de --keep" >&2
        exit 2
      fi
      KEEP="$2"
      shift
      ;;
    --help|-h) usage; exit 0 ;;
    *) echo "Opción no reconocida: $1" >&2; usage >&2; exit 2 ;;
  esac
  shift
done

if ! [[ "$KEEP" =~ ^[0-9]+$ ]] || (( KEEP < 1 )); then
  echo "KEEP debe ser un entero >= 1" >&2
  exit 2
fi

if [[ ! -d "$BACKUP_DIR" ]]; then
  echo "No existe el directorio de backups: $BACKUP_DIR"
  exit 0
fi

if [[ -L "$BACKUP_DIR" ]]; then
  echo "AVISO: $BACKUP_DIR es un enlace simbólico; no se procesa."
  exit 0
fi

BACKUP_REAL="$(readlink -f "$BACKUP_DIR")"
if [[ -z "$BACKUP_REAL" ]]; then
  echo "No se pudo resolver $BACKUP_DIR"
  exit 1
fi

if WWW_REAL="$(readlink -f "$WWW_DIR" 2>/dev/null)" && [[ -n "$WWW_REAL" ]]; then
  case "$BACKUP_REAL" in
    "$WWW_REAL"|"$WWW_REAL"/*)
      echo "AVISO: el directorio de backups está dentro de la web activa; no se procesa."
      exit 0
      ;;
  esac
fi

# No limpiar mientras hay un despliegue en curso. Un invocador que ya mantiene
# el lock puede marcar TODOF1_DEPLOY_LOCK_HELD=1 para omitir esta comprobación.
if [[ "${TODOF1_DEPLOY_LOCK_HELD:-0}" != "1" ]]; then
  DEPLOY_LOCK="${TODOF1_LOCK_FILE:-${TMPDIR:-/tmp}/todof1-deploy.lock}"
  exec 8>"$DEPLOY_LOCK" || { echo "No se pudo abrir el lock de despliegue"; exit 1; }
  if command -v flock >/dev/null 2>&1; then
    if ! flock -n 8; then
      echo "Hay un despliegue en curso; se omite la limpieza de backups."
      exit 0
    fi
  fi
fi

# Evita que dos limpiezas interfieran.
LOCK_FILE="$BACKUP_DIR/.prune.lock"
exec 9>"$LOCK_FILE" || { echo "No se pudo abrir el lock de limpieza"; exit 1; }
if command -v flock >/dev/null 2>&1; then
  if ! flock -n 9; then
    echo "Ya hay una limpieza de backups en curso; se omite esta ejecución."
    exit 0
  fi
fi

size_kb() {
  local value
  value="$(du -sk "$1" 2>/dev/null | awk '{print $1}')"
  echo "${value:-0}"
}

log_line() {
  printf '%s\t%s\t%s\n' "$(date '+%Y-%m-%dT%H:%M:%S%z')" "$MODE" "$*" >> "$BACKUP_DIR/prune.log"
}

valid=()
ignored=()
while IFS= read -r -d '' path; do
  base="$(basename "$path")"
  [[ "$base" =~ ^[0-9]{8}-[0-9]{6}$ ]] || continue
  [[ -d "$path" && ! -L "$path" ]] || continue

  # Una copia válida del deploy contiene estos tres bloques.
  if [[ ! -f "$path/index.html/PagePrincipal.php" || ! -d "$path/Images" || ! -d "$path/api" ]]; then
    ignored+=("$path")
    continue
  fi

  real="$(readlink -f "$path" 2>/dev/null || true)"
  case "$real" in
    "$BACKUP_REAL"/*) valid+=("$path") ;;
    *) ignored+=("$path") ;;
  esac
done < <(find "$BACKUP_DIR" -mindepth 1 -maxdepth 1 -type d -print0)

if (( ${#valid[@]} == 0 )); then
  echo "No hay copias de despliegue válidas en $BACKUP_DIR."
  log_line "valid=0 kept=0 removed=0 recoverable_kb=0"
  exit 0
fi

# Más reciente primero (el nombre YYYYMMDD-HHMMSS ordena lexicográficamente).
mapfile -t ordered < <(printf '%s\n' "${valid[@]}" | sort -r)
kept=("${ordered[@]:0:KEEP}")
removed=("${ordered[@]:KEEP}")

recoverable_kb=0
for path in "${removed[@]:-}"; do
  [[ -n "$path" ]] || continue
  recoverable_kb=$((recoverable_kb + $(size_kb "$path")))
done

echo "Directorio de backups: $BACKUP_DIR"
echo "Web activa (no se toca): $WWW_DIR"
echo "Copias válidas encontradas: ${#valid[@]}"
echo "Copias a conservar: ${#kept[@]} (máximo $KEEP)"
if (( ${#ignored[@]} > 0 )); then
  echo "Directorios ignorados (no son copias completas o están fuera del directorio): ${#ignored[@]}"
  printf '  IGNORADO %s\n' "${ignored[@]}"
fi

for path in "${kept[@]:-}"; do
  [[ -n "$path" ]] || continue
  echo "  CONSERVAR $(basename "$path") ($(size_kb "$path") KB)"
  log_line "KEEP $(basename "$path") kb=$(size_kb "$path")"
done

if (( ${#removed[@]} == 0 )); then
  echo "No hay copias antiguas que eliminar."
  log_line "valid=${#valid[@]} kept=${#kept[@]} removed=0 recoverable_kb=0"
  exit 0
fi

echo "Copias a eliminar: ${#removed[@]}"
for path in "${removed[@]}"; do
  echo "  ELIMINAR $(basename "$path") ($(size_kb "$path") KB)"
  log_line "REMOVE $(basename "$path") kb=$(size_kb "$path")"
done
echo "Espacio recuperable: $((recoverable_kb / 1024)) MB (${recoverable_kb} KB)"

if [[ "$MODE" == "dry-run" ]]; then
  echo "SIMULACIÓN: no se ha eliminado nada. Usa --apply con autorización."
  log_line "valid=${#valid[@]} kept=${#kept[@]} removed=${#removed[@]} recoverable_kb=${recoverable_kb} applied=0"
  exit 0
fi

for path in "${removed[@]}"; do
  # Guardas finales: patrón de copia, ruta dentro del directorio y sin symlink.
  base="$(basename "$path")"
  if ! [[ "$base" =~ ^[0-9]{8}-[0-9]{6}$ ]]; then
    echo "Se omite ruta no válida: $path" >&2
    continue
  fi
  if [[ ! -d "$path" || -L "$path" ]]; then
    echo "Se omite ruta no válida: $path" >&2
    continue
  fi
  real="$(readlink -f "$path" 2>/dev/null || true)"
  case "$real" in
    "$BACKUP_REAL"/*) ;;
    *) echo "Se omite ruta fuera del backup: $path" >&2; continue ;;
  esac
  rm -rf -- "$path"
  echo "  ELIMINADO $(basename "$path")"
done

log_line "valid=${#valid[@]} kept=${#kept[@]} removed=${#removed[@]} recoverable_kb=${recoverable_kb} applied=1"
echo "Limpieza completada. Web activa intacta."

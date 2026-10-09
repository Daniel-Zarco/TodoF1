#!/usr/bin/env bash
#
# TodoF1 — Despliegue seguro en alwaysdata (FASE 1)
# ------------------------------------------------------------
# Actualiza el repositorio del servidor y sincroniza únicamente los
# archivos públicos necesarios hacia el directorio web.
#
# Uso:
#   bash deploy/deploy.sh --dry-run   # simulación (no modifica el servidor)
#   bash deploy/deploy.sh             # despliegue real
#   bash deploy/deploy.sh --help
#
# Este script NO ejecuta migraciones, NO toca MariaDB y NO sobrescribe
# private/db.php ni .env. Está pensado para ser invocado por GitHub Actions
# vía SSH (por ejemplo: bash /home/todof1/todof1-app/deploy/deploy.sh).
#
set -euo pipefail

# ------------------------------- Configuración ------------------------------
REPO_DIR="${TODOF1_REPO_DIR:-/home/todof1/todof1-app}"
WWW_DIR="${TODOF1_WWW_DIR:-/home/todof1/www}"
BACKUP_DIR="${TODOF1_BACKUP_DIR:-/home/todof1/deploy-backups}"
BRANCH="${TODOF1_BRANCH:-main}"
REMOTE="${TODOF1_REMOTE:-origin}"
LOCK_FILE="${TODOF1_LOCK_FILE:-${TMPDIR:-/tmp}/todof1-deploy.lock}"

# Archivos de api/ que SÍ se publican. Lista explícita y cerrada:
# los endpoints nuevos NO se publican hasta añadirlos aquí conscientemente.
API_FILES=(
  db.php
  navbar.php
  logout.php
  guardar_usuario.php
  PageInfo.php
)

DRY_RUN=0

# ------------------------------- Utilidades ---------------------------------
log()  { printf '[%s] %s\n'  "$(date '+%Y-%m-%d %H:%M:%S')" "$*"; }
warn() { printf '[%s] AVISO: %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" >&2; }
die()  { printf '[%s] ERROR: %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" >&2; exit 1; }

usage() {
  cat <<'EOF'
Uso: deploy.sh [--dry-run] [--help]

  --dry-run   Muestra qué archivos se copiarían o eliminarían, sin modificar el servidor.
  --help      Muestra esta ayuda.

Variables de entorno opcionales:
  TODOF1_REPO_DIR    Directorio del repositorio (def: /home/todof1/todof1-app)
  TODOF1_WWW_DIR     Directorio público        (def: /home/todof1/www)
  TODOF1_BACKUP_DIR  Directorio de copias      (def: /home/todof1/deploy-backups)
  TODOF1_BRANCH      Rama a desplegar          (def: main)
  TODOF1_REMOTE      Remoto git                (def: origin)
  TODOF1_LOCK_FILE   Fichero de bloqueo        (def: ${TMPDIR:-/tmp}/todof1-deploy.lock)
EOF
}

# ------------------------------- Argumentos ---------------------------------
for arg in "$@"; do
  case "$arg" in
    --dry-run) DRY_RUN=1 ;;
    --help|-h) usage; exit 0 ;;
    *) die "Opción no reconocida: '$arg' (usa --help)" ;;
  esac
done

# -------------------------- Comprobaciones previas --------------------------
[[ -d "$REPO_DIR" ]]     || die "No existe el directorio del repositorio: $REPO_DIR"
[[ -d "$WWW_DIR" ]]      || die "No existe el directorio público: $WWW_DIR"
[[ -d "$REPO_DIR/.git" ]] || die "No es un repositorio git: $REPO_DIR"

command -v git   >/dev/null 2>&1 || die "No se encontró 'git'."
command -v rsync >/dev/null 2>&1 || die "No se encontró 'rsync'. Se detiene el proceso: no se improvisa una sincronización destructiva."

for d in index.html Images api; do
  [[ -d "$REPO_DIR/$d" ]] || die "Falta el directorio de origen: $REPO_DIR/$d"
done

# El archivo privado de producción debe existir: la nueva www/api/db.php lo
# localiza mediante la ruta relativa ../../todof1-app/private/db.php.
PRIVATE_DB="$(dirname "$WWW_DIR")/todof1-app/private/db.php"
[[ -f "$PRIVATE_DB" ]] || die "No se encuentra la configuración privada esperada por api/db.php: $PRIVATE_DB"

# ------------------------------- Bloqueo ------------------------------------
exec 9>"$LOCK_FILE" || die "No se pudo abrir el fichero de bloqueo: $LOCK_FILE"
if command -v flock >/dev/null 2>&1; then
  flock -n 9 || die "Ya hay un despliegue en curso (bloqueo: $LOCK_FILE)."
else
  warn "flock no está disponible: el bloqueo es best-effort."
fi

# --------------------------- Actualización de git ---------------------------
current_branch="$(git -C "$REPO_DIR" rev-parse --abbrev-ref HEAD)"
[[ "$current_branch" == "$BRANCH" ]] || die "La rama actual es '$current_branch' y se esperaba '$BRANCH'."

dirty="$(git -C "$REPO_DIR" status --porcelain --untracked-files=no)"
if [[ -n "$dirty" ]]; then
  warn "Hay cambios locales en archivos versionados del repositorio:"
  printf '%s\n' "$dirty" >&2
  if [[ "$DRY_RUN" -eq 0 ]]; then
    die "Abortado para no descartar cambios inesperados. Revísalos manualmente."
  fi
fi

if [[ "$DRY_RUN" -eq 1 ]]; then
  log "MODO SIMULACIÓN: se omite la actualización de git (no se modifica el repositorio)."
else
  log "Actualizando el repositorio $REPO_DIR (rama $BRANCH)…"
  git -C "$REPO_DIR" fetch --prune "$REMOTE" "$BRANCH"
  # Actualización segura: solo avance rápido. Nunca 'git reset --hard' ni 'git clean'.
  git -C "$REPO_DIR" merge --ff-only "$REMOTE/$BRANCH" \
    || die "No se pudo hacer avance rápido; la rama local ha divergido. Revísala manualmente."
  log "Repositorio actualizado a $(git -C "$REPO_DIR" rev-parse --short HEAD)."
fi

# --------------------------- Copia de seguridad -----------------------------
TS="$(date '+%Y%m%d-%H%M%S')"
BK="$BACKUP_DIR/$TS"

if [[ "$DRY_RUN" -eq 1 ]]; then
  log "MODO SIMULACIÓN: se crearía copia de seguridad de www/{index.html,Images,api} en $BK."
else
  log "Creando copia de seguridad en $BK…"
  mkdir -p "$BK"
  for d in index.html Images api; do
    if [[ -d "$WWW_DIR/$d" ]]; then
      cp -a "$WWW_DIR/$d" "$BK/$d"
    fi
  done
  mkdir -p "$WWW_DIR/index.html" "$WWW_DIR/Images" "$WWW_DIR/api"
fi

# ---------------------------------- rsync -----------------------------------
RSYNC_BASE=(-a -i --delete)
if [[ "$DRY_RUN" -eq 1 ]]; then
  RSYNC_BASE+=(--dry-run)
fi

log "Sincronizando index.html/ → www/index.html/…"
rsync "${RSYNC_BASE[@]}" "$REPO_DIR/index.html/" "$WWW_DIR/index.html/"

log "Sincronizando Images/ → www/Images/…"
rsync "${RSYNC_BASE[@]}" "$REPO_DIR/Images/" "$WWW_DIR/Images/"

# api/: solo la lista explícita. --delete-excluded elimina cualquier otro
# archivo que hubiera en www/api/ (p. ej. endpoints peligrosos ya publicados).
API_OPTS=(-a -i --delete-excluded)
if [[ "$DRY_RUN" -eq 1 ]]; then
  API_OPTS+=(--dry-run)
fi
for f in "${API_FILES[@]}"; do
  API_OPTS+=(--include="$f")
done
API_OPTS+=(--exclude='*')

log "Sincronizando api/ (solo: ${API_FILES[*]}) → www/api/…"
rsync "${API_OPTS[@]}" "$REPO_DIR/api/" "$WWW_DIR/api/"

# ------------------------------- Comprobación -------------------------------
if [[ -f "$WWW_DIR/index.php" ]]; then
  log "Conservado $WWW_DIR/index.php (redirección a /index.html/index.html)."
else
  warn "No existe $WWW_DIR/index.php; el script no lo crea ni lo modifica. Revísalo manualmente."
fi

log "No se han publicado: crear_admin.php, generarUsuarios.php, incrementar_contador.php, .env, private/, vendor/, .git/ ni archivos internos de Laravel."
log "No se ejecutan migraciones ni se modifica MariaDB."

if [[ "$DRY_RUN" -eq 1 ]]; then
  log "Simulación completada. No se ha modificado el servidor."
else
  log "Despliegue finalizado correctamente."
fi

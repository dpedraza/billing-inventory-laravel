#!/bin/bash
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

log()    { echo -e "${BLUE}[app]${NC} $1"; }
success(){ echo -e "${GREEN}[app]${NC} ✅ $1"; }
error()  { echo -e "${RED}[app]${NC} ❌ $1"; }

# ─── 1. Archivo .env ─────────────────────────────────────────────────────────
if [ ! -f ".env" ]; then
    log ".env no encontrado — copiando desde .env.example..."
    cp .env.example .env
fi

# ─── 2. Dependencias PHP ─────────────────────────────────────────────────────
if [ ! -f "vendor/autoload.php" ]; then
    log "Instalando dependencias PHP (composer install)..."
    composer install --no-interaction --no-progress --prefer-dist
    success "Dependencias PHP instaladas."
fi

# ─── 3. Assets (Vite + Tailwind) ─────────────────────────────────────────────
if [ ! -f "public/build/manifest.json" ]; then
    log "Compilando assets (pnpm install && pnpm run build)..."
    pnpm install --frozen-lockfile
    pnpm run build
    success "Assets compilados."
fi

# ─── 4. APP_KEY ──────────────────────────────────────────────────────────────
if ! grep -qE '^APP_KEY=.+' .env; then
    log "APP_KEY vacía, generando..."
    php artisan key:generate --force
    success "APP_KEY generada."
fi

# ─── 5. Esperar a la base de datos ───────────────────────────────────────────
log "Esperando a PostgreSQL (${DB_HOST}:${DB_PORT})..."
RETRIES=30
# -U explícito: el UID del host no existe en /etc/passwd del contenedor.
until pg_isready -q -h "${DB_HOST}" -p "${DB_PORT}" -U postgres; do
    RETRIES=$((RETRIES - 1))
    if [ "$RETRIES" -le 0 ]; then
        error "PostgreSQL no respondió a tiempo."
        exit 1
    fi
    sleep 2
done
success "Base de datos disponible."

# ─── 6. Migraciones y datos demo ─────────────────────────────────────────────
# Los seeders son idempotentes: en reinicios no duplican datos.
log "Ejecutando migraciones y seeders..."
php artisan migrate --force --seed
success "Migraciones y seeders aplicados."

# ─── 7. Storage link ─────────────────────────────────────────────────────────
if [ ! -L "public/storage" ]; then
    php artisan storage:link
fi

success "Sistema listo → http://localhost:${APP_PORT:-8080}"
echo ""

exec "$@"

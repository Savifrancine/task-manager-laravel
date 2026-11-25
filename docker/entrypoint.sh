#!/bin/sh
# ─────────────────────────────────────────────────────────────────
# Script exécuté à chaque démarrage du conteneur "app".
# ─────────────────────────────────────────────────────────────────

# Arrête le script à la première erreur
set -e

# 1. Le .env de Windows n'est pas copié dans l'image (voir .dockerignore) :
#    on utilise la configuration prévue pour Docker (MySQL du conteneur "db").
if [ ! -f .env ]; then
    cp docker/.env.docker .env
fi

# 2. Clé de chiffrement de Laravel : générée une seule fois
if ! grep -q '^APP_KEY=base64' .env; then
    php artisan key:generate --force
fi

# 3. Crée ou met à jour les tables dans le MySQL du conteneur "db"
php artisan migrate --force

# 4. Lance l'API. --host=0.0.0.0 est indispensable : sans lui, l'API
#    n'écoute qu'à l'intérieur du conteneur et reste injoignable depuis Windows.
exec php artisan serve --host=0.0.0.0 --port=8000

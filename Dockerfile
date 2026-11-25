# ─────────────────────────────────────────────────────────────────
# Dockerfile — la "recette" de l'image de l'API Task Manager.
#
# Une IMAGE, c'est un moule : elle contient le système, PHP,
# les dépendances et le code. Un CONTENEUR, c'est ce moule
# en train de tourner.
#
# Chaque instruction ci-dessous ajoute une "couche" à l'image.
# Docker garde ces couches en cache : si une étape n'a pas changé,
# elle n'est pas refaite au prochain build.
# ─────────────────────────────────────────────────────────────────

# 1. On part d'une image officielle qui contient déjà PHP 8.4
FROM php:8.4-cli

# 2. On ajoute ce qui manque :
#    - unzip     : Composer en a besoin pour décompresser les paquets
#    - pdo_mysql : l'extension PHP pour parler à MySQL
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# 3. On récupère Composer depuis son image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Dossier de travail dans le conteneur (comme un "cd /app")
WORKDIR /app

# 5. On installe les dépendances AVANT de copier le code.
#    Astuce de cache : tant que composer.json et composer.lock
#    ne changent pas, Docker réutilise cette étape (gain de temps).
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader

# 6. On copie le reste du code (sauf ce qui est listé dans .dockerignore)
COPY . .
RUN composer dump-autoload --optimize

# 7. Le port sur lequel l'API écoute À L'INTÉRIEUR du conteneur
EXPOSE 8000

# 8. Le script lancé au démarrage du conteneur
ENTRYPOINT ["sh", "docker/entrypoint.sh"]

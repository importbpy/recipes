# Recipes

Self-hosted recipe website to help you organize your recipes.

## Local development in Docker

Inside the `docker` directory run the Docker containers:

`docker compose up -d`

Optionally execute migrations to initialize the database:

`docker exec -it recipes_php php /var/www/recipes/bin/console doctrine:migrations:migrate --no-interaction`


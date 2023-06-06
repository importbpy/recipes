# Recipes

Self-hosted recipe website to help you organize your recipes.

## Local development in Docker

It is necessary to install dependencies using Composer:

`composer install`

Inside the `docker` directory run the Docker containers:

`docker compose up -d`

Optionally execute migrations to initialize the database:

`docker exec -it recipes_php php /var/www/recipes/bin/console doctrine:migrations:migrate --no-interaction`

Initialize image directories and set permissions

```
docker exec -it recipes_php /bin/bash -c 'mkdir -p /var/www/recipes/public/images/original/ /var/www/recipes/public/images/small/ && chmod -R a+w /var/www/recipes/public/images/'
```


### Adminer

Adminer runs at http://localhost:666.

System: MySQL
Server: database:3306
Username: root
Password: password
Database: recipes

### Application

Recipes application runs at http://localhost:8080.

First created user is a super admin with all possible privileges.
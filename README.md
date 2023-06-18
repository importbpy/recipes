# Recipes

Self-hosted recipe website to help you organize your recipes.

## Local development in Docker

Inside the `docker` directory run the Docker containers:

```
docker compose up -d
```

Install dependencies using Composer:

```
docker exec -it recipes_php composer install
```

Optionally execute migrations to initialize the database:

```
docker exec -it recipes_php php /var/www/recipes/bin/console doctrine:migrations:migrate --no-interaction
```

Initialize image directories and set permissions

```
docker exec -it recipes_php /bin/bash -c 'mkdir -p /var/www/recipes/public/images/original/ /var/www/recipes/public/images/small/ && chmod -R a+w /var/www/recipes/public/images/'
```

### Application

Recipes application runs at http://localhost:8080.

First registered user is a super admin with maximum privileges. All other users are registered as standard users 
and only super admin can change their privileges.

### Adminer

Adminer runs at http://localhost:9090.

- System: MySQL
- Server: database:3306
- Username: root
- Password: password
- Database: recipes

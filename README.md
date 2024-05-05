# Price Alert

![made-with-symfony](https://img.shields.io/badge/Made_with-Symfony-orange?style=flat)

## Install Project dependencies :
```sh
composer install
yarn install
```

### Create database :
```sh
bin/console doctrine:database:create
# or
bin/console d:d:c
```

### Database migration :
```sh
bin/console doctrine:migrations:migrate
# or
bin/console d:m:m
```

## Docker webserver
If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)

### Development
2. Run `docker compose up --build` to build and launch images.
2. Run `docker compose rm -f` to remove all images.

### Production
2. Run `docker compose build --no-cache` to build fresh images.
3. Run `docker compose up -d` to launch the the Docker containers.
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## License
Price Alert is available under the MIT License.
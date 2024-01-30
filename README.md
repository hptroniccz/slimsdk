# SDK for SlimAPI

## Local Development & Testing - php8
```bash
$ docker run --rm --name sdk -v $PWD:/var/www slimapi/nginx-php:8.2.1-2 composer install
$ docker run --rm --name sdk -v $PWD:/var/www slimapi/nginx-php:8.2.1-2 composer tests
```
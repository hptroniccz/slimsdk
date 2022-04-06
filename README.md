# SDK for SlimAPI

## Local Development & Testing
```bash
$ docker run --rm --name sdk -v $PWD/slimsdk:/var/www slimapi/nginx-php:7.4.21-2 composer install
$ docker run --rm --name sdk -v $PWD/slimsdk:/var/www slimapi/nginx-php:7.4.21-2 composer tests
```

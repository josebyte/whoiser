# whoiser

Make a bulk whois of a given domain for alert if anyone register a similar domain.

###### System requirements:
- PHP >= 7.2 (old versions supports 5.4+)
- php-curl
- php-mbstring
- Open port 43 in firewall

###### Optional:
- php-intl
- php-memcached + Memcached server 

###### Project requirements:
- PSR-4 autoloader

## Install
```
composer require io-developer/php-whois
composer install
```

## Config
- Set the mail configuration and cron timing in `Dockerfile`.
- Config `script.php` variables:
```
$SENDEMAILALERTTO = the email account to send alerts
```
```
$DOMAINTOPOPULATE = your domain name without dot and TLD
```

## Run script once
```
php script.php
```

## Run everything together using docker:
```
docker-compose up --force-recreate
```

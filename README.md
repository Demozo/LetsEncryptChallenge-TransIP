## Automate http-01 and dns-01 challenges
Adds automation for http-01 (.well-known) and dns-01 (wildcard) challenges for TransIP.

## Requirements
- PHP 8.1
- Composer
- certbot
- No pre-existing `_acme-challenge` TXT record in your DNS

## How to use
1. Clone repo
2. `composer install`
2. `cp .env.example .env`
3. Fill in blank fields in .env file
4. Run following command:
```shell
sudo certbot certonly --manual -d yourdomain.com -d *.yourdomain.com \
    --manual-auth-hook "/usr/bin/php /path/to/project/index.php" \
    --manual-cleanup-hook "/usr/bin/php /path/to/project/index.php cleanup"
```
5. Certbot will setup a scheduled task to automatically renew your certificate

## Security concern
Setting `LOGGING_LEVEL` to `Debug` will print your `$_SERVER` global array to `letsencrypt.log`.
This means that it will also print your `TRANSIP_KEY`. By default, it is set to `Info`.

## Shortcomings
Currently the certificate gets updated/created after the cleanup step has run. Meaning that the `nginx -s reload` 
commands it runs don't actually update the certificate nginx uses. This still requires a manual `nginx -s reload`
to be run.

A solution to this problem is editing the `/etc/letsencrypt/cli.ini` file and adding `deploy-hook = nginx -s reload` to that file.

## Closing
The code for this is very simple, so please have a look through it and give me any feedback you might have.  
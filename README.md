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
3. `cp .env.example .env`
4. Fill in blank fields in .env file
5. Run following command:
```shell
sudo certbot certonly --manual -d yourdomain.com -d *.yourdomain.com \
    --manual-auth-hook "/usr/bin/php /path/to/project/index.php" \
    --manual-cleanup-hook "/usr/bin/php /path/to/project/index.php cleanup"
```
6. Wait ~2 minutes if you are doing a dns-01 challenge, otherwise ~10 seconds
7. Certbot will setup a scheduled task to automatically renew your certificate
8. (Recommended) Edit `/etc/letsencrypt/cli.ini` and add `deploy-hook = nginx -s reload`

## Security concern
Setting `LOGGING_LEVEL` to `Debug` will print your `$_SERVER` global array to `letsencrypt.log`.
This means that it will also print your `TRANSIP_KEY`. By default, the logging level is set to `Info` however.

To clarify, if this happens, **GENERATE A NEW KEY**.

## Closing
The code for this is very simple, so please have a look through it and give me any feedback you might have.  
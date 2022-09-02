<?php

namespace MozoDev\LetsEncrypt;

class WellKnownUpdater
{
    public function updateWellKnown(): bool {
        if(!is_dir('/var/www/html/.well-known/acme-challenge')) {
            mkdir('/var/www/html/.well-known/acme-challenge', 0775, true);
        }

        $file = fopen("/var/www/html/.well-known/acme-challenge/{$_SERVER['CERTBOT_TOKEN']}", 'w');
        fwrite($file, $_SERVER['CERTBOT_VALIDATION']);
        fclose($file);

        $siteEnabled = $this->enableNginxSite();

        return file_exists("/var/www/html/.well-known/acme-challenge/{$_SERVER['CERTBOT_TOKEN']}")
            && $siteEnabled;
    }

    public function cleanup(): bool
    {
        return $this->disableNginxSite();
    }

    private function enableNginxSite(): bool
    {
        shell_exec('ln -s /etc/nginx/sites-available/acme /etc/nginx/sites-enabled/');

        return file_exists('/etc/nginx/sites-enabled/acme');
    }

    private function disableNginxSite(): bool {
        shell_exec('rm /etc/nginx/sites-enabled/acme');

        return !file_exists('/etc/nginx/sites-enabled/acme');
    }
}
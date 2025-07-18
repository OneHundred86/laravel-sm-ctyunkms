<?php

namespace Oh86\CtyunKms;

use Illuminate\Support\ServiceProvider;
use Oh86\CtyunKms\Commands\UkeyImportCert;
use Oh86\SmCryptor\Facades\Cryptor;

class CtyunKmsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Cryptor::extend('ctyunKms', function ($app, $config) {
            return new CtyunKmsCryptor($config);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                UkeyImportCert::class,
            ]);
        }
    }
}

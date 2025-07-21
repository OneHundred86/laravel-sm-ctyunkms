<?php

namespace Oh86\CtyunKms\Commands;

use Illuminate\Console\Command;
use Oh86\SmCryptor\Facades\Cryptor;

class UkeyImportCert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ctyunkms:ukey-import-cert {certificate : 证书} {ukeyName : 证书名称，自定义}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入UKey证书到CtyunKMS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $certificate = $this->argument('certificate');
        $ukeyName = $this->argument('ukeyName');

        $certificateId = Cryptor::driver('ctyunKms')->ukeyImportCert($certificate, $ukeyName);
        $this->info('certificateId: ' . $certificateId);

        return 0;
    }
}

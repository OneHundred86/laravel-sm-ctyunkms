<?php

namespace Oh86\CtyunKms;

use Illuminate\Support\Facades\Log;
use Oh86\CtyunKms\CtyunKmsSDK\Exceptions\KMSRequestException;
use Oh86\CtyunKms\CtyunKmsSDK\KMSApi;
use Oh86\SmCryptor\AbstractCryptor;
use Oh86\SmCryptor\Exceptions\SmCryptorException;

class CtyunKmsCryptor extends AbstractCryptor
{
    /**
     * @var array{
     *     encryptCmkUuid: string,
     *     hmacCmkUuid: string,
     *     ukeyOptions: array{
     *         algorithm: string,
     *     },
     * }
     */
    protected $config;
    /**
     * 
     * @var KMSApi
     */
    private $api;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->api = new KMSApi($config);
    }

    public function sm3(string $text): string
    {
        try {
            return $this->api->sm3($text);
        } catch (KMSRequestException $e) {
            Log::error(__METHOD__, $e->toArray());
            throw new SmCryptorException($e->getMessage());
        }
    }

    public function hmacSm3(string $text): string
    {
        try {
            return $this->api->hmac($this->config['hmacCmkUuid'], $text);
        } catch (KMSRequestException $e) {
            Log::error(__METHOD__, $e->toArray());
            throw new SmCryptorException($e->getMessage());
        }
    }

    public function sm4Encrypt(string $text): string
    {
        try {
            return $this->api->encrypt($this->config['encryptCmkUuid'], base64_encode($text));
        } catch (KMSRequestException $e) {
            Log::error(__METHOD__, $e->toArray());
            throw new SmCryptorException($e->getMessage());
        }
    }

    public function sm4Decrypt(string $text): string
    {
        try {
            return base64_decode($this->api->decrypt($text));
        } catch (KMSRequestException $e) {
            Log::error(__METHOD__, $e->toArray());
            throw new SmCryptorException($e->getMessage());
        }
    }

    public function ukeyImportCert(string $certificate, string $ukeyName): string
    {
        try {
            return $this->api->importCertificateForUK($this->config['ukeyOptions']['algorithm'], $ukeyName, $certificate);
        } catch (KMSRequestException $e) {
            Log::error(__METHOD__, $e->toArray());
            throw new SmCryptorException($e->getMessage());
        }
    }

    public function ukeyVerifySign(string $certificateId, string $text, string $sign): bool
    {
        try {
            return $this->api->certificatePublicKeyVerifyForUsbKey($this->config['ukeyOptions']['algorithm'], $certificateId, base64_encode($text), $sign);
        } catch (KMSRequestException $e) {
            Log::error(__METHOD__, $e->toArray());
            throw new SmCryptorException($e->getMessage());
        }
    }
}

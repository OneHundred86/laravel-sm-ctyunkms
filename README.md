# 天翼云kms密码机，基于`oh86/sm_cryptor`的拓展

### 配置 `config/sm_cryptor.php`

```php
return [
    'driver' => env('SM_CRYPTOR_DRIVER', 'ctyunKms'),

    'ctyunKms' => [
        'endPoint' => env('CTYUN_KMS_ENDPOINT'),
        'accessKey' => env('CTYUN_KMS_ACCESS_KEY'),
        'secretKey' => env('CTYUN_KMS_SECRET_KEY'),

        // 加解密主密钥
        'encryptCmkUuid' => env('CTYUN_KMS_ENCRYPT_CMKUUID'),
        // 摘要计算主密钥
        'hmacCmkUuid' => env('CTYUN_KMS_HMAC_CMKUUID'),
        // ukey可选项配置
        'ukeyOptions' => [
            'algorithm' => 'SM2',
        ],

        // [guzzleOptions](https://docs.guzzlephp.org/en/stable/request-options.html)
        'clientOptions' => [
            'timeout' => 10,
            'verify' => false,
            # 'debug' => true,
        ],
    ],
];
```
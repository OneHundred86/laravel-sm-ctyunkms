<?php

namespace Oh86\CtyunKms\CtyunKmsSDK;

use Oh86\CtyunKms\CtyunKmsSDK\Exceptions\KMSRequestException;

class KMSApi
{
    protected Client $client;

    /**
     * @param array{
     *     endPoint: string,
     *     accessKey: string,
     *     secretKey: string,
     *     clientOptions: array,
     * } $config
     */
    public function __construct($config)
    {
        $this->client = new Client($config, $config['clientOptions'] ?? []);
    }

    /**
     * 创建主密钥
     * @param array $data
     * @return string
     * @throws KMSRequestException
     */
    public function createKey(array $data)
    {
        $response = $this->client->post('/cmkManage/createKey', $data);

        $this->assertResponseOk($response);

        return $response->json('data.cmkUuid');
    }

    /**
     * 在线加密
     * @param string $cmkUuid
     * @param string $plaintext  base64编码
     * @return string  base64编码
     * @throws KMSRequestException
     */
    public function encrypt(string $cmkUuid, string $plaintext)
    {
        $response = $this->client->post('/keyCompute/encrypt', [
            'cmkUuid' => $cmkUuid,
            'plaintext' => $plaintext,
        ]);

        $this->assertResponseOk($response);

        return $response->json('data.ciphertextBlob');
    }

    /**
     * 在线解密
     * @param string $ciphertextBlob  base64编码
     * @return string  base64编码
     * @throws KMSRequestException
     */
    public function decrypt(string $ciphertextBlob)
    {
        $response = $this->client->post('/keyCompute/decrypt', [
            'ciphertextBlob' => $ciphertextBlob,
        ]);

        $this->assertResponseOk($response);

        return $response->json('data.plaintext');
    }

    /**
     * 计算hmac值
     * @param string $cmkUuid
     * @param string $input  不用base64编码
     * @return string  hex
     * @throws KMSRequestException
     */
    public function hmac(string $cmkUuid, string $input)
    {
        $response = $this->client->post('/get/hmaccompute', [
            'cmkUuid' => $cmkUuid,
            'input' => $input,
        ]);

        $this->assertResponseOk($response);

        return $response->json('data.hmacoutput');
    }

    /**
     * 导入UKey证书，获取证书ID
     * @param string $algorithm  'SM2' | 'RSA 2048'
     * @param string $ukName  证书名称，自定义
     * @param string $certificate  pem格式证书
     * @return string  certificateId
     * @throws KMSRequestException
     */
    public function importCertificateForUK(string $algorithm, string $ukName, string $certificate)
    {
        $response = $this->client->post('/manageCertificate/importCertificateForUK', [
            'algorithm' => $algorithm,
            'ukName' => $ukName,
            'cert' => $certificate,
        ]);

        $this->assertResponseOk($response);

        return $response->json('data.ukId');
    }

    /**
     * Summary of certificatePublicKeyVerifyForUsbKey
     * @param string $algorithm  签名算法，与导入UK证书保持一致
     * @param string $certificateId  导入UK返回的证书ID
     * @param string $msg   消息
     * @param string $signature   需要验签的签名值
     * @return bool 
     * @throws KMSRequestException
     */
    public function certificatePublicKeyVerifyForUsbKey(string $algorithm, string $certificateId, string $msg, string $signature)
    {
        $response = $this->client->post('/certificateCompute/certificatePublicKeyVerifyForUsbKey', [
            'algorithm' => $algorithm,
            'certificateId' => $certificateId,
            'message' => $msg,
            'signatureValue' => $signature,
        ]);

        $this->assertResponseOk($response);

        return $response->json('data.signatureValid');
    }

    /**
     * 计算SM3摘要
     * @param string $input  不用base64编码
     * @return string  base64编码
     * @throws KMSRequestException
     */
    public function sm3(string $input)
    {
        $response = $this->client->post('/get/messageDigest', [
            'input' => $input,
        ]);

        $this->assertResponseOk($response);

        return $response->json('data.output');
    }

    /**
     * @param \Illuminate\Http\Client\Response $response
     * @throws \Oh86\CtyunKms\CtyunKmsSDK\Exceptions\KMSRequestException
     */
    private function assertResponseOk($response)
    {
        if ($response->failed() || $response->json('code') != 200) {
            $lastReqeustInfo = $this->client->getLastRequestInfo();
            throw new KMSRequestException(
                $response->status(),
                $response->body(),
                $lastReqeustInfo['url'],
                $lastReqeustInfo['data'],
                $lastReqeustInfo['headers'],
            );
        }
    }
}

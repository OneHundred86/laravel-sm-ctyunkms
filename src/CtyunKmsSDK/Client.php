<?php

namespace Oh86\CtyunKms\CtyunKmsSDK;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class Client
{
    /** @var string */
    protected $endPoint;
    /** @var string */
    protected $accessKey;
    /** @var string */
    protected $secretKey;

    /** @var array */
    protected $options;
    /** 
     * @var array{
     *          url: string,
     *          headers: array,
     *          data: array,
     *      }
     */
    private $lastReqeustInfo;


    /**
     * @param array{
     *     endPoint: string,
     *     accessKey: string,
     *     secretKey: string,
     * } $config
     * @param array $options
     */
    public function __construct(array $config, $options = [])
    {
        $this->endPoint = $config['endPoint'];
        $this->accessKey = $config['accessKey'];
        $this->secretKey = $config['secretKey'];
        $this->options = $options;

        $this->lastReqeustInfo = null;
    }

    /**
     * @param array|null $data
     * @return string
     */
    public function genContentMd5($data)
    {
        $data = $data ?? [] + [
            'accessKey' => $this->accessKey,
        ];
        ksort($data);
        $this->debugLog('排序后结果', $data);

        $contentArr = [];
        foreach ($data as $key => $value) {
            if ($value === true) {
                $value = 'true';
            } elseif ($value === false) {
                $value = 'false';
            }

            $contentArr[] = $value;
        }
        $content = implode("\n", $contentArr);
        $this->debugLog("拼接字符串", $content);

        return base64_encode(md5($content, true));
    }

    /**
     * @param string $servicePath  eg: '/create/datakey'
     * @param string $requestDate  eg:'Wed, 16 Jul 2025 15:47:53 CST'
     * @param array|null $data
     * @return string
     */
    public function genHmac($servicePath, $requestDate, $data)
    {
        $contentMD5 = $this->genContentMd5($data + ['accessKey' => $this->accessKey]);
        $this->debugLog('contentMD5', $contentMD5);
        $content = sprintf("%s\n%s\n%s", $contentMD5, $requestDate, $servicePath);
        $this->debugLog('拼接contentMD5\nrequestDate\nservicePath', $content);

        return base64_encode(hash_hmac('sha256', $content, $this->secretKey, true));
    }

    public function getCurrentRequestDate()
    {
        return Carbon::now()->format('D, d M Y H:i:s T');
    }

    /**
     * @param string $servicePath
     * @param array|null $data
     * @return \Illuminate\Http\Client\Response
     */
    public function post($servicePath, $data = null)
    {
        $url = $this->fullUrl($servicePath);
        $requestDate = $this->getCurrentRequestDate();
        $headers = [
            'hmac' => $this->genHmac($servicePath, $requestDate, $data),
            'requestDate' => $requestDate,
            'accessKey' => $this->accessKey,
        ];
        $this->lastReqeustInfo = [
            'url' => $url,
            'headers' => $headers,
            'data' => $data,
        ];
        return Http::withOptions($this->options)
            ->withHeaders($headers)
            ->asJson()
            ->acceptJson()
            ->post($url, $data);
    }

    /**
     * @param string $servicePath  eg: '/create/datakey'
     * @return string
     */
    public function fullUrl($servicePath)
    {
        return $this->endPoint . $servicePath;
    }

    /**
     * @return array{data: array, headers: array, url: string}|null
     */
    public function getLastRequestInfo()
    {
        return $this->lastReqeustInfo;
    }

    private function debugLog(string $tag, $var)
    {
        if (!($this->options['debug'] ?? false)) {
            return;
        }

        echo $tag . ': ';
        \Symfony\Component\VarDumper\VarDumper::dump($var);
    }
}

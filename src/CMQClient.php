<?php

namespace yuncms\broadcast\qcloud;

use Exception;
use yuncms\broadcast\qcloud\exceptions\CMQClientParameterException;
use yuncms\broadcast\qcloud\exceptions\CMQServerException;
use yuncms\broadcast\qcloud\exceptions\CMQServerNetworkException;
use yuncms\broadcast\qcloud\http\HttpClient;
use yuncms\broadcast\qcloud\http\RequestInternal;

/**
 * Class CMQClient
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class CMQClient
{
    private $host;
    private $secretId;
    private $secretKey;
    private $version;
    private $http;
    private $method;
    private $signMethod;
    private $URISEC = '/v2/index.php';

    /**
     * CMQClient constructor.
     * @param $host
     * @param $secretId
     * @param $secretKey
     * @param string $version
     * @param string $method
     */
    public function __construct($host, $secretId, $secretKey, $version = "SDK_PHP_1.3", $method = "POST")
    {
        $this->processHost($host);
        $this->secretId = $secretId;
        $this->secretKey = $secretKey;
        $this->version = $version;
        $this->method = $method;
        $this->signMethod = 'HmacSHA1';
        $this->http = new HttpClient($this->host);
    }

    protected function processHost($host)
    {
        if (strpos($host, "http://") === 0) {
            $_host = substr($host, 7, strlen($host) - 7);
        } elseif (strpos($host, "https://") === 0) {
            $_host = substr($host, 8, strlen($host) - 8);
        } else {
            throw new CMQClientParameterException("Only support http(s) prototol. Invalid endpoint:" . $host);
        }
        if ($_host[strlen($_host) - 1] == "/") {
            $this->host = substr($_host, 0, strlen($_host) - 1);
        } else {
            $this->host = $_host;
        }
    }

    public function setSignMethod($signMethod = 'sha1')
    {
        if ($signMethod == 'sha1' || $signMethod == 'HmacSHA256')
            $this->signMethod = 'HmacSHA1';
        elseif ($signMethod == 'sha256')
            $this->signMethod = 'HmacSHA256';
        else
            throw new CMQClientParameterException('Only support sign method HmasSHA256 or HmacSHA1 . Invalid sign method:' . $signMethod);
    }

    public function setMethod($method = 'POST')
    {
        $this->method = $method;
    }

    public function setConnectionTimeout($connectionTimeout)
    {
        $this->http->setConnectionTimeout($connectionTimeout);
    }

    public function setKeepAlive($keepAlive)
    {
        $this->http->setKeepAlive($keepAlive);
    }

    /**
     * @param $action
     * @param $params
     * @param $reqInter
     * @throws \Exception
     */
    protected function buildReqInter($action, $params, &$reqInter)
    {
        $_params = $params;
        $_params['Action'] = ucfirst($action);
        $_params['RequestClient'] = $this->version;

        if (!isset($_params['SecretId']))
            $_params['SecretId'] = $this->secretId;

        if (!isset($_params['Nonce']))
            $_params['Nonce'] = rand(1, 65535);

        if (!isset($_params['Timestamp']))
            $_params['Timestamp'] = time();

        if (!isset($_params['SignatureMethod']))
            $_params['SignatureMethod'] = $this->signMethod;

        $plainText = $this->makeSignPlainText($_params, $this->method, $this->host, $reqInter->uri);
        $_params['Signature'] = $this->sign($plainText, $this->secretKey, $this->signMethod);

        $reqInter->data = http_build_query($_params);
        $this->buildHeader($reqInter);
    }

    protected function buildHeader(&$req_inter)
    {
        if ($this->http->isKeepAlive()) {
            $req_inter->header["Connection"] = "Keep-Alive";
        }
    }

    /**
     * @param $respInter
     */
    protected function checkStatus($respInter)
    {
        if ($respInter->status != 200) {
            throw new CMQServerNetworkException($respInter->status, $respInter->header, $respInter->data);
        }

        $resp = json_decode($respInter->data, TRUE);
        $code = $resp['code'];
        $message = $resp['message'];
        $requestId = $resp['requestId'];

        if ($code != 0) {
            throw new CMQServerException($message, $requestId, $code, $resp);
        }
    }

    /**
     * @param $action
     * @param $params
     * @return http\ResponseInternal
     * @throws \Exception
     */
    protected function request($action, $params)
    {
        // make request internal
        $req_inter = new RequestInternal($this->method, $this->URISEC);
        $this->buildReqInter($action, $params, $req_inter);
        $iTimeout = 0;
        if (array_key_exists("UserpollingWaitSeconds", $params)) {
            $iTimeout = (int)$params['UserpollingWaitSeconds'];
        }
        // send request
        $resp_inter = $this->http->sendRequest($req_inter, $iTimeout);
        return $resp_inter;
    }

    //=============================================topic operation================================================

    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function publishMessage($params)
    {
        $respInter = $this->request("PublishMessage", $params);
        $this->checkStatus($respInter);
        $ret = json_decode($respInter->data, TRUE);
        return $ret;
    }

    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function batchPublishMessage($params)
    {
        $respInter = $this->request("BatchPublishMessage", $params);
        $this->checkStatus($respInter);
        $ret = json_decode($respInter->data, TRUE);
        return $ret;
    }

    /**
     * sign
     * 生成签名
     * @param  string $srcStr 拼接签名源文字符串
     * @param  string $secretKey secretKey
     * @param  string $method 请求方法
     * @return bool|string
     * @throws Exception
     */
    public function sign($srcStr, $secretKey, $method = 'HmacSHA1')
    {
        switch ($method) {
            case 'HmacSHA1':
                $retStr = base64_encode(hash_hmac('sha1', $srcStr, $secretKey, true));
                break;
            case 'HmacSHA256':
                $retStr = base64_encode(hash_hmac('sha256', $srcStr, $secretKey, true));
                break;
            default:
                throw new Exception($method . ' is not a supported encrypt method');
                break;
        }

        return $retStr;
    }


    /**
     * makeSignPlainText
     * 生成拼接签名源文字符串
     * @param  array $requestParams 请求参数
     * @param  string $requestMethod 请求方法
     * @param  string $requestHost 接口域名
     * @param  string $requestPath url路径
     * @return string
     */
    public function makeSignPlainText($requestParams, $requestMethod = 'POST', $requestHost = '', $requestPath = '/v2/index.php')
    {
        $url = $requestHost . $requestPath;
        // 取出所有的参数
        $paramStr = self::_buildParamStr($requestParams, $requestMethod);
        $plainText = $requestMethod . $url . $paramStr;
        return $plainText;
    }

    /**
     * _buildParamStr
     * 拼接参数
     * @param  array $requestParams 请求参数
     * @param  string $requestMethod 请求方法
     * @return string
     */
    protected static function _buildParamStr($requestParams, $requestMethod = 'POST')
    {
        $paramStr = '';
        ksort($requestParams);
        $i = 0;
        foreach ($requestParams as $key => $value) {
            if ($key == 'Signature') {
                continue;
            }
            // 排除上传文件的参数
            if ($requestMethod == 'POST' && substr($value, 0, 1) == '@') {
                continue;
            }
            // 把 参数中的 _ 替换成 .
            if (strpos($key, '_')) {
                $key = str_replace('_', '.', $key);
            }

            if ($i == 0) {
                $paramStr .= '?';
            } else {
                $paramStr .= '&';
            }
            $paramStr .= $key . '=' . $value;
            ++$i;
        }

        return $paramStr;
    }
}

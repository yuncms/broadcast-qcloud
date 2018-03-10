<?php

namespace yuncms\broadcast\qcloud\http;

use yuncms\broadcast\qcloud\exceptions\CMQClientException;
use yuncms\broadcast\qcloud\exceptions\CMQClientNetworkException;

/**
 * Class HttpClient
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class HttpClient
{
    /**
     * @var int
     */
    private $connectionTimeout;

    /**
     * @var bool
     */
    private $keepAlive;

    /**
     * @var string
     */
    private $host;

    /**
     * @var resource
     */
    private $curl;

    /**
     * @var string
     */
    private $method;

    /**
     * CMQHttp constructor.
     * @param string $host
     * @param int $connectionTimeout
     * @param bool $keepAlive
     */
    public function __construct($host, $connectionTimeout = 10, $keepAlive = true)
    {
        $this->connectionTimeout = $connectionTimeout;
        $this->keepAlive = $keepAlive;
        $this->host = $host . "" . "/v2/index.php";
        $this->curl = NULL;
    }

    public function setMethod($method = 'POST')
    {
        $this->method = $method;
    }

    public function setConnectionTimeout($connection_timeout)
    {
        $this->connectionTimeout = $connection_timeout;
    }

    public function setKeepAlive($keep_alive)
    {
        $this->keepAlive = $keep_alive;
    }

    public function isKeepAlive()
    {
        return $this->keepAlive;
    }

    public function sendRequest($reqInter, $userTimeout)
    {
        if (!$this->keepAlive) {
            $this->curl = curl_init();
        } else {
            if ($this->curl == NULL)
                $this->curl = curl_init();
        }

        if ($this->curl == NULL) {
            throw new CMQClientException("Curl init failed");
        }

        $url = $this->host;
        if ($reqInter->method == 'POST') {
            curl_setopt($this->curl, CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $reqInter->data);
        } else {
            $url .= $reqInter->uri . '?' . $reqInter->data;
        }

        if (isset($reqInter->header)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $reqInter->header);
        }

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->connectionTimeout + $userTimeout);

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        if (false !== strpos($url, "https")) {
            // 证书
            // curl_setopt($ch,CURLOPT_CAINFO,"ca.crt");
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $resultStr = curl_exec($this->curl);
        if (curl_errno($this->curl)) {
            throw new CMQClientNetworkException(curl_error($this->curl));
        }
        $info = curl_getinfo($this->curl);
        $respInter = new ResponseInternal($info['http_code'], NULL, $resultStr);
        return $respInter;
    }
}






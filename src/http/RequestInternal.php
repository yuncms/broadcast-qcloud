<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\broadcast\qcloud\http;

/**
 * Class RequestInternal
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class RequestInternal
{
    public $header;
    public $method;
    public $uri;
    public $data;

    /**
     * RequestInternal constructor.
     * @param string $method
     * @param string $uri
     * @param null $header
     * @param string $data
     */
    public function __construct($method = "", $uri = "", $header = NULL, $data = "")
    {
        if ($header == NULL) {
            $header = [];
        }
        $this->method = $method;
        $this->uri = $uri;
        $this->header = $header;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $info = [
            "method" => $this->method,
            "uri" => $this->uri,
            "header" => json_encode($this->header),
            "data" => $this->data
        ];
        return json_encode($info);
    }
}
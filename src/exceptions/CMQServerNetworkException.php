<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\broadcast\qcloud\exceptions;


class CMQServerNetworkException extends CMQExceptionBase
{
    //服务器网络异常

    public $status;
    public $header;
    public $data;

    public function __construct($status = 200, $header = NULL, $data = "")
    {
        if ($header == NULL) {
            $header = array();
        }
        $this->status = $status;
        $this->header = $header;
        $this->data = $data;
    }

    public function __toString()
    {
        $info = array("status" => $this->status,
            "header" => json_encode($this->header),
            "data" => $this->data);

        return "CMQServerNetworkException  " . json_encode($info);
    }
}
<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\broadcast\qcloud\exceptions;


class CMQClientNetworkException extends CMQClientException
{
    /* 网络异常

        @note: 检查endpoint是否正确、本机网络是否正常等;
    */
    public function __construct($message, $code=-1, $data=array())
    {
        parent::__construct($message, $code, $data);
    }

    public function __toString()
    {
        return "CMQClientNetworkException  " .  $this->get_info();
    }
}
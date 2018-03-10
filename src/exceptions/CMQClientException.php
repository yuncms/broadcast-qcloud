<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\broadcast\qcloud\exceptions;


class CMQClientException extends CMQExceptionBase
{
    public function __construct($message, $code=-1, $data=array())
    {
        parent::__construct($message, $code, $data);
    }

    public function __toString()
    {
        return "CMQClientException  " .  $this->get_info();
    }
}
<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\broadcast\qcloud\exceptions;


class CMQClientParameterException extends CMQClientException
{
    /* 参数格式错误

        @note: 请根据提示修改对应参数;
    */
    public function __construct($message, $code=-1, $data=array())
    {
        parent::__construct($message, $code, $data);
    }

    public function __toString()
    {
        return "CMQClientParameterException  " .  $this->get_info();
    }
}
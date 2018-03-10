<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\broadcast\qcloud\http;

/**
 * Class ResponseInternal
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class ResponseInternal
{
    public $header;
    public $status;
    public $data;

    /**
     * ResponseInternal constructor.
     * @param int $status
     * @param array $header
     * @param string $data
     */
    public function __construct($status = 0, $header = NULL, $data = "")
    {
        if ($header == NULL) {
            $header = [];
        }
        $this->status = $status;
        $this->header = $header;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $info = [
            "status" => $this->status,
            "header" => json_encode($this->header),
            "data" => $this->data
        ];
        return json_encode($info);
    }
}
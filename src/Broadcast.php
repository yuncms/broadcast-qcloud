<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\broadcast\qcloud;

use Yii;
use yii\base\InvalidConfigException;
use yuncms\broadcast\BaseBroadcast;
use yuncms\broadcast\MessageInterface;
use yuncms\broadcast\qcloud\CMQClient;

/**
 * Class Broadcast
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 3.0
 */
class Broadcast extends BaseBroadcast
{

    /**
     * @var  string
     */
    public $endPoint;

    /**
     * @var string
     */
    public $accessId;

    /**
     * @var string
     */
    public $accessKey;

    /**
     * @var  string 主题名称
     */
    public $topicName;

    /**
     * @var CMQClient
     */
    protected $_client;

    /**
     * 初始化组件
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->endPoint)) {
            throw new InvalidConfigException ('The "endPoint" property must be set.');
        }
        if (empty ($this->accessId)) {
            throw new InvalidConfigException ('The "accessId" property must be set.');
        }
        if (empty ($this->accessKey)) {
            throw new InvalidConfigException ('The "accessKey" property must be set.');
        }
        if (empty ($this->topicName)) {
            throw new InvalidConfigException ('The "topicName" property must be set.');
        }
    }

    /**
     * @return array|HttpClient aliyun mns topic instance or array configuration.
     * @throws InvalidConfigException
     */
    public function getClient()
    {
        if (!is_object($this->_client)) {
            $this->_client = Yii::createObject(CMQClient::class, [
                $this->endPoint,
                $this->accessId,
                $this->accessKey,
            ]);
        }
        return $this->_client;
    }

    /**
     * Sends the specified message.
     * This method should be implemented by child classes with the actual broadcast sending logic.
     * @param MessageInterface $message the message to be sent
     * @return bool whether the message is sent successfully
     */
    protected function sendMessage($message)
    {
        $params = [
            'topicName' => $this->topicName,
            'msgBody' => $message->toString(),
        ];
        $tags = $message->getTag();
        if ($tags != null && is_array($tags) && !empty($tags)) {
            $n = 1;
            foreach ($tags as $tag) {
                $key = 'msgTag.' . $n;
                $params[$key] = $tag;
                $n += 1;
            }
        }
        $msgRes = $this->getClient()->publishMessage($params);
        if ($msgRes['code'] == 0) {
            return true;
        }
        return false;
    }
}
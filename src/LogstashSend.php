<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 25.08.2019
 * Time: 12:27
 */

namespace mitrm\logstash;

use mitrm\logstash\transport\TransportInterface;
use Yii;
use yii\base\Component;

/**
 * Class LogstashSend
 * @package mitrm\logstash
 */
class LogstashSend extends Component
{

    /** @var TransportInterface */
    private $sender;

    /**
     * @var array
     */
    public $config = [];


    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->sender = Yii::createObject($this->config);
        parent::init();
    }


    /**
     * @brief
     * @param array|string $data
     */
    public function sendLog($data)
    {
        $this->sender->send($data);
    }


}
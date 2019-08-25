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
use yii\helpers\ArrayHelper;

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
     * @var array
     */
    public $addParams = [];


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
        if (is_array($data) && !empty($this->addParams) && is_array($this->addParams)) {
            $data = ArrayHelper::merge($data, $this->addParams);
        }
        $this->sender->send($data);
    }


}
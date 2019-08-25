<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 25.08.2019
 * Time: 13:43
 */

namespace mitrm\logstash;

use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\log\Target;

/**
 * Class LogstashTarget
 * @package mitrm\logstash
 */
class LogstashTarget extends Target
{
    /**
     * @var array Extra options
     */
    public $clientOptions = [];

    /**
     * @var string Logstash type name.
     */
    public $type = 'log';
    /**
     * @var LogstashSend|array|string the logstash connection object or the application component ID
     * of the logstash connection.
     */
    public $logstash = 'logstash';

    /**
     * @var boolean If true, context will be logged as a separate message after all other messages.
     */
    public $isLogContext = true;

    /**
     * @var boolean If true, context will be logged as a separate message after all other messages.
     */
    public $isLogUser = true;
    /**
     * @var boolean If true, context will be included in every message.
     * This is convenient if you log application errors and analyze them with tools like Kibana.
     */
    public $includeContext = false;
    /**
     * @var boolean If true, context message will cached once it's been created. Makes sense to use with [[includeContext]].
     */
    public $cacheContext = false;
    /**
     * @var string Context message cache (can be used multiple times if context is appended to every message)
     */
    protected $_contextMessage = null;

    /**
     * @var callable Callback function that can modify extra's array
     */
    public $extraCallback;


    /**
     * This method will initialize the [[logstash]] property to make sure it refers to a valid Logstash connection.
     * @throws InvalidConfigException if [[logstash]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->logstash = Instance::ensure($this->logstash, LogstashSend::class);
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        foreach ($this->messages as $message) {
            $this->logstash->sendLog($this->prepareMessage($message));
        }
    }

    /**
     * If [[includeContext]] property is false, returns context message normally.
     * If [[includeContext]] is true, returns an empty string (so that context message in [[collect]] is not generated),
     * expecting that context will be appended to every message in [[prepareMessage]].
     * @return array the context information
     * @throws InvalidConfigException
     */
    protected function getContextMessage()
    {
        if (null === $this->_contextMessage || !$this->cacheContext) {
            $context = ArrayHelper::filter($GLOBALS, $this->logVars);
        }
        if (($this->isLogUser === true) && ($user = Yii::$app->get('user', false)) !== null) {
            /** @var \yii\web\User $user */
            $context['userId'] = $user->getId();
        }
        return $context;
    }

    /**
     * Processes the given log messages.
     * This method will filter the given messages with [[levels]] and [[categories]].
     * And if requested, it will also export the filtering result to specific medium (e.g. email).
     * Depending on the [[includeContext]] attribute, a context message will be either created or ignored.
     * @param array $messages log messages to be processed. See [[Logger::messages]] for the structure
     * of each message.
     * @param boolean $final whether this method is called at the end of the current application
     */
    public function collect($messages, $final)
    {
        $this->messages = array_merge(
            $this->messages,
            $this->filterMessages($messages, $this->getLevels(), $this->categories, $this->except)
        );

        $count = count($this->messages);
        if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {


            // set exportInterval to 0 to avoid triggering export again while exporting
            $oldExportInterval = $this->exportInterval;
            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;

            $this->messages = [];
        }
    }



    /**
     * @param $text
     * @return array
     */
    protected function formatText($text)
    {
        $type = gettype($text);
        switch ($type) {
            case 'string':
                return ['@message' => $text];
            case 'array':
                return $text;
            case 'object':
                return get_object_vars($text);
            default:
                return ['@message' => Yii::t('app', "Warning! Invalid log message type: " .$type)];
        }
    }


    /**
     * Prepares a log message.
     * @param array $message The log message to be formatted.
     * @return array
     * @throws InvalidConfigException
     */
    public function prepareMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $result = [
            'level' => Logger::getLevelName($level),
            'category' => $category,
            '@timestamp' => date('c', $timestamp)
        ];
        $result = ArrayHelper::merge($result,
            $this->formatText($text),
            $this->clientOptions
        );
        if (isset($message[4])) {
            $result['trace'] = $message[4];
        }
        if (is_callable($this->extraCallback) && isset($text['data'])) {
            $result['data'] = call_user_func($this->extraCallback, $text, $result['data']);
        }
        if (!$this->includeContext && $this->isLogContext) {
            $result['context'] = $this->getContextMessage();
        }
        return $result;
    }

}

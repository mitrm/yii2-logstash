<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 25.08.2019
 * Time: 12:29
 */

namespace mitrm\logstash\transport;


use mitrm\logstash\exception\ServerException;

/**
 * Class Http
 * @package mitrm\logstash\adapter
 */
class HttpTransport implements TransportInterface
{
    /**
     * @var string
     */
    public $timeout = 10;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port = 80;

    /**
     * @inheritdoc
     */
    public function send($data)
    {
        $curl = curl_init();
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        curl_setopt_array($curl, [
            CURLOPT_PORT => $this->port,
            CURLOPT_URL => $this->host,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
        ]);
        curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            throw new ServerException('Ошибка отправки данных в logstash: ' . $err);
        } else {
            return true;
        }
    }

}
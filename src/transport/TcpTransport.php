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
 * Class UcpAdapter
 * @package mitrm\logstash\adapter
 */
class TcpTransport implements TransportInterface
{

    /**
     * @var string
     */
    public $socket;
    /**
     * @var string
     */
    public $timeout = 5;

    /** A primary socket */
    private $client;


    /**
     * @inheritdoc
     */
    public function send($data)
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $socket = $this->socket;
        if (!$this->client) {
            $this->client = @stream_socket_client($socket, $errno, $errorMessage, $this->timeout);
        }
        if ($this->client === false) {
            throw new ServerException(sprintf('Failed to connect to Logstash [%d]: %s', $errno, $errorMessage));
        }
        $result = stream_socket_sendto($this->client, $data);
        if ($result == -1) {
            throw new ServerException('Error while writing to logstash');
        }
        $result = stream_socket_sendto($this->client, "\n");
        if ($result == -1) {
            throw new ServerException('Error while writing to logstash');
        }
        return true;
    }

}
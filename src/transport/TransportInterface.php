<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 25.08.2019
 * Time: 12:30
 */

namespace mitrm\logstash\transport;

/**
 * Interface TransportInterface
 * @package mitrm\logstash\adapter
 */
interface TransportInterface
{

    /**
     * @param array|string $data
     * @return boolean
     */
    public function send($data);

}
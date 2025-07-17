<?php

namespace app\api\controller;

use app\BaseController;
use GatewayWorker\Lib\Gateway;
use think\worker\Server;

class Worker extends Server
{
    protected $socket = 'tcp://0.0.0.0:25';
    protected $uidConnections = array();

    public function __construct()
    {
        parent::__construct();
    }


    public function onConnect($connection)
    {
        $message = [
            'app' => 'test',
            'yourId' => $connection->id,
        ];
        $connection->send(json_encode($message, JSON_UNESCAPED_UNICODE));
    }


    /**
     * @param $connection
     * @param $data 142842567084ds
     */
    public function onMessage($connection, $data)
    {
        $message = [
            'data' => $data,
            'yourId' => $connection->id,
        ];
        $connection->send(json_encode($message, JSON_UNESCAPED_UNICODE));
    }


    public function sendMsg($content)
    {
        if (isset($content['type'])) {
            $type = $content['type'];
            $sendId = $content['sendId'];

            if ($type == "pushSingle") {
                Gateway::sendToUid($sendId, $content['content']);
            }
        }
    }
}

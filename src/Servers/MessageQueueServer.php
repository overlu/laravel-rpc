<?php


namespace Overlu\Rpc\Servers;


use Overlu\Rpc\Drivers\MessageQueueModule;
use Overlu\Rpc\Server;
use Overlu\Rpc\Util\Command;

class MessageQueueServer extends Server
{
    public $server;

    public function __construct()
    {
        $this->server = new MessageQueueModule();
        parent::__construct();
    }

    public function job()
    {
        Command::line('server [mq] start...');
        $this->server->watch();
    }


    /**
     * @return array
     */
    public function status()
    {
        $status = $this->server->status();
        $header = [
            '频道', '总任务数', '当前连接数', '等待响应连接数', '监控连接数'
        ];
        return [
            'header' => $header,
            'body' => $status
        ];
    }

}

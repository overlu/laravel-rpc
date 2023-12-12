<?php

namespace Overlu\Rpc\Console;

use Exception;
use Illuminate\Console\Command;
use Overlu\Rpc\Servers\HproseServer;
use Overlu\Rpc\Servers\MessageQueueServer;

class Status extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rpc:status {server}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'show the rpc server status (mq / rpc)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server = $this->argument('server');
        try {
            switch ($server) {
                case 'mq':
                    $status = (new MessageQueueServer())->status();
                    $this->table($status['header'], $status['body']);
                    break;
                case 'rpc':
                    (new HproseServer())->status();
                    break;
                default:
                    $this->error("server [{$server}] not exist.");
                    return;
            }
        } catch (Exception $exception) {
            $this->error("something error. message: " . $exception->getMessage() . ', on file: ' . $exception->getFile() . ', at line: ' . $exception->getLine());
        }

    }
}

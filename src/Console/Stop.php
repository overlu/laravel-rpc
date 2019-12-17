<?php

namespace Overlu\Rpc\Console;

use Illuminate\Console\Command;
use Overlu\Rpc\Servers\HproseServer;
use Overlu\Rpc\Servers\MessageQueueServer;

class Stop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rpc:stop {server}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'stop the rpc server (mq / rpc / all)';

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
                case 'all':
                    (new MessageQueueServer())->stop();
                    (new HproseServer())->stop();
                    break;
                case 'mq':
                    (new MessageQueueServer())->stop();
                    break;
                case 'rpc':
                    (new HproseServer())->stop();
                    break;
                default:
                    $this->error("server [{$server}] not exist.");
                    return;
            }
        } catch (\Exception $exception) {
            $this->error("stop server [{$server}] failed. error message: " . $exception->getMessage() . ', on file: ' . $exception->getFile() . ', at line: ' . $exception->getLine());
        }

    }
}

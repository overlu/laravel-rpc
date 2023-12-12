<?php

namespace Overlu\Rpc\Console;

use Illuminate\Console\Command;
use Overlu\Rpc\Servers\HproseServer;
use Overlu\Rpc\Servers\MessageQueueServer;

class Reload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rpc:reload {server}
                            {--d : Run the worker in daemon mode (Deprecated)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reload the rpc server (mq / rpc / all)';

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
        $demonize = $this->option('d');
        try {
            switch ($server) {
                case 'all':
                    (new MessageQueueServer())->reload($demonize);
                    (new HproseServer())->reload($demonize);
                    break;
                case 'mq':
                    (new MessageQueueServer())->reload($demonize);
                    break;
                case 'rpc':
                    (new HproseServer())->reload($demonize);
                    break;
                default:
                    $this->error("server [{$server}] not exist.");
                    return;
            }
        } catch (\Exception $exception) {
            $this->error("restart server [{$server}] failed. error message: " . $exception->getMessage() . ', on file: ' . $exception->getFile() . ', at line: ' . $exception->getLine());
            $this->handle();
        }
    }
}

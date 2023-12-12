<?php

namespace Overlu\Rpc\Servers;

use Overlu\Rpc\Drivers\HproseModule;
use Overlu\Rpc\Server;
use Overlu\Rpc\Util\Command;

class HproseServer extends Server
{
    public function job()
    {
        Command::line('server [rpc] start...');
        (new HproseModule())->watch();
    }

}

<?php


namespace Overlu\Rpc\Drivers;


use Illuminate\Http\Request;
use Overlu\Rpc\Base;
use Overlu\Rpc\Driver;
use Overlu\Rpc\Util\Encrypt;
use Overlu\Rpc\Util\Signature;

class Api extends Base implements Driver
{
    /*public function checkSignature(): bool
    {
        $request = request();
        if ($request->hasHeader('signature')) {
            return $request->hasHeader('nonce') ?
                Encrypt::verify($request->header('nonce'), $request->header('signature'), $request->header('timestamp')) :
                Signature::verify($request->header('signature'));
        }
        return false;
    }*/
}

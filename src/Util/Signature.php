<?php


namespace Overlu\Rpc\Util;


use Illuminate\Support\Facades\Cache;

class Signature
{
    /**
     * @param $signature
     * @param $nonce
     * @return bool
     */
    public static function make($signature, $nonce): bool
    {
        Cache::forget('rpc_signature:' . $signature);
        return Cache::add('rpc_signature:' . $signature, $nonce, config('rpc.signature_expiry'));
    }

    /**
     * @param $signature
     * @return bool
     */
    public static function verify($signature): bool
    {
        return Cache::has('rpc_signature:' . $signature);
    }
}

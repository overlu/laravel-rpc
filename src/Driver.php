<?php


namespace Overlu\Rpc;


interface Driver
{
    public function module(array $module);

    public function checkSignature(): bool;
}

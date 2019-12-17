<?php


namespace Overlu\Rpc\Util;


use Overlu\Rpc\Exceptions\RpcException;

class Request
{
    /**
     * @param $url
     * @param array $params
     * @param array $headers
     * @return bool|string
     * @throws RpcException
     */
    public static function call($url, $params = [], $headers = [])
    {
        /*try {
            $params = http_build_query($params);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } catch (\Exception $exception) {
            throw new RpcException([
                $exception->getCode() => $exception->getMessage()
            ]);
        }*/
        return \HttpRequest::postData($url, $params);
    }
}

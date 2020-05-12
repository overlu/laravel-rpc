<?php
/**
 * Rpc、MQ、Api、Local
 */

return [
    /**
     * dev(default), production
     * dev模式免签名验证
     */
    'environment' => 'dev',
    /**
     * 验证方式: white_ip_lists(default) / signature
     */
//    'verify_method' => 'white_ip_lists',
    'verify_method' => 'signature',

    'white_ip_lists' => [
        '127.0.0.1',
    ],
    /**
     * 秘钥，签名加密用，务必保证每个节点都一样
     */
    'key' => 'Q6Hc4pLmSMeYHVnqnRu68UcbC36RvIW2P7v3eQyAyxQ',

    /**
     * 签名有效时间，单位秒
     */
    'signature_expiry' => '600',

    /**
     * 异常处理驱动, rpc/default
     */
    'exception_driver' => 'rpc',

    /**
     * 是否打开调用日志
     */
    'log_info' => true,
    'rpc_log_info_channel' => [
        'driver' => 'daily',
        'path' => storage_path('logs/rpc/info.log'),
        'level' => 'info',
//        'days' => 14,
    ],
    /**
     * 是否打开错误日志
     */
    'log_error' => true,
    'rpc_log_error_channel' => [
        'driver' => 'daily',
        'path' => storage_path('logs/rpc/error.log'),
        'level' => 'error',
//        'days' => 14,
    ],

    /**
     * 配置beanstalkd
     */
    'beanstalkd' => [
        'host' => '127.0.0.1',
        'port' => '11300',
        'channel' => 'Lrpc_uDi7q',
        'username' => '',
        'password' => '',
    ],

    /**
     * rpc服务端口
     */
    'port' => 1314,

    /**
     * 是否使用nacos服务
     */
    'use_nacos' => false,
];

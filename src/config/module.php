<?php
/**
 * 模块信息配置
 */
return [
    /**
     * Module Mapping
     * Module Name => NameSpace\\Module Name
     */
    'mapping' => [
        'Log' => '\\App\\RpcTest\\Log',
        'Member' => '\\App\\RpcTest\\Member',
        'News' => '\\App\\RpcTest\\News',
        'Live' => '\\App\\RpcTest\\Live',
        'Auth' => '',
    ],

    /**
     * module registration center
     */
    'registration' => [
        'local' => ['Log', 'Auth'],  // message queue监听使用，判断是否是自身服务
        'mq' => ['Member'],
        'rpc' => ['Live'],
        'api' => ['News'],
    ],

    /**
     * rpc服务器模块映射
     */
    'rpc' => [
        'rpc.test' => [
            'Live'
        ]
    ],

    /**
     * api服务器模块映射
     */
    'api' => [
        'rpc.test' => [
            'News',
        ],
        'rpc2.test' => [
            'News2'
        ]
    ],
];

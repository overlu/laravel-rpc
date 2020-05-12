<?php
/**
 * 模块信息配置
 */
return [
    /**
     * Module Mapping
     * ModuleName => Namespace\\ModuleName
     */
    'mapping' => [
//        'ModuleName' => 'Namespace\\ModuleName',
    ],

    /**
     * 默认local驱动
     */
    'default_driver' => 'local',

    /**
     * module registration center
     *
     */
    'registration' => [
        'local' => [  // message queue监听使用，判断是否是自身服务
//            'ModuleName'
        ],
        'mq' => [
//            'ModuleName'
        ],
        'rpc' => [
//            'ModuleName'
        ],
        'api' => [
//            'ModuleName'
        ],
    ],

    /**
     * rpc服务地址模块映射
     * 一个模块对应多个服务地址，使用','隔开
     */
    'hosts' => [
//        'ModuleName' => '127.0.0.1',
//        'ModuleName2' => '127.0.0.1,127.0.0.2',
    ]
];

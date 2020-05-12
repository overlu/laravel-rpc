### 前言
#### 项目介绍
overlu/laravel-rpc是一款基于laravel的分布式开发部署扩展，`运行日志记录放在storage/logs/rpc`，包含4种方案：
* 本地调用
* MessageQueue调用 `异步`
* RPC `同步`、`异步`
* API `同步`

#### 安装
##### 1. 安装beanstalkd
```shell script
# 访问 http://kr.github.io/beanstalkd/download.html 下载beanstalkd-1.11.tar.gz
> tar -xf beanstalkd-1.11.tar.gz
> cd beanstalkd-1.11
> make
# 查看beanstalkd参数信息
> ./beanstalkd -h
# 启动beanstalkd，-b表示开启binlog，断电后重启自动恢复任务
> ./beanstalkd -l 127.0.0.1 -p 11300 -b /data/beanstalkd/binlog &
```

##### 2. 安装扩展
```shell script
php composer.phar require overlu/laravel-rpc
# publish config
php artisan vendor:publish --provider="Overlu\Rpc\RpcServiceProvider"
```
```php
# 安装好Rpc扩展后，打开 config/app.php，注册如下服务提供者到 $providers 数组：
Overlu\Rpc\RpcServiceProvider::class
# 然后添加如下门面到 $aliaes 数组：
'Rpc' => \Overlu\Rpc\Facades\Rpc::class
```
使用Nacos服务发现：
```shell script
php composer.phar require overlu/laravel-reget
```
> 具体使用查看：  
[https://github.com/overlu/laravel-reget](https://github.com/overlu/laravel-reget)

#### 配置
```php
/**
 * rpc.php
 * 基本信息配置
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
        'host' => env('BEANSTALKD_HOST', '127.0.0.1'),
        'port' => env('BEANSTALKD_POST', '11300'),
        'channel' => env('BEANSTALKD_CHANNEL', 'Lrpc_uDi7q'),
    ],

    /**
     * rpc服务端口
     */
    'port' => env('RPC_SERVER_PORT', 1314),

    /**
     * 是否使用nacos服务
     */
    'use_nacos' => false,
];

```
```php
/**
 * module.php
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

```
> 注意：如果同一个模块注册在多个API/RPC驱动服务器上，则系统会自动随机获取一个服务器连接

#### 流程图  
![QwSCM8.png](https://s2.ax1x.com/2019/12/09/QwSCM8.png)  

#### Usage
##### 修改配置文件module.php `本地配置文件模式`
```php
// 配置模块映射(demo)
'mapping' => [
    'Log' => '\\App\\RpcTest\\Log',
    'SMS' => '\\App\\RpcTest\\SMS',
    'News' => '\\App\\RpcTest\\News',
]
// 注册模块(demo)
'registration' => [
    'local' => ['Log'],
    'mq' => ['SMS'],
    'rpc' => [],
    'api' => ['News'],
]
// 配置服务地址(demo)
'hosts' => [
    'Log' => '127.0.0.1',
    'SMS' => '127.0.0.1,127.0.0.2',
    'News' => '127.0.0.3'
]
```

##### 修改配置 `nacos模式`
__注册服务(demo)__  
![YNPsGF.png](https://s1.ax1x.com/2020/05/12/YNPsGF.png) 
__配置模块参数(demo)__ 
![YN9Iw6.png](https://s1.ax1x.com/2020/05/12/YN9Iw6.png)  

##### 初始化配置 `nacos模式`
```shell script
# demo
php artisan reget:listen demo.module.config --handle="Namespace\ClassName"
```
```php
<?php

namespace Namespace;

Class ClassName
{
    /**
     * @param $key
     * @param $data
     * @param $originData
     */
    public static function handle($key, $data, $originData)
    {
        Cache::set('module.registration', $data['registration']);
        Cache::set('module.mapping', $data['mapping']);
    }

    /**
     * @param \Exception $exception
     */
    public static function error(\Exception $exception)
    {
        dd($exception->getCode());
    }
}
```
##### 调用模块   
___同步调用, 和原生new class一样的传参方式。___
```php
$result = Rpc::Module_Name('class_args1','class_args2')->method('method_arg',['method_arg2_1','method_arg2_2']);
var_dump($result);
```
```php
// 1. 获取远程实例化对象，注意：实例化模块需要事先在配置文件module.php中进行相关注册操作
$news = Rpc::News();
$log = Rpc::Log('debug', 'file');
$sms = Rpc::SMS('ali');
$sms = Rpc::SMS('huaxin');
...
```
```php
// 2. 调用远程对象方法，注意：和本地化调用以及传参一样，静态方法需要用::调用，普通方法用->调用
$news_list = $new->get();
$news_list_100 = $new->get(100);
dump($news_list, $news_list_100);
$log::debug('log debug info');
$sms->send(18888888888, '我是同步发送短信方式');
...
```
___异步调用，实例化和传参见同步调用___
```php
// 异步调用，需要在实例化模块前面加上"_"
$sms = Rpc::_SMS();
$sms->send(18888888888, '我是异步发送短信方式');

// 异步生成新闻
Rpc::_News()->create('news content');
// 新增回调函数，调用的函数名前面加上"_"，在该回调函数内处理回调数据
public function _create($result)
{
    // 在这里处理异步回调数据
    if($result) {
        echo 'create success';
    } else {
        echo 'create faild';
    }
}
```
> 注意：异步调用无返回值，需要新增一接收回调函数，普通函数名前面加上下划线`_`。异步调用目前只支持mq/rpc驱动

#### 服务
*  启动服务
```shell script
php artisan rpc:start mq 
php artisan rpc:start rpc 
php artisan rpc:start all 
# 常驻后台
php artisan rpc:start mq --d
php artisan rpc:start rpc --d
php artisan rpc:start all --d
```
*  关闭服务
```shell script
php artisan rpc:stop mq 
php artisan rpc:stop rpc 
php artisan rpc:stop all 
```
*  重启服务
```shell script
php artisan rpc:reload mq 
php artisan rpc:reload rpc 
php artisan rpc:reload all
# 常驻后台
php artisan rpc:reload mq --d
php artisan rpc:reload rpc --d
php artisan rpc:reload all --d
```
*  服务状态
```shell script
php artisan rpc:status mq 
php artisan rpc:status rpc 
```
> 建议配合supervisor管理进程

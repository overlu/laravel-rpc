### 前言
#### 项目介绍
overlu/laravel-rpc是一款基于laravel的分布式开发部署扩展，`运行日志记录放在storage/logs/rpc`，包含4种方案：
* 本地调用
* MessageQueue调用（<span style='color:red'>异步</span>）
* RPC（<span style='color:red'>同步、异步</span>）
* API (<span style='color:red'>同步</span>)

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
``` php
php composer.phar require overlu/laravel-rpc
# publish config
php artisan vendor:publish --provider="Overlu\Rpc\RpcServiceProvider"
# 安装好Rpc扩展后，打开 config/app.php，注册如下服务提供者到 $providers 数组：
Overlu\Rpc\RpcServiceProvider::class
# 然后添加如下门面到 $aliaes 数组：
'Rpc' => \Overlu\Rpc\Facades\Rpc::class
```

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
     * 驱动: rpc,mq,api,local(default)
     * 配置rpc和api服务地址不需要加schema，例如http,tcp
     */
    'driver_config' => [
        'local' => [],
        'mq' => [
            'host' => '127.0.0.1',
            'port' => '11300',
            'channel' => 'Lrpc_uDi7q',
            'username' => '',
            'password' => '',
        ],
        'rpc_port' => 1314,
    ],
];
```
```php
/**
 * rpc.php
 * 模块信息配置
 * demo
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
```
> 注意：如果同一个模块注册在多个API/RPC驱动服务器上，则系统会自动随机获取一个服务器连接

#### 流程图

![QwSCM8.png](https://s2.ax1x.com/2019/12/09/QwSCM8.png) 

#### Usage
* ##### 修改配置文件module.php
```php
// 配置模块映射
'modules' => [
    'Log' => '\\App\\RpcTest\\Log',
    'SMS' => '\\App\\RpcTest\\SMS',
    'News' => '\\App\\RpcTest\\News',
]
// 注册模块
'registration' => [
    'local' => ['Log'],
    'mq' => ['SMS'],
    'rpc' => [],
    'api' => ['News'],
]
```
* ##### 调用模块   

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

#### 部署原则

* 本地化相关
```
调用不同进程上对象的方法要比调用进程内对象的方法慢数百倍；将对象移动到网络中的另一台计算机上，这种方法调用又会慢数十倍
如果决定或被迫分布全部或部分业务逻辑层，那么应当保证经常交互的组件被放置在一起。换句话说，您应当本地化相关内容。
```
* 无状态系统
```
尽可能无状态，只有当业务确实需要，才使用状态。无状态系统易于扩展，有状态系统不易扩展且状态复杂时更易出错。
```
* 粗粒度接口
```
设想一下每个属性和方法调用跨越大西洋进行遍历，这将会产生严重的性能问题
```
* 异步设计
```
能异步尽量用异步，只有当绝对必要或者无法异步时，才使用同步调用
```
* 接口式编程/分布式编程
```
如果一台计算机能够在5秒钟内完成的任务，那么5台计算机以并行的方式一起工作时就能在1秒钟内完成一项任务
```
* 水平扩展而非垂直升级
```
永远不要依赖更大、更快的系统
小构建、小发布和快试错
```
* 设计多活数据中心

#### TODO
- [x] 增加服务启动命令
- [x] 增加服务查看命令
- [x] 增加服务重启命令
- [x] 增加服务暂停命令
- [x] 支持hprose驱动
- [x] 增加日志监控
- [x] 增加安全验证
> 注意：mq驱动建议做广播使用，其他远程调用建议使用rpc和api驱动


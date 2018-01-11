<?php
use \Workerman\Worker;
//require_once __DIR__ . '/../vendor/autoload.php';
$task = new Worker();
$task->name = 'task init';
$task->count = 4;
$task->onWorkerStart = function($task)
{

};

// 连接关闭时，删除对应连接的定时器
$task->onClose = function($connection)
{
};
// task名称，status方便查看
// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
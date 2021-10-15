<?php

// [ 应用入口文件 ]
header('Access-Control-Allow-Origin: *');  //跨域问题
header('Access-Control-Allow-Methods:POST'); //接受POST
header('Access-Control-Allow-Headers:x-requested-with,content-type,accessToken');  //接受header头类型

// 定义运行目录
define('WEB_PATH', __DIR__ . '/');

// 定义应用目录
define('APP_PATH', WEB_PATH . '../source/application/');

// 加载框架引导文件
require APP_PATH . '../thinkphp/start.php';

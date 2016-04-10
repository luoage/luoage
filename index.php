<?php
/**
 * 使用说明
 * PHP>=5.3,支持PHP匿名函数
 */
//1.202.52.34
//if($_SERVER['REMOTE_ADDR'] !== '1.202.52.34' ) exit(' 17500 testing...');
// 前台不能包含后台请求数据

require_once('./format.php');
define('LUOAGE','./LuoAge');//定义框架目录
define('LUOAGE_APP','./test');//定义应用目录
define('USE_FW',true);

define('PLUGROOT','../plugs');//定义插件路径
include(LUOAGE.'/LuoAge.php');//定义框架入口文件
?>

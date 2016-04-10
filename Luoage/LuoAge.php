<?php
/**
 * Luoage入口文件
 * @author luoage@msn.cn
 * @version $$version
 */
if(! defined('LUOAGE')) exit;
if(! defined('LUOAGE_APP')) define('LUOAGE_APP','app');
if(! defined('USE_FW')) define('USE_FW', true );

include(LUOAGE.'/config/base.php');//加载基础文件
c(array('__fileTimeStart' => microtime(true)));//初始化时间
c(array('__route'=>include(LUOAGE.'/config/route.php')));//加载路由文件
//setIncludePath();//设置包含路径
if( ! defined('USE_FW') || USE_FW ) entry::init(); else entry::idp();//初始化
?>

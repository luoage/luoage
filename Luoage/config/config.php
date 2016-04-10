<?php

/**
 * 框架系统配置文件
 * @package luoage
 * @author luoage@msn.cn
 * @version $$version
 */
if (!defined('LUOAGE')) exit;
return array(
    'LINK_TYPE'=>'db1',//数据库连接标志位,主要进行多数据库连接使用,该属性在调用model时手动重新配置
    'DB_CHECK_FIELDS' => true, //默认开启字段检测
    'DB_ARRAY_RECURSIVE' => false, //默认关闭写入的参数递归

    //模板加载
    'TPL_FILE_SUFIX' => '.html', //模板,布局后缀名
    'TPL_FILE_PATH' => LUOAGE_APP . '/template', //模板,布局默认路径
    'TPL_PUBLIC_PATH' => 'public', //公共文件夹
    'TPL_PUBLIC_FILE' => '/redirect', //默认跳转页面
    'TPL_CSS_URL' => 'css/', //CSS地址
    'TPL_JS_URL' => 'js/', //JS地址
    'TPL_DEFAULT_LAYOUT' => 'index', //默认布局文件名

    //模板引擎设置
    'TPL_LEFT_DELIMITER' => '{<', //变量输出左分隔符
    'TPL_RIGHT_DELIMITER' => '>}', //变量输出右分隔符
    //模拟PHP分隔符，不能与变量输出分隔符相同，使用时，应尽量避免常用字符以免被替换掉，两个同时存在才进行替换
    'TPL_LEFT_PHP' => '', //左
    'TPL_RIGHT_PHP' => '', //右
    //静态化元素
    'STATIC_TEMPLATE_DIR' => LUOAGE_APP . '/cache/temp/static/',
    'ELEMENT_STATIC_PAGE' => true, //静态化元素
    'ELEMENT_STATIC_PAGE_TIME' => 1200, //如果未填写时间,默认静态化时间(秒/s)

    //错误调试
    'DEBUG_SHOW' => true, //页面显示错误
    'DEBUG_LOG' => false, //文件记录错误

    //url
    'URL_PATH_INFO_DELIMITER' => '-', //url分隔符,中横线
    'URL_SUFIX' => '.html', //url后缀
    'URL_TYPE' => 0, //0,path_info传值,1,get传值,2,手动传值,只有为2时,下面一二两项才会生效,排除2,下面的三四两项才会生效
    'URL_DEFAULT_CLASS_KEY' => 'c', //默认$_GET类传值使用键值
    'URL_DEFAULT_FUNC_KEY' => 'f', //默认$_GET方法传值使用键值
    'URL_DEFAULT_CLASS' => 'index', //默认加载类
    'URL_DEFAULT_FUNC' => 'index', //默认加载方法

    //系统默认常用功能
    'TIME_DEFAULT_SET' => 'PRC', //设置区时默认为china
    'ENCODING_DEFAULT_SET' => 'utf-8', //设置编码默认为utf-8
    'MAGIC_QUOTES_GPC' => true, //开启魔术转义
    'ADDITION_SCRIPT'=>date('Y-m-d'), // 默认增加脚本以天数缓存
	'ALL_STATIC_TEMPLATE_ONELINE' => false, // 全清,空格制符表换行符

    //---------session重写
    'CUSTOM_PHPSESSID'=>'0phk0fo3gt0ilpqaril530qc7m0', // 自定义sessionid通过$_REQUEST获取
    'SESSION_REWRITE' => false, //不进行session重写，只有开启时，下面参数才有效,----//session重写，session重写时，需要加上一个特定数据表
    'SESSION_DEFAULT_IP' => '23.123.12.2', //如果没有抓取到IP，自定义
    'SESSION_DEFAULT_AGE' => 'jfeee5^%ei\n iefje_', //如果没有抓取到用户版本信息，自定义
    'SESSION_DEFAULT_REPLACE' => 'fwj&&()', //session安全加密字段,如果修改，登陆的用户会立刻掉线,建议空闲时修改,以达到安全的目的
    'SESSION_TIMEOUT' => 1800, //session超时时间，半小时
    'SESSION_SIGN' => 'app', //任意类型，每个项目中的app不应该相同
    'COOKIE_TIMEOUT' => 3600 * 240, //cookie超时时间
    'COOKIE_PATH' => '/', //cookie有效路径
    'COOKIE_DOMAIN' => '', //cookie作用域
    'COOKIE_SECURE' => false, //true,https安全传输


    //插件总开关,插件调用顺序与其在这里的顺序一致
    'PLUG_ACCESS' => true,
    
    //-----------以下都是插件程序----------------------------
    'PLUG_ALLOT_CONFIG' => true, //配置文件中config_开头的文件放入所有模板中

    'PLUG_STATIC_TEMPLATE' => false, //静态化页面输出
    'STATIC_TEMPLATE_ONELINE' => true, // 静态化页面时清除空格,制符表,换行符
    'STATIC_TEMPLATE_TYPE' => '1', //1->根据方法名称结尾为 STATIC_TEMPLATE_SUFFIX 判断是否需要缓存( 暂不支持 )，2->get值。3->数据库缓存( 暂不可用 )。
    'STATIC_TEMPLATE_SUFFIX' => '_st', //方法后缀_st  STATIC_TEMPLATE_TYPE=1 
    'STATIC_TEMPLATE_GET' => 'cache', //$_GET['cache'] STATIC_TEMPLATE_TYPE=2
    'STATIC_TEMPLATE_TIMTEOUT' => 24 * 60 * 60, //静态页面持续时间(秒/s)，如果为数据库缓存，此项无效。


    // 加密与解密
    'BASE64TABLE' => 'xmvdN1F9sqC-t6LoKuWU7phzHcVy3wjPRSBibQfZX2l8MJOk+5eA0GrEDTYnIag4', // base64Encode/base64Decode编码表
    'BAND62TABLE' => 'gXdoDnSuQ4kjF6bsL1aqyrHKE9v7Mh3PRY8xlBVec0zmtOIi2fwZCGNTAp5WUJ', // 62进制编码表

    //系统自定义设置,以config_开始,系统自动将其放入assign中进行文件变量替换,无论键值大小写，使用时，都是小写,需开启PLUG_ALLOT_CONFIG
    //example : 'config_image_path'=>'/admin_pic/',//图片路径
);
?>

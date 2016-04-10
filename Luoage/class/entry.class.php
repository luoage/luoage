<?php

/**
 * 应用入口文件
 * @author luoage@msn.cn
 * @version $$version
 */
if (!defined('LUOAGE')) exit;

class Entry extends Delimiter {

    /**
     * url地址解析,页面编码设置,时区设置,运行action文件
     * @param void
     * @access public
     * @static
     * @return void
     */
    public static function init() {
        self::config();
        header('Content-Type:text/html; charset=' . c('ENCODING_DEFAULT_SET'));//设置编码
        self::operation(); //运行action
    }
    /**
     * 配置文件
     * @access private
     * @param void
     * @return void
     */
    private static function config(){
        self::initSet();
        // session 是否重写
        if (c('SESSION_REWRITE') === true){
            new ses;
            $_SESSION['whereAmI'] = c('__cf');
            $_SESSION['sesKey'] = isset($_COOKIE['luoageId']) ? $_COOKIE['luoageId'] : '';
        }else{
            //sessionid自定义
            if(isset($_REQUEST) && isset($_REQUEST[c('CUSTOM_PHPSESSID')]) && $_REQUEST[c('CUSTOM_PHPSESSID')] ){
                session_id($_REQUEST[c('CUSTOM_PHPSESSID')]);
            }
            session_start();
        }

        set_exception_handler('getException'); //抛出错误
        date_default_timezone_set(C('TIME_DEFAULT_SET'));//设置时区
        c(array('MAGIC_QUOTES_GPC_' => false));//开启魔术转义
        if (!get_magic_quotes_gpc() && c('MAGIC_QUOTES_GPC')) {
            c(array('MAGIC_QUOTES_GPC_' => true));
        }

    }
    /**
     * 调用action方法,并设置自定义设置模拟到全局变量中。
     * @param void
     * @access private
     * @return void
     */
    private static function operation() {
        $__cf = self::distribute();
        if (!is_array($__cf)) throwException('$_SERVER[\'PATH_INFO\']解析有误!');
        list($class, $method) = each($__cf);
        $class = $class . 'Action';
        if (!class_exists($class, true)) throwException($class . '类不存在');
        if (!method_exists($class, $method)) throwException($class . '类中不存在' . $method . '方法');
        c(array('__cf' => $__cf));
        //---插件start
        if (c('PLUG_ACCESS')) plug::init();
        //------插件end
        $return = new $class;
        $return->$method();
        //如果debug开启 渲染debug
        if (c('DEBUG_SHOW') === true) echo $return->element('debug', array('public' => 'debug'));
    }

    /**
     * 错误调试
     * @param void
     * @access private
     * @return <string> 返回错误信息
     */
    public static function debug() {
        return str_replace("\n","<br />\n", debug() );
    }

    /**
     * 加载函数,生成目录,示例文件
     * @param void
     * @access private
     * @return void
     */
    private static function initSet() {
        include(LUOAGE.'/public/functions.php'); // 加载系统函数文件
        c(include(LUOAGE . '/config/config.php')); //加载系统配置文件,定义配置属性
        $db = include(LUOAGE . '/config/databases.php');
        c($db);
        $db && isset($db[c('LINK_TYPE')]) && c($db[c('LINK_TYPE')]);
        self::addApp();
        fnc(LUOAGE_APP.'/config/');
        c(c('databases')); // 加载数据库连接信息
        $dbApp = c('databases');
        if( $dbApp && isset($dbApp[c('LINK_TYPE')]) ) c($dbApp[c('LINK_TYPE')]);
        
        if(defined('LUOAGE_INDEX') && file_exists(LUOAGE_INDEX.'/config.php')) c(include(LUOAGE_INDEX. '/config.php'));// 加载目录目录下配置文件
        self::includeFunc(LUOAGE_APP.'/public/');//加载自定义函数文件
    }
    /**
     * 生成应用目录,css,js,pic目录,生成应用示例文件
     * @param void
     * @access private
     * @return void
     */
    private static function addApp(){
        $dirArr = array(
            'operation',
            'operation/model',
            'operation/action',
            'operation/action/component',
            'custom',
            'template',
            'template/index',
            'template/layout',
            'template/elements',
            'config',
            'cache',
            'cache/error_log',
            'cache/base_log',
            'cache/shell',
            'cache/temp',
            'cache/temp/php',
            'cache/temp/static',
            'cache/database',
            'public'
        );
        mkdirs(LUOAGE_APP, $dirArr);
        if(! file_exists(LUOAGE_APP . '/config/config.php')){
            $addition = array('css', 'js', 'pic');
            mkdirs('.', $addition);
            self::mkfiles(LUOAGE_APP);
        }
    }

    /**
     * 包含某文件夹下所有文件(支持多重文件夹)
     * @param <string> $dir
     * @param <string> $suffix='.php'
     * @static
     * @return null
     */
    public static function includeFunc($dir, $suffix = '.php') {
        incFnc($dir, $suffix );
    }

    /**
     * 生成应用示例文件
     * @param string $luoagePath
     * @access private
     * @return null
     */
    private static function mkfiles($luoagePath = '') {
        $file = array(
            LUOAGE . '/example/config.php' => $luoagePath . '/config/config.php', //config
            LUOAGE . '/example/databases.php' => $luoagePath . '/config/databases.php', //databases
            LUOAGE . '/example/indexaction.class.php' => $luoagePath . '/operation/action/indexaction.class.php', //action
            LUOAGE . '/example/index.html' => $luoagePath . '/template/index/index.html', //view
            LUOAGE . '/example/layout.html' => $luoagePath . '/template/layout/index.html', //layout
            LUOAGE . '/example/element.html' => $luoagePath . '/template/elements/index.html', //element
            LUOAGE . '/example/debug.html' => $luoagePath . '/template/elements/debug.html', //element
            LUOAGE . '/example/publiccomponent.class.php' => $luoagePath . '/operation/action/component/publiccomponent.class.php', //component
        );
        array_walk($file, array('self', 'transferContent'));
    }

    /**
     * 将文件内容写入某文件
     * @param <array> $transferFile 将key的文件转入value
     * @access public
     * @return void
     */
    public static function transferContent($value, $key) {
        if (!file_exists($value)) file_put_contents($value, file_get_contents($key));
    }
    
    /**
     * 框架之外调用方法，可以使用本程序任意类库
     * @access public
     */
    public static function idp(){
         self::config();
    }

}

?>
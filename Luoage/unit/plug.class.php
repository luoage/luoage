<?php

/**
 * 插件管理程序,这些程序放在所限制的url前执行，
 * @author luoage@msn.cn
 * @version $$version
 */
class plug {

    /**
     * 初始化插件程序
     * @access public
     * @static 
     * @return void
     */
    public static function init() {
        if (is_array($c = c())) {
            foreach ($c as $key => $value) {
                if (preg_match('/^plug_/i', $key)){
                    if (strtoupper($key) == 'PLUG_ACCESS') continue;
                    if ($value !== true) continue;
                    $method = strtolower(substr($key, 5));
                    if (!method_exists('plug', $method)) throwException('plug中不存在'.$method.'方法');
                    self::$method();
                }
            }
        }
    }

    //------------------------------  系统插件  ----------------------------------------------
    /**
     * 配置文件中config_开头的文件放入所有模板中
     * @access private
     * @static 
     * @return void
     */
    private static function allot_config() {
        if (is_array($c = c())) {
            foreach ($c as $key => $value) {
                if (preg_match('/^config_/i', $key))
                    action::$vars[$key] = $value;
            }
        }
    }

    /**
     * session重写开启，如果未开启session重写,则开启session_start(),记录当前所在页面进入session['whereAmI']
     * @access private
     * @static 
     * @return void
     *
    private static function session_rewrite() {
        new ses;
        $_SESSION['whereAmI'] = c('__cf');
        $_SESSION['sesKey'] = isset($_COOKIE['luoageId']) ? $_COOKIE['luoageId'] : '';
    }*/

    /**
     * 生成和使用静态页面，通过某种限制，控制url自动使用静态页
     * @access private
     * @static 
     * @return void
     */
    private static function static_template(){
        $ts = static_template::init();
        if( $ts === true ) $ts = c('__fileName');
        //var_dump($ts);
        c(array('__static_template' => $ts ));
    }

    //------------------------------  自定义插件  ----------------------------------------------

}

?>
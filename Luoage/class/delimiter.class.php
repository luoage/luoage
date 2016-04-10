<?php

/**
 * URL传值处理
 * @package luoage
 * @author luoage@msn.cn
 * @version $$version
 */
if (!defined('LUOAGE')) exit;
class Delimiter {
    /**
     * 解析$_SERVER['PATH_INFO']
     * @access private
     * @param void
     * @return array 所用类名和方法名
     */
    private static function cutPathInfo() {
        $pathInfo = self::setPathInfo();
        //preg_replace('#(\w+)' . c('URL_PATH_INFO_DELIMITER') . '([^' . c('URL_PATH_INFO_DELIMITER') . ']+)#e', '$path[\'$1\']=\'$2\'', $pathInfo);
        
        $pathInfoArr = explode(c('URL_PATH_INFO_DELIMITER'),$pathInfo);
        $path = array();
        for($length = count($pathInfoArr),$i=0;$i<$length;$i+=2){
            $path[$pathInfoArr[$i]] = isset($pathInfoArr[$i+1]) ? $pathInfoArr[$i+1] : '';
        }
        
        if (is_array($path))
            $pathInfo = array_splice($path, 0, 1);
        if (is_array($path) && !empty($path))
            $_GET = array_merge($_GET, $path);
        return $pathInfo;
    }

    /**
     * 处理$_SERVER['PATH_INFO']
     * @abstract 获取$_SERVER['PATH_INFO']
     * @access private
     * @param void
     * @return string PATH_INFO信息
     */
    private static function setPathInfo() {
        
        //echo c('URL_PATH_INFO_DELIMITER');
        $pathInfo = trim(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null, c('URL_PATH_INFO_DELIMITER') . '/');
        
        // 不存在pathinfo只有request_uri
        if( !isset($_SERVER['PATH_INFO']) && isset($_SERVER['REQUEST_URI']) ){
            $requestUri = strpos($_SERVER['REQUEST_URI'],'?') ? explode( '?',$_SERVER['REQUEST_URI'] ) : explode( '&',$_SERVER['REQUEST_URI'] );
            $pathInfo = trim($requestUri[0],c('URL_PATH_INFO_DELIMITER') . '/');
        }
        if (c('URL_SUFIX'))
            $pathInfo = preg_replace('/\.' . substr(c('URL_SUFIX'), 1) . '$/', '', $pathInfo);
        //设置默认主页
        if ($pathInfo == '')
            return c('URL_DEFAULT_CLASS') . c('URL_PATH_INFO_DELIMITER') . c('URL_DEFAULT_FUNC');
        //if($pathInfo != '' && !strpos($pathInfo,c('URL_PATH_INFO_DELIMITER'))) return rtrim($pathInfo,c('URL_SUFIX')).c('URL_PATH_INFO_DELIMITER').c('URL_DEFAULT_FUNC');
        if (!preg_match('#(\w+)' . c('URL_PATH_INFO_DELIMITER') . '\w+#', $pathInfo, $match)) {
            $pathInfo = explode(c('URL_PATH_INFO_DELIMITER'), $pathInfo);
            return $pathInfo[0] . c('URL_PATH_INFO_DELIMITER') . c('URL_DEFAULT_FUNC');
        }
        return $pathInfo;
    }

    /**
     * 分配操作类和操作方法，去除js,css等
     * @param void
     * @access protected
     * @return array
     */
    protected static function distribute() {
        $type = array('cutPathInfo', 'cutGet', 'free');
        $func = array_key_exists(c('URL_TYPE'), $type) ? $type[c('URL_TYPE')] : $type[1];
        return self::$func();
    }

    /**
     * 处理$_GET
     * @abstract 解析$_GET
     * @access private
     * @return array 所用类名和方法名
     */
    private static function cutGet(){
        if (empty($_GET))
            return array(c('URL_DEFAULT_CLASS') => c('URL_DEFAULT_FUNC'));
        else
            return $_GET;
    }

    /**
     * 处理手动取值
     * @todo 只能拿到默认项
     * @access private
     * @return array 所用类名和方法名
     */
    private static function free() {
        $ck = isset($_GET[c('URL_DEFAULT_CLASS_KEY')]) ? $_GET[c('URL_DEFAULT_CLASS_KEY')] : c('URL_DEFAULT_CLASS');
        $fk = isset($_GET[c('URL_DEFAULT_FUNC_KEY')]) ? $_GET[c('URL_DEFAULT_FUNC_KEY')] : c('URL_DEFAULT_FUNC');
        return array($ck=>$fk);
    }

}

?>

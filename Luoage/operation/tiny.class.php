<?php

/**
 * 功能类，由组件和控制器调用
 * @package luoage
 * @author luoage@msn.cn
 * @version $$version
 */
if (!defined('LUOAGE')) exit;

class Tiny {

    /**
     * 收集assign数据放入页面
     * @var <array>
     * @static 
     * @access public
     */
    public static $vars = array();

    /**
     * 得到js,css数据放入head头部
     * @var <array>
     * @access public
     */
    public $addition = array();

    /**
     * 页面标题
     * @var <string>
     * @access public
     */
    public $title = '';

    /**
     * 模板静态变量，
     * @var obj
     * @access private
     * @static static
     */
    public static $template = '';

    /**
     * 是否有布局文件,根据是否有布局文件，来决定输出对象
     * @var boolen
     * @access public
     */
    public $havLayout = true;

    /**
     * 设置模板中的变量,支持字符串和数组,否则返回所有已经设置的变量
     * @param  $key
     * @param <mixed> $value
     * @access public
     * @return void
     */
    public function assign($key = '', $value = '') {
        if (empty($key))
            return self::$vars;
        if (is_string($key)) {
            self::$vars[$key] = $value;
            return;
        }
        if (is_array($key))
            self::$vars = array_merge(self::$vars, $key);
    }

    /**
     * 添加css和js
     * @param <string> $addition
     * @access public
     * @return void
     */
    public function addition($addition = '') {
        if ( ! $addition ) return;
        if (is_string($addition)) {
            $this->addition[] = $this->link($addition);
            return;
        }
        if (is_array($addition)) $this->addition = array_merge(array_map(array('self', 'link'), $addition), $this->addition);
    }

    /**
     * 外链拼写完整
     * @param <string> $addition
     * @access public
     * @return <string>
     */
    public function link($addition = ''){
        if(c('ADDITION_SCRIPT'))
            $addition = strpos($addition,'?') ? $addition.'&'.c('ADDITION_SCRIPT') : $addition.'?'.c('ADDITION_SCRIPT');

        if (strpos($addition, '.js')) {
            strpos($addition, '/') === false && $addition = c('TPL_JS_URL') . $addition;
            return '<script type="text/javascript" language="javascript" src="' . $addition . '"></script>';
        }
        if (strpos($addition, '.css')) {
           	strpos($addition, '/') === false && $addition = c('TPL_CSS_URL') . $addition;
            return '<link rel="stylesheet" type="text/css" href="' . $addition . '" />';
        }
        return;
    }

    /**
     * 模板字符串替换，使用md5加密生成文件名称,并渲染文件内容,如果模板修改，重新生成temp文件
     * @param string $file
     * @param array $vars
     * @param boolen $end 如果end = true, 默认对layout进行渲染
     * @param $cacheName 元素缓存标志位
     * @access public
     * @return string $getContent
     */
    public function outFile($file = '', $vars = array(), $end = false, $cacheName = '' ){
        if ( ! file_exists($file) ) throwException($file . '不存在');
        if (! $vars) $vars = self::$vars;
        extract($vars, EXTR_OVERWRITE);
        if (isset($title)) $this->title = $title;

        //查看缓存是否存在，模板是否被更改过
        $fileName = md5($file);
        $tempPath = LUOAGE_APP . '/cache/temp/php/';
        $tmpFile = $tempPath . $fileName . '.php';
        $staticPath = c('STATIC_TEMPLATE_DIR'); //静态文件目录
        //模板标签修改成PHP可执行标签
        if ( ! file_exists($tmpFile) || filemtime($tmpFile) < filemtime($file) ) {
            $template = self::$template;
            if (!$template) $template = self::$template = new template;
            wFile($tmpFile, $template->r(rFile($file)));
        }
        //模拟PHP环境
        if ($end){
            
            /* 测试eval
            $a = microtime(true);
            $getContents = eval('include($tmpFile);');
            echo $getContents;
            echo microtime(true)-$a;
            */
            
            ob_start();
            include($tmpFile);
            $getContents = ob_get_contents();
            ob_end_clean();

            
            

			// 清除空格/换行/制符表
            //var $pattern = '/(\n|\r| |\t)+/';
			if(c('ALL_STATIC_TEMPLATE_ONELINE')) $getContents = preg_replace( '/[\n\r \t]+/', ' ', $getContents );
            if ( c('PLUG_STATIC_TEMPLATE') === true && c('__static_template') !== false ){ //是否需要缓存成静态页面
                $f = $staticPath. c('__static_template').c('TPL_FILE_SUFIX');
                // 写入缓存的静态文件
                $getContents = c('STATIC_TEMPLATE_ONELINE') && !c('ALL_STATIC_TEMPLATE_ONELINE') ? preg_replace( '/[\n\r \t]+/', ' ', $getContents ) : $getContents;
                wFile($f, $getContents);
            }
            return $getContents;
        }

        //处理元素
        $additionEle = $this->addition;
        $this->addition = array(); // 清空引用的脚本
        ob_start();
        include($tmpFile);
        $getContents = ob_get_contents();
        ob_end_clean();
        if (c('__static_element') === true) { //元素进行静态缓存
            $staticFile = $staticPath.$fileName.$cacheName.c('TPL_FILE_SUFIX'); //静态文件
            if($this->addition){
                $this->staticElement($staticPath, $file,$cacheName);
            }
            //写入静态文件
            wFile($staticFile, $getContents);
        }
        if( $this->addition || $additionEle )
            $this->addition = array_merge($additionEle, $this->addition ); // 合并原先存在的脚本
        return $getContents;
    }

    /**
     * js,css把css,js写入文件中
     * @param $staticPath 静态目录
     * @param $file 含有目录的源文件
     * @param $cacheName 
     * @access public 
     * @retun void
     */
    public function staticElement($staticPath, $file,$cacheName=''){
        if (! $staticPath) return;
        // 文件名是根据 静态缓存路径 + 文件相对首地址路径 + 标志位 决定的
        $additionFile = $staticPath . md5($staticPath.$file).$cacheName.c('TPL_FILE_SUFIX');
        $additionContents = implode('', $this->addition);
        //写入js,css等
        wFile($additionFile, $additionContents);
    }

    /**
     * 获取模板文件路径及其名称
     * @param <string> $file
     * @param <string> $tplFilePath
     * @access public
     * @return <string>
     */
    public function fileName($file = '', $tplFilePath = '') {
        if (!$tplFilePath) $tplFilePath = c('TPL_FILE_PATH');
        $cf = c('__cf');
        reset($cf);
        list($class, $method) = each($cf);
        if (!$file) {
            $file = $class . '/' . $method . c('TPL_FILE_SUFIX');
        } elseif (!strpos($file, '/')) {
            $file = $class . '/' . $file . c('TPL_FILE_SUFIX');
        } elseif (!strpos($file, '.')) {
            $file = $file . c('TPL_FILE_SUFIX');
        }
        return rtrim($tplFilePath, '/') . '/' . $file;
    }

    /**
     * 获取类名
     * @param void
     * @access public
     * @return <string>
     */
    public function actionName() {
        $cf = c('__cf');
        reset($cf);
        list($class) = each($cf);
        return $class;
    }

    /**
     * 获取方法名
     * @param void
     * @access public
     * @return <string>
     */
    public function methodName() {
        $cf = c('__cf');
        reset($cf);
        list($class, $method) = each($cf);
        return $method;
    }

    /**
     * 判断元素是否需要静态缓存，并获取之
     * @param $srcFile 需要缓存的文件
     * @param $staticPath 缓存目录
     * @param $cache = array($time 缓存时间，默认为系统缓存,cacheName 缓存标志位)
     * @access public 
     * @return string
     */
    public function elementFile($srcFile, $staticPath, $cache){
        $time = $cache['time'];
        $cacheName = $cache['cacheName'];

        if (!$srcFile || !file_exists($srcFile) || ! c('ELEMENT_STATIC_PAGE') || !$staticPath) return false;
        //可以进行静态缓存
        c(array('__static_element' => true));

        $tempPath = $staticPath; //静态文件目录
        $file = md5($srcFile).$cacheName.c('TPL_FILE_SUFIX');
        $staticFile = $tempPath . $file;

        //1,判断静态文件是否存在
        //2,判断时间是否过期(如果没写时间,默认使用配置文件中的时间)
        if(file_exists($staticFile) && ( filemtime($staticFile) + ( $time ? intval($time) : c('ELEMENT_STATIC_PAGE_TIME') ) > time() )){
            $additionFile = $tempPath . md5($tempPath.$srcFile).$cacheName.c('TPL_FILE_SUFIX'); 
            // 判断是否存在脚本,如果存在,读取脚本,存放于 this.addition中
            if (file_exists($additionFile)) $this->addition[] = rFile($additionFile);
            return rFile($staticFile);
        }
        return false;
    }

}

?>

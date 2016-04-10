<?php
/**
 * 基础文件
 * @package luoage
 * @author luoage@msn.cn
 * @version $$version
 */
if (!defined('LUOAGE')) exit;

/**
 * @abstract 打印输出
 * @param <array,string,null,...> $var 
 * @return null
 */
function p($var,$isDump = false) {
    echo '<pre>';
    if($isDump)
        var_dump($var);
    else
        print_r($var);
    echo '</pre>';
}
//匿名函数,两个下划线,5.3+
function __($obj){return $obj();}
/**
 * 配置/查看属性
 * @param <array,string> $config
 * @return <string>
 */
function c($config = '') {
    static $c = array();
    if ($config !== '' && is_string($config)) {
        $configLower = strtolower($config);
        return isset($c[$configLower]) ? $c[$configLower] : '';
    }
    if (is_array($config)) return $c = array_merge($c, array_change_key_case($config, CASE_LOWER));
    return $c;
}
/**
 * 显示当前运行状态
 * @return string
 */
function debug(){
    c(array('__fileTimeEnd' => microtime(true)));
    $files = get_included_files();
    $debug = 'CurTime:' . date('Y-m-d H:i:s')."\n"
            ."RunTime:" . round((c('__fileTimeEnd') - c('__fileTimeStart')), 4) . "s\n" 
            .(Model::$sql ? "[SQL]:\n" . substr(print_r(Model::$sql, true), 8, -2) : '')
            .'[Files]:' . "\n" . substr(print_r($files, true), 8, -2);
    return $debug;
}

/**
 * a additional 追加
 * r read       读取
 * w write      写入
 * b byte
 */
//读取文件,8K读取一次
function rFile( $file, $size = 8192 ) {
    $handle = fopen($file, 'rb');
    $contents = '';
    while (!feof($handle)) {
        $contents .= fread($handle, $size);
    }
    fclose($handle);
    return $contents;
}

//写入文件
function wFile($file, $contents, $type = 'wb') {
    $handle = fopen($file, $type);
    fwrite($handle, $contents);
    fclose($handle);
}

/**
 * 设置包含路径
 * @param null
 * @return null

function setIncludePath() {
    $path = explode(PATH_SEPARATOR, get_include_path());
    $fileDir = array(
            //加载框架文件
            LUOAGE.'/class/',
            LUOAGE.'/operation/action/',
            LUOAGE.'/operation/component',
            LUOAGE.'/operation/',
            LUOAGE.'/operation/model/',
            LUOAGE.'/operation/template/',
            LUOAGE.'/unit/',
            //加载应用文件
            LUOAGE_APP.'/custom/',
            LUOAGE_APP.'/operation/action/',
            LUOAGE_APP.'/operation/action/component',
            LUOAGE_APP.'/operation/model/',
    );
    $includePath = array_merge($path, $fileDir);
    set_include_path(implode(PATH_SEPARATOR, $includePath));
}
 */

/**
 * 自动加载,清除加载错误,所有目录名一律为小写(Luoage.php除外)
 * @param <string> $className
 * @return null
 */
function __autoload($className) {
    $className = strtolower($className);
    $__route = c('__route'); //加载路由设置

    //根据后缀判断应用目录
    if (!isset($__route[$className]))
        if (substr($className, -6, 6) == 'action' && $className != 'action')//判断是否为action
            $__route[$className] = $__route['appAction'] . '/' . $className . '.class.php';

        elseif (substr($className, -9, 9) == 'component' && $className != 'component')//判断是否为component
            $__route[$className] = $__route['appComponent'] . '/' . $className . '.class.php';

        elseif (substr($className, -5, 5) == 'model' && $className != 'model')//判断是否为model
            $__route[$className] = $__route['appModel'] . '/' . $className . '.class.php';
        else
            $__route[$className] = $__route['appCustom'] . '/' . $className . '.class.php'; //custom中查找
    include_once($__route[$className]);
}
/**
 * 异常抛出
 * @param $error 错误信息
 */
function throwException($error) {
    throw new TinyException($error);
    return;
}

/**
 * 统一处理错误,以页面显示/记录错误日志
 * 当日志开启，页面不直接输出错误时，返回页面不存在！(page not found.)
 * 错误信息包括1,请求时间，2,请求IP地址，3,错误信息
 * @param <object> $e
 */
function getException($e) {
    $error = date('Y-m-d H:i:s').' :00: '.(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR'] : 'noremoteIp').' :00: '.$e->getMessage()."\n";

    if (c('DEBUG_LOG')){ // 记录错误日志
        $error .= 'server:'.print_r(isset($_SERVER) ? $_SERVER : '' , true);
        $error .= 'session:'.print_r(isset($_SESSION) ? $_SESSION : '' , true);
        $error .= 'cookie:'.print_r(isset($_COOKIE) ? $_COOKIE : '' , true);
        //$error .= ' :00: ' . $error;
        $error .= '-----------------------------------------------';

        error_log($error . "\n\n", 3, LUOAGE_APP . '/cache/error_log/' . date('Y-m-d') . '.log');
        
    }

    if(c('DEBUG_SHOW')) echo $error;
    else{
         header("HTTP/1.0 404 Not Found !");// 设置header头部
         exit('页面不存在！');
    }
}

//统一错误日志
function errorLog($e,$fileName = '') {
    $fileName = $fileName ? $fileName : date('Y-m-d') . '.log';
    $fileName = LUOAGE_APP . '/cache/base_log/'.$fileName;
    error_log($e."\n", 3,$fileName);
}

/**
 * 生成文件
 * @param <string> $pathFile 路径+文件名
 * @param <mixed> $fileContent 写入的内容
 */
function mkFiles($pathFile, $fileContent = '') {
    if (!file_exists($pathFile)) file_put_contents($pathFile, $fileContent);
}

/**
 * 创建生成应用目录
 * @param <array> $dir
 * @param <string> $appDir
 * @access private
 * @return null;
 */
function mkdirs($appDir, $dir = '') {
    if (!is_dir($appDir)) mkdir($appDir, 0777);
    if (empty($dir)) return;
    if (is_string($dir)) {
        $f = $appDir . '/' . $dir;
        if (!is_dir($f)) mkdir($f, 0777);
        return;
    }
    if (is_array($dir)) {
        foreach ($dir as $value) {
            $v = $appDir . '/' . $value;
            if (!is_dir($v)) mkdir($v, 0777);
        }
        return;
    }
    return;
}


/**
 * 实例化model
 * @example $param = array('model'=>'','db'=>'') 
 * @staticvar array $a
 * @param type $param
 * @return object 实例化对象
 */
function m($param = array()){
    $param  = is_array($param) ? $param : array('model'=>$param);
    $model = isset($param['model']) ? $param['model'] : 'model';
    $db = isset($param['db']) ? $param['db'] : '';
    static $a = array();
    if(isset($a[$model.$db])) return $a[$model.$db];
    $model = $model == 'model' ? 'model' : $model.'Model';
    return $a[$model.$db] = new $model($db);
}
// 针对多数据库连接
function mm($param){
    if(is_string($param)) $param = array('db'=>$param);
    if(!is_array($param)) return 'need string or array types';

    $model = isset($param['model']) ? $param['model'] : 'model';

    // 加载文件
    $c = c();
    $param['db'] && isset($c['databases'][$param['db']]) && c($c['databases'][$param['db']]);

    $model = $model == 'model' ? 'model' : $model.'Model';
    return new $model($param['db']);
}

//获取参数的唯一值
function getAll( $get,&$g, $e = array()){
    if(is_array($get)){
        ksort($get);
        foreach($get as $k=>$v){
            if(in_array($k,$e)) continue;
            $g .= $k;
            if(is_array($v)){
                getMd5($v,$g,$e);
            }else{
                $g .= $v;
            }
        }
    }
}

/**
 * 判断前数组是否是后数组子集
 * @param $c 子集
 * @param $p 
 * @param $isNull 不判断子集内容为空的情况
 * @param $isLow $c键值转换成小写
 * @return boolean
 */
function isChild( $c,$p, $isNull = false,$isLow = false){
    if(! is_array($c) || ! is_array($p)) return false;
    $len = count($c);
    $n = 0;
    foreach($c as $a=>$v){
        $a = $isLow ? strtolower($a) : $a ;
        if( array_key_exists( $a ,$p) && ( (!$v && $isNull) || $p[$a] == $v) ) $n++;
    }
    /*
    p($c);
    p($p);
    echo $n,'<br />-------------<br />';
    */
    return $len == $n;
}

// 包含文件夹中的所有文件
function incFnc($dir, $suffix = '.php') {
    if (!$dir) return; else $dir = rtrim($dir, '/');
    $resource = opendir($dir);
    while ($name = readdir($resource)) {
        if ($name === '.' || $name === '..') continue;
        $fileName = $dir . '/' . $name;
        if (is_dir($fileName)) incFnc($fileName);
        if (is_file($fileName) && substr($name, -strlen($suffix), strlen($suffix)) === $suffix) include($fileName);
    }
    closedir($resource);
}

// 包含文件夹中的所有文件
function fnc($dir,$suffix = '.php') {
    if (!$dir) return; else $dir = rtrim($dir, '/');
    $resource = opendir($dir);
    while ($name = readdir($resource)) {
        if ($name === '.' || $name === '..') continue;
        $fileName = $dir . '/' . $name;
        if (is_dir($fileName)) fnc($fileName);
        if (is_file($fileName) && substr($name, -strlen($suffix), strlen($suffix)) === $suffix){
            $nm = substr($name,0, -strlen($suffix));
            if(in_array($nm,array('config')))
                c(include($fileName));
            else
                c(array($nm=>include($fileName)));
        }
    }
    closedir($resource);
}

// 删除文件夹内的所有文件,文件夹
function dFile( $dir, $suffix = '.html'){
    if (!$dir) return; else $dir = rtrim($dir, '/');
    $resource = opendir($dir);
    while ($name = readdir($resource)) {
        if ($name === '.' || $name === '..') continue;
        $fileName = $dir . '/' . $name;
        if (is_dir($fileName)) dFile($fileName,$suffix );
        if (is_dir($fileName)) rmdir($fileName);
        if($suffix){
            if (is_file($fileName) && substr($name, -strlen($suffix), strlen($suffix)) === $suffix) unlink($fileName);
        }else{
            if (is_file($fileName)) unlink($fileName);
        }
    }
    closedir($resource);
}

/**
 * 自动创建目录
 * @param string $dir    需要创建目录的路径
 * @param boolean $isEnd 最后一个是否创建
 * @param 创建目录的权限
 * @return void
 */
function autoMkDir( $dir,$isEnd = false,$right='0644' ) {
    if (! $dir) return;
    $dir = explode('/', $dir);
    if( ! $isEnd ) array_pop( $dir );
    $v = '';
    foreach ($dir as $value) {
        if(! $value || $value === '.' || $value === '.') continue;
        $v .= $value.'/';
        if (!is_dir($v)) mkdir($v, 0755, true);
    }
}

?>

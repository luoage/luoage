<?php
/**
 * 静态页面
 */
class static_template{
    public static $class;
	/**
	 * STATIC_TEMPLATE_TYPE = 1,根据method后缀
	 * @access private
	 * @static
	 * @return boolean
	 */
	private static function type1(){
        $method = self::method();
		if(! $method) return false;
		$strlen = strlen(c('STATIC_TEMPLATE_SUFFIX'));
		$suffix = substr($method,-$strlen);
		if($suffix !== c('STATIC_TEMPLATE_SUFFIX')) return false;
        $m = '';
        getAll( $_GET,$m );
        return $m;
	}
	
	/**
	 * STATIC_TEMPLATE_TYPE = 2,根据get传值
     * 缺点,如果get值中存在时间,则无法缓存,并且出现多次缓存不使用的情况
	 * @access private
	 * @static
     * @return file_contents || boolean
	 */
	private static function type2(){
		if( ! isset($_GET[c('STATIC_TEMPLATE_GET')]) ) return false;
        $m = '';
        getAll( $_GET,$m );
        return $m;
	}

	/**
	 * STATIC_TEMPLATE_TYPE = 3,根据数据库
	 * @access private
	 * @static
	 */
	private static function type3(){
        $method = self::method();
        $class = self::$class;
        if(! $method) return false;

        $v = m()->tableName('static_file')->select(array(
            'fields'=>array('v','p','t','e','c','isFull'),
            'condition'=>array('p'=>$class.'='.$method),
            'order'=>array('v'=>'DESC')
        ));
        if(! $v) return false;
        
        // 是否存在这样的缓存
        $k = -1;
        $g = array();
        
        if($_GET){ // 最小化键值
            foreach($_GET as $kg=>$tg){
                $g[strtolower($kg)] = $tg ;
            }
        }

        $leng = count($g);

        foreach($v as $a=>$b){
            if(! $b['v'] && $b['isFull']){
                $k = $a ;
                continue;
            }
            $vv = array();
            preg_replace('/\&?(\w+)=(\w+)*/e','$vv["\\1"]="\\2"',$b['v']); // 缓存规则 键=值&键=值,
            $vv[$class] = $method;

            if(isChild($vv,$g,true,true)){
				if(! $b['isFull']){
					if(count($vv) != $leng) continue ;
				}
                $k = $a;
                break;
            }
        }
        //echo $k;exit;
        if( $k<0 ) return false;
        $v = $v[$k];
        if(! $v['c']) return false; // 如果数据库存在,但是不允许缓存

        $e = $v['e'] ? explode(',',$v['e']) : array() ;
        getAll( $g , $v['v'] , $e);
        return $v;
	}
    /**
     * 处理静态文件
     * @access private
     * @param $tempFile (array || string) 缓存文件
     * @static
     * @return string
     */
    private static function handle( $tmpFile ){
        if(is_array($tmpFile)){
            $arr = $tmpFile;
            c( array('__fileName'=>md5( $arr['v'] ) ) );
            $tmpFile = c('STATIC_TEMPLATE_DIR').c('__fileName').c('TPL_FILE_SUFIX'); // 文件名
            $time = $arr['t'] ? $arr['t'] : c('STATIC_TEMPLATE_TIMTEOUT');
        }else{
            c( array('__fileName'=> md5( $tmpFile ) ) );
            $tmpFile = c('STATIC_TEMPLATE_DIR').c('__fileName').c('TPL_FILE_SUFIX'); // 文件名
            $time = c('STATIC_TEMPLATE_TIMTEOUT');      // 缓存时间
        }

        if( ! file_exists($tmpFile) || $time < time() - filemtime($tmpFile) ){
            return true;
        }
        return rFile($tmpFile);
    }
    /**
     * 处理method
     * @access private
     * @static
     * @rerun string
     */
    private static function method(){
        $cf = c('__cf');
		reset($cf);
        $ap = $cf;
		if(! is_array($ap)) return false;
		list($class,$method) = each($ap);
		if(! $method) return false;
        self::$class = $class;
        return $method;
    }
    /**
     * 根据配置文件，调用相关方法处理,返回静态文件
     * @access public
     * @static
     * @return boolen
     */
    public static function init(){
        $arr = array(1,2,3);
        if(! in_array(c('STATIC_TEMPLATE_TYPE'),$arr)) return false;
        $arr = array('type1','type2','type3');
        $file = self::$arr[c('STATIC_TEMPLATE_TYPE')-1]();
        if( ! $file ) return false;

        $contents = self::handle( $file );
        if($contents === false) // 达不到存储条件 
            return false;      
		elseif($contents === true) // 存储过期
            return true;     
		//输出静态页
        //echo c('STATIC_TEMPLATE_ONELINE') && ! c('ALL_STATIC_TEMPLATE_ONELINE') ? preg_replace( '/(\n|\r| |\t)+/', ' ', $contents ) : $contents;
        echo $contents;
        exit();
        
    }
	
}
?>

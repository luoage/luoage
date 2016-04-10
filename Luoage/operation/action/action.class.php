<?php
/**
 * 控制器基类
 * @package luoage
 * @author luoage@msn.cn
 * @version $$version
 */
class Action extends Tiny{
    /**
     * 使用组件
     * @var array
     * @access public
     */
    public $component = array();
    /**
     * 布局文件名
     * @var string
     * @access private
     */
    public $layout = '';
    
    public $layoutMeta = '';
    // 方法名参数
    private $fnParam = array();

    /**
     * 显示页面,最终布局
     * @param string $file
     * @param string $tplFilePath
     * @access public
     * @return void
     */
    public function render($file='',$tplFilePath = ''){
        $f = $this->fileName($file, $tplFilePath);

        //如果不需要布局文件
        if ($this->havLayout === false){
            $isReturn = c('PLUG_STATIC_TEMPLATE') === true && c('__static_template') !== false ? true : false;
            return $this->outFile( $f,array(),$isReturn );
        }

        $layout = array(
            'layoutContent' => $this->outFile($f),
            'layoutMeta' => $this->layoutMeta,
            'layoutEncoding' => c('ENCODING_DEFAULT_SET'),
            'layoutTitle' => $this->title,
            'layoutLink' => implode("\n", array_unique($this->addition) )."\n"
        );
        echo $this->outFile(
            $this->fileName( $this->layout ? 'layout/'.$this->layout.c('TPL_FILE_SUFIX') : 'layout/'.c('TPL_DEFAULT_LAYOUT').c('TPL_FILE_SUFIX') ),
            $layout,
            true
        );
    }

	/**
     * 页面跳转,设置默认选项
     * @param string $file
     * @param string $tplFilePath
     * @access public
     * @return void
     */
	public function redirect($file='',$tplFilePath = ''){
		self::$vars['layoutEncoding'] = c('ENCODING_DEFAULT_SET');
		self::$vars['goto'] = isset(self::$vars['goto']) ? self::$vars['goto'] : (isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'');
		self::$vars['gotoTime'] = isset(self::$vars['gotoTime']) ? self::$vars['gotoTime'] : 1;
        self::$vars['gotoTime'] = isset(self::$vars['error']) && self::$vars['gotoTime'] == 1 ? 3 : self::$vars['gotoTime'];
		//构造当前地址
		$host = isset($_SERVER['HTTP_HOST']) ? 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : '';
		if(empty(self::$vars['goto'])  || $host == self::$vars['goto'] ) self::$vars['goto'] = '/';
		$file = empty($file) ? c('TPL_PUBLIC_PATH').c('TPL_PUBLIC_FILE').c('TPL_FILE_SUFIX') : c('TPL_PUBLIC_PATH').$file.c('TPL_FILE_SUFIX');
		echo $this->outFile(
			$this->fileName($file,$tplFilePath),
			self::$vars,
            true
		);
	}
	/**
	 * 直接跳转，没有任何的页面渲染
	 * @access public
	 * @param string $requestUrl
	 * @param string $serverName
	 * @return void
	 */
	 public function jump($requestUrl,$serverName = ''){
		 echo '<script type="text/javascript">',
			 'setTimeout(function(){ window.location.replace("'.$serverName.$requestUrl.'");},0);',
			 '</script>';
		 exit;
	 }
    /**
     * 增加元素,尝试静态加载,建议在模板中使用
     * @param string $elementFile
     * @param mixed $component
	 * @param array 
     * $elementArr = array('isCache'=> boolen,--是否需要缓存,'time'=>'',缓存时间,'cacheName'=>'',缓存标志位 );
     * @param array $fnParam 方法后面的参数
     * @access public
     * @example $this->assign('elementOne',$this->element('element'));
     * @return string
     */
    public function element($elementFile = '',$component = '',$elementArr = array(),$fnParam = array()){
        $this->fnParam = $fnParam;
		$elementFile = ($elementFile ? 'elements/'.$elementFile : 'elements/'.$this->actionName()).c('TPL_FILE_SUFIX');
        //$elementFile = $elementFile ? ( strpos($elementFile,'/') ? $elementFile : 'elements/'.$elementFile ) : 'elements/'.$this->actionName();
		$fileName = $this->fileName($elementFile);
		$staticPath = c('STATIC_TEMPLATE_DIR');//静态文件目录
		
		c(array('__static_element'=>false));//初始化__static_element
        $cacheName = isset($elementArr['cacheName']) ? $elementArr['cacheName'] : '' ;
		if(isset($elementArr['isCache']) && $elementArr['isCache'] === true && c('ELEMENT_STATIC_PAGE')){ //尝试静态缓存
			$time = isset($elementArr['time']) ? $elementArr['time'] : '';
			$back = $this->elementFile( $fileName,$staticPath,array( 'time'=>$time,'cacheName'=>$cacheName ) );
			if($back !== false) return $back;
		}
		$vars = array();
        if(!empty($component)) $vars = $this->useComponent($component);
        return $this->outFile($fileName,$vars,false,$cacheName );
    }
    /**
     * 调用组件,并设置属性值
     * @param <mixed> $componentName
     * @access private
     * @return <string>
     */
    private function useComponent($componentName){
        $componentName = is_array($componentName) ? $componentName : array($componentName);
        $vars = array();
        array_walk($componentName,array('self','componentVars'),$vars);
        return $vars;
    }
    /**
     * 收集组件设置的值
     * @param mixed $value
     * @param string $key
     * @param array $vars
     * @access private
     * @return void
     */
    private function componentVars($value,$key,&$vars){
        if(is_int($key) && is_string($value) && !empty($value)){
            $componentName = $value.'Component';
            $component = $this->componentReturn($componentName);
        }else {
            if(!$key) throwException($value.'Component组件不存在');
            $componentName = $key.'Component';
            $component = $this->componentReturn($componentName);
            if(is_string($value) && !empty($value)) {
                if(!method_exists($componentName,$value)) throwException($componentName.'类中不存在'.$value.'方法');

                call_user_func_array(array($component,$value),$this->fnParam);
                //$component->$value();
            }
            
            if(is_array($value)){
                foreach($value as $v){
                    if(!method_exists($componentName,$v)) throwException($componentName.'类中不存在'.$v.'方法');

                    call_user_func_array(array($component,$v),$this->fnParam);
                    //$component->$v();
                }
            }
        }
        if(empty($component->vars) || !is_array($component->vars)) return;
        $vars = array_merge($vars,$component->vars);
    }
    /**
     * 使用组件,如果组件已经实例化，则返回实例化后的组件
     * @param string $componentName
     * @access private
     * @return object
     */
    private function componentReturn($componentName){
        if(empty($componentName) || !is_string($componentName)) return;
        if(isset($this->component[$componentName])) return $this->component[$componentName];
        if(!class_exists($componentName)) throwException($componentName.'类不存在');
        return $this->component[$componentName] = new $componentName;
    }
}
?>
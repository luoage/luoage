<?php
/**
 * Model基类
 * @package Luoage
 * @author luoage@msn.cn
 * @version $$version
 */
if(!defined('LUOAGE')) exit;
class Model extends Mysql {
    /**
     * 表名为键值,缓存的内容为值
     * @var <string>
     * @access public
     */
    public $cacheFields = array();
    /**
     * 设定规则，多表联合查询调用属性
     * @var <array> $link
     * @example $link 第一个为主表,接下来的都是附表,主表中不存在condition键值和map键值,只存在tableCondition值
     * @example $link = array('mainTable'=>'','user'=>array('userCondition'=>array(''),'condition'=>array(''),'map'=>array('')))
     * @access public
     */
    public $link = array();
    /**
     * 字段缓存目录名
     * @var <string>
     * @access public
     */
    public $cacheDirName = '';
    /**
     * 作用相当于一个标志位,作为衔接点
     * @var mixed
     * @access private
     */
    private $mixedSelect = false;
    private $mixedWhere = false;
    private $mixedGroup = false;
    private $mixedHaving = false;
    private $mixedOrder = false;
    private $mixedLimit = false;
    private $mixedUpdate = false;
    /**
     * 映射字段,仅针对写入更新操作进行映射
     * @var <array>
     * @example array('formFields'=>'dbFields')
     * @access public
     */
    public $map = array();
    
    public function  __construct($db = ''){
        $this->initModel($db);
    }
    
    //初始化数据库
    public function initModel($db = ''){
        $connectError = $this->connect($db);//连接数据库
        $this->isError($connectError);
        $selectDbError = $this->selectDb();//选择数据库
        $this->isError($selectDbError);
        $charsetError = $this->charset();//设置字符集
        $this->isError($charsetError);
        $cacheFileNameError = $this->cacheFileName();//获取缓存目录
        if(empty($cacheFileNameError)) throwException('请填写数据库名称');
        if(! file_exists($this->cacheDirName)) mkdirs($this->cacheDirName);
    }
    /**
     * 如果出现字符串错误,则抛出
     * @param <string> $string
     * @access public
     * @return void
     */
    public function isError($string){
        if(is_string($string)) throwException($string);
    }
    /**
     * 生成缓存目录名
     * @param void
     * @access public
     * @return <string>
     */
    public function cacheFileName(){
        $this->cacheDirName = LUOAGE_APP.'/cache/database/'.c('DB_NAME');
        return c('DB_NAME');
    }
    /**
     * 文件缓存字段,数据库-表名.sql为文件名,内容为字段序列化
     * @param <string> $tableName 表名
     * @access private
     * @return <array>
     */
    private function fileCacheFields($tableName = ''){
        if(isset($this->cacheFields[$tableName])) return $this->cacheFields[$tableName];
        $cacheDir = $this->cacheDirName;
        $tableFileName = $cacheDir.'/'.$tableName;
        if(file_exists($tableFileName)) return $this->getFileCacheFields($tableName);
        $field = $this->fields($this->tableName);
        $this->isError($field);
        mkFiles($tableFileName,serialize($field));
        return  $this->cacheFields[$tableName] = $field;
    }
    /**
     * 获取字段缓存数据
     * @param <string> $tableName 表名
     * @access public
     * @return <array>
     */
    public function getFileCacheFields($tableName = ''){
        $tableName = $tableName ? $tableName : $this->tableName;
        if(empty($tableName)) throwException('请设置$object->tableName($var),$var无需前后缀');
        if(isset($this->cacheFields[$tableName])) return $this->cacheFields[$tableName];
        $cacheDir = $this->cacheDirName;
        $tableFileName = $cacheDir.'/'.$tableName;
        if(file_exists($tableFileName)) return $this->cacheFields[$tableName] = unserialize(file_get_contents($tableFileName));
        return $this->fileCacheFields($tableName);
    }
    /**
     * 检查字段是否是有效字段
     * @param <array> $fields
     * @param <boolen> $isKey
     * @access protected
     * @return <array> $return
     */
    protected function checkFields($fields = array(),$isKey = true){
        if(!is_array($fields)) throwException('操作的字段必须为数组');
        $return = array();
        $func = c('DB_ARRAY_RECURSIVE') === true ? 'array_walk_recursive' : 'array_walk';
        $func($fields,array('self','fieldsCallBack'),array('isKey'=>$isKey,'return'=>&$return));
        return $return;
    }
    /**
     * 检查字段,并返回值
     * @param <mixed> $value
     * @param <string> $key
     * @param <array> $param
     * @access private
     * @return void
     */
    private function fieldsCallBack($value,$key,$param){
        static $tableFields = array();
        $checkFields = isset($tableFields[$this->tableName]) ? $tableFields[$this->tableName] : $tableFields[$this->tableName] = $this->fileCacheFields();
        $key = ($param['isKey'] === false) ? $value : $key;//选择执行的数据是否为键值
        if (!in_array($key, $checkFields) || empty($key)) {
            throwException($key . '不存在于' . $this->tableName . '表中');
        }
        if($param['isKey'] === false) return $param['return'][] = $value;//选择执行的数据是否为键值
        $param['return'][$key] = $value;
    }
    /**
     * 返回对象自身，如果方法跟数据库吻合，不需要输入参数
     * @param <string> $tableName = null
     * @access public
     * @return <self::object> $this
     */
    public function tableName($tableName = null) {
        if(! isset($tableName)) {
            reset(c('__cf'));
            list($tableName) = each(c('__cf'));
        }
        parent::tableName($tableName);
        return $this;
    }

    /**
     * 直接返回表名
     * @param <string> $tableName
     * @access public
     * @return string
     */
    public function sTableName($tableName){
        return c('DB_PREFIX').$tableName.c('DB_SUFIX');
    }

    /**
     * where条件中的语句
     * @param mixed $param
     * @access public
     * @return <self::object> $this
     */
    public function whereFields($param = '',$isCheck = true){
        if(empty($param)){
            $this->mixedWhere = false;
            return $this;
        }
        if(!is_array($param)){
            $this->mixedWhere = sprintf('WHERE %s',$param);
            return $this;
        }
        $func = c('DB_ARRAY_RECURSIVE') === true ? 'array_walk_recursive' : 'array_walk';
        if(c('DB_CHECK_FIELDS') && $isCheck){
            $param = $this->checkFields($param);
            $func = 'array_walk';
        }
        $this->mixedWhere = false;
        $addition = array('delimiter'=>'AND','attribute'=>&$return);
        $this->neaten($func,$param,$addition);
        $this->mixedWhere = 'WHERE'.substr($return,0,-3);
        return $this;
    }
    
    /**
     * update中的语句,如果存在$this->map则进行字段映射,如果参数为空,则使用$_POST作为参数
     * @param mixed $param
     * @access public
     * @return <self::object> $this
     */
    public function updateFields($param = ''){
        $param = $param ? $param : $_POST;
        if(empty($param)){
            $this->mixedUpdate = false;
            return $this;
        }
        if(!is_array($param)){
            $this->mixedUpdate = sprintf('SET %s',$param);
            return $this;
        }
        if(!empty($this->map)){//添加映射
            array_walk_recursive($param,array($this,'mapCallBack'),$back);
            $param = $back;
        }
        $func = c('DB_ARRAY_RECURSIVE') === true ? 'array_walk_recursive' : 'array_walk';
        if(c('DB_CHECK_FIELDS')){
            $param = $this->checkFields($param);
            $func = 'array_walk';
        }
        $this->mixedUpdate = false;
        $addition = array('delimiter'=>',','attribute'=>&$return);
        $this->neaten($func,$param,$addition);
        $this->mixedUpdate = 'SET'.substr($return,0,-1);
        return $this;
    }
    /**
     * group by选项
     * @param <mixed> $param
     * @access public
     * @return <self::object> $this
     */
    public function groupFields($param = ''){
        if(empty($param)){
            $this->mixedGroup = false;
            return $this;
        }
        if(!is_array($param)){
            $this->mixedGroup = sprintf('GROUP BY %s',$param);
            return $this;
        }
        $func = c('DB_ARRAY_RECURSIVE') === true ? 'array_walk_recursive' : 'array_walk';
        if(c('DB_CHECK_FIELDS')){
            $param = $this->checkFields($param,false);
            $func = 'array_walk';
        }
        $this->mixedGroup = false;
        $addition = array('delimiter'=>',','attribute'=>&$return,'isValue'=>true);
        $this->neaten($func,$param,$addition);
        $this->mixedGroup = 'GROUP BY'.substr($return,0,-1);
        return $this;
    }
    /**
     * order by选项
     * @param <mixed> $param
     * @access public
     * @return <self::object> $this
     */
    public function orderFields($param = ''){
        if(empty($param)){
            $auto = $this->getFileCacheFields();
            $pri = isset($auto['pri']) ? $auto['pri'] : array_shift($auto);
            $this->mixedOrder = sprintf('ORDER BY`%s`DESC',$pri);
            return $this;
        }
        if(!is_array($param)){
            $this->mixedOrder = sprintf('ORDER BY %s',$param);
            return $this;
        }
        $func = c('DB_ARRAY_RECURSIVE') === true ? 'array_walk_recursive' : 'array_walk';
        if(c('DB_CHECK_FIELDS')){
            $param = $this->checkFields($param);
            $func = 'array_walk';
        }
        $this->mixedOrder = false;
        $addition = array('delimiter'=>',','attribute'=>&$return,'isOrder'=>true);
        $this->neaten($func,$param,$addition);
        $this->mixedOrder = 'ORDER BY'.substr($return,0,-1);
        return $this;
    }
    /**
     * limit选项
     * @param <mixed> $param
     * @access public
     * @return <self::object> $this
     */
    public function limitFields($param = ''){
        if(empty($param)){
            $this->mixedLimit = false;
            return $this;
        }
        if(!is_array($param)){
            $this->mixedLimit = sprintf('LIMIT %s',$param);
            return $this;
        }
        if(count($param) ===1){
            $this->mixedLimit = 'LIMIT '.array_shift($param);
            return $this;
        }
        $this->mixedLimit = sprintf('LIMIT %d,%d',array_shift($param),array_shift($param));
        return $this;
    }
    /**
     *  select中的语句
     * @param mixed $param
     * @access public
     * @return <self::object> $this
     */
    public function selectFields($param = ''){
        if(empty($param)){
            $this->mixedSelect = '`'.implode($this->getFileCacheFields(),'`,`').'`';
            return $this;
        }
        if(!is_array($param)){
            $this->mixedSelect = ' '.$param.' ';
            return $this;
        }
        $func = c('DB_ARRAY_RECURSIVE') === true ? 'array_walk_recursive' : 'array_walk';
        if(c('DB_CHECK_FIELDS')){
            $param = $this->checkFields($param,false);
            $func = 'array_walk';
        }
        $this->mixedSelect = false;
        $addition = array('delimiter'=>',','attribute'=>&$return,'isValue'=>true);
        $this->neaten($func,$param,$addition);
        $this->mixedSelect = substr($return,0,-1);
        return $this;
    }
    /**
     * having操作
     * @param <string> $string
     * @access public
     * @return <self::object> $this
     */
    public function havingFields($string = ''){
        if(empty($string) || !is_string($string)){
            $this->mixedHaving = false;
            return $this;
        }
        $this->mixedHaving ='HAVING '.$string;
        return $this;
    }
    /**
     * insert数据操作
     * @param <mixed> $param
     * @access public
     * @return <self::object> $this
     */
    public function insertFields($param = ''){
        return $this->updateFields($param);
    }
    /**
     * 字段写入数据表的映射
     * @param <mixed> $value
     * @param <string> $key
     * @param <var> $return
     * @return void
     */
    private function mapCallBack($value,$key,&$return){
	if(c('MAGIC_QUOTES_GPC_')) $value = addslashes($value);
        if(isset($this->map[$key])) return $return[$this->map[$key]] = $value;
        $return[$key] = $value;
    }
    /**
     * 整理数据
     * @param <string> $func
     * @param <array> $data
     * @param <array> $param
     * @example $param = array('delimiter'=>'','attribute'=>&)
     * @access private
     * @return void
     */
    private function neaten($func,$param,$addition = array()){
        $func($param,array('self','callBack'),$addition);
    }
    /**
     * 整理数据的回调函数
     * @param <string> $value
     * @param <string> $key
     * @param <array> $param
     * @access private
     * @return void
     */
    private function callBack($value,$key,$param){
	if(c('MAGIC_QUOTES_GPC_')) $value = addslashes($value);
        if(isset($param['isValue']) && $param['isValue'] === true) return $param['attribute'] .= "`{$value}`{$param['delimiter']}";
        if(isset($param['isOrder']) && $param['isOrder'] === true) return $param['attribute'] .= "`{$key}`{$value}{$param['delimiter']}";
        if(is_string($value)) $value = "'{$value}'";
        $param['attribute'] .= "`{$key}`={$value} {$param['delimiter']}";
    }
    
    /**
     * 单表查询
     * @param <array> $param
     * @example $param = array('tableName'=>string,'fields'=>'','condition'=>'','having'=>'','order'=>'','limit'=>'');
     * @access public
     * @return <mixed>
     */
    public function select($param = array()){
        if(!is_array($param)) $param = '';
        if(isset($param['tableName'])) $this->tableName($param['tableName']);
        if(!$this->mixedSelect) $this->selectFields(isset($param['fields']) ? $param['fields'] : null);
        if(isset($param['condition'])) $this->whereFields($param['condition']);
        if(isset($param['group'])) $this->groupFields($param['group']);
        if(isset($param['having'])) $this->havingFields($param['having']);
        if(!$this->mixedOrder) $this->orderFields(isset($param['order']) ? $param['order'] : null);
        if(isset($param['limit'])) $this->limitFields($param['limit']);
        return $this->refer();
    }
    /**
     * 单表写入数据
     * @param <array> $param
     * @access public
     * @return <int>
     */
    public function insert($param = array()){
        if(!empty($param)) $this->insertFields($param);
        return $this->resave();
    }
    /**
     * 单表更新操作
     * @param <array> $param
     * @example $param = array('update'=>'','condition'=>'');
     * @access public
     * @return
     */
    public function update($param = array()){
        if(isset($param['tableName'])) $this->tableName($param['tableName']);
        if(isset($param['update'])) $this->updateFields($param['update']);
        if(isset($param['condition'])) $this->whereFields($param['condition']);
        if(isset($param['limit'])) $this->limitFields($param['limit']);
        return $this->renew();
    }
    /**
     * 删除操作
     * @param <array> $param
     * @example $param = array()
     * @access public
     * @return
     */
    public function delete($param = array()){
        if(isset($param['tableName'])) $this->tableName($param['tableName']);
        if(isset($param['condition'])) $this->whereFields($param['condition']);
        if(isset($param['limit'])) $this->limitFields($param['limit']);
        return $this->remove();

    }
    /**
     * 重载remove方法,清空标志位,做异常抛出处理
     * @param void
     * @access protected
     * @return <array>
     */
    protected function remove($tableName = '',$where = '',$limit = ''){
        $result = parent::remove($this->tableName,$this->mixedWhere,$this->mixedLimit);
        $this->emptyAttribute();
        $this->isError($result);
        return $result;
    }
    /**
     * 重载renew方法,清空标志位,做异常抛出处理
     * @param void
     * @access protected
     * @return <array>
     */
    protected function renew($tableName = '',$update = '',$where = '',$limit = ''){
        $result = parent::renew($this->tableName, $this->mixedUpdate, $this->mixedWhere,$this->mixedLimit);
        $this->emptyAttribute();
        $this->isError($result);
        return $result;
    }
    /**
     * 重载refer方法,清空标志位,做异常抛出处理
     * @param void
     * @access protected
     * @return <array>
     */
    protected function refer($select = '',$tableName = '',$where = '',$group = '',$having = '',$order = '',$limit = ''){
        $result = parent::refer($this->mixedSelect,$this->tableName,$this->mixedWhere,$this->mixedGroup,$this->mixedHaving,$this->mixedOrder,$this->mixedLimit);
        $this->emptyAttribute();
        $this->isError($result);
        return $result;
    }
    /**
     * 重载save方法,清空标志位,做异常抛出处理
     * @param void
     * @access protected
     * @return <array>
     */
    protected function resave($tableName = '',$value = ''){
        $result = parent::resave($this->tableName,$this->mixedUpdate);
        $this->emptyAttribute();
        $this->isError($result);
        return $result;
    }
    /**
     * 清空属性,否则会发生无法预知的错误
     * @param void
     * @access public
     * @return void
     */
    public function emptyAttribute(){
         $this->mixedWhere = false;
         $this->mixedSelect = false;
         $this->mixedUpdate = false;
         $this->mixedGroup = false;
         $this->mixedOrder = false;
         $this->mixedHaving = false;
         $this->mixedLimit = false;
    }
    /**
     * 把多表联合查询换成单表查询并混合数据
     * @param <array> $data 手动添加主表信息(二维数组)
     * @param <boolen> $auto 是否开启数据检测默认为不开启,适用于列表(相当于左关联),如果开启,适用于取值(相当于自然关联)
     * @access public
     * @return <array>
     */
    public function relationSelect( $auto = false){
        return $this->linkOperation( $auto);
    }
    /**
     * 写入数据库操作,多表写入
     * @param void
     * @access public
     * @return <mixed>
     */
    public function relationInsert(){
        return $this->linkInsert();
    }
    /**
     * 更新数据库操作,多表更新
     * @param void
     * @access public
     * @return <mixed>
     */
    public function relationUpdate(){
        return $this->linkUpdate();
    }
    /**
     * 更新数据库操作,多表更新
     * @param void
     * @abstract private
     * @return <mixed>
     */
    private function linkUpdate(){
        if(empty($this->link) || !is_array($this->link)) throwException('请填写完整关联关系');
        $link = $this->link;
        list($tableName,$value) = each($link);
        reset($link);//恢复指针
        $condition = $value[$tableName.'Condition']['condition'] ? $value[$tableName.'Condition']['condition'] : array();
        if(c('DB_ARRAY_RECURSIVE') === true){
            array_walk_recursive($condition,array('self','setUpdate'),$return);//如果开启参数递归
            $condition = $return;
        }
        return $this->insertOperation($link,$condition,'condition');
    }
    /**
     * 写入数据库操作进行多表写入
     * @param void
     * @access private
     * @return <mixed>
     */
    private function linkInsert(){
        if(empty($this->link) || !is_array($this->link)) throwException('请填写完整关联关系');
        $link = $this->link;
        list($tableName,$value) = each($link);
        $data = array_shift($link);
        $update = $value[$tableName.'Condition']['insert'] ? $value[$tableName.'Condition']['insert'] : array();
        $priValue = $this->tableName($tableName)->insertFields($update)->insert();
        if(!is_array($link)) return $priValue;
        if(c('DB_ARRAY_RECURSIVE') === true){
            array_walk_recursive($update,array('self','setUpdate'),$return);//如果开启参数递归
            $update = $return;
        }
        if(isset($this->cacheFields[$tableName]['pri'])) $update[$this->cacheFields[$tableName]['pri']] = $priValue;//取得主表pri值
        return $this->insertOperation($link,$update);
    }
    /**
     * 数据混合插入操作
     * @param <array> $link
     * @param <array> $update
     * @access private
     * @return <mixed>
     */
    private function insertOperation($link,$update,$status = 'insert'){
        foreach($link as $k=>$v) {
            if(empty($k) || !is_array($v)) continue;
            $sign = is_array($v[$k.'Condition'][$status]) ? array() : '';
            if(is_array($v['condition'])){
                foreach($v['condition'] as $ck=>$cv) {//取值
                    if(isset($update[$cv])) {
                        if(is_array($sign)) {
                            $sign[$ck] = $update[$cv];
                        }else {
                            if(is_string($update[$cv])) $update[$cv] = "'{$update[$cv]}'";
                            $sign .= "{$ck}={$update[$cv]},";
                        }
                    }
                }
        }
            if(is_array($sign)) {
                $insert = $sign + $v[$k.'Condition'][$status];
            }else {
                $insert = $v[$k.'Condition'][$status] ? $sign.$v[$k.'Condition'][$status] : rtrim($sign,',');
            }
            if($status == 'insert') {
                $result = $this->setFunc($k,$insert,$status);
            }else {
                $v[$k.'Condition'][$status] = $insert;
                $result = $this->setFunc($k,'',$status,$v[$k.'Condition']);
            }
            if(is_string($result)){
                $error[] = $result;
            }

        }
        if($error) return $error;
        return true;
    }
    /**
     * 对写入,更新字段做处理
     * @param <string> $tableName
     * @param <mixed> $data
     * @param <string> $status
     * @param <array> $condi
     * @return <mixed>
     */
    private function setFunc($tableName,$data,$status,$condi = array()){
        if($status == 'insert') return $this->tableName($tableName)->insertFields($data)->insert();
        $this->tableName($tableName);//防止直接写入tableName
        extract($condi);
        return $this->updateFields($update)->whereFields($condition)->update();
    }
    /**
     * 递归循环操作
     * @param <mixed> $value
     * @param <mixed> $key
     * @param <array> $return
     * @access private
     * @return void
     */
    private function setUpdate($value,$key,$return){
        $return[$key] = $value;
    }

    /**
     * 把多表联合查询换成单表查询并混合数据
     * by jl 将手动写入的数据拿掉，不支持$data,$auto手动写入@todo
     * @param <array> $data 手动添加主表信息(二维数组)
     * @param <boolen> $auto 是否开启数据检测默认为不开启,适用于列表(相当于左关联),如果开启,适用于取值(相当于自然关联)
     * @access private
     * @return <array> $data
     */
    private function linkOperation($auto){
        if(empty($this->link) || !is_array($this->link)) throwException('请填写完整关联关系');
        $link = $this->link;
        $tmp = array_shift($link);
        $mainTable = array_shift($tmp);
        if(! isset($mainTable['tableName'])) throwException('主表名称未填写！');
        $this->tmpName = $mainTable['tableName'];
        $this->return = array();//获取数据
        array_walk($this->link,array('self','mixedDataCallBack'));
        $return = $this->return;
        $data = array_shift($return);  //(empty($data) || !is_array($data)) ? array_shift($return) : $data;
        if(!is_array($data)) throwException('请填写完整关联关系');
        if(! $data) return array();

        foreach($data as $dataKey=>$dataValue){//循环主表
            foreach($return as $key=>$value){
                foreach($this->link[$key]['condition'] as $ck=>$cv) {//循环条件
                    foreach($value as $k=>$v) {//循环附表字段
                        if($v[$ck] == $dataValue[$cv]){
                            $data[$dataKey] = $data[$dataKey] + $v;
                        }
                    }

                }
            }
            if($auto === true){
                if($data[$dataKey] == $dataValue){
                    unset($data[$dataKey]);
                }
            }
        }
        return $data;
    }

    /**
     * 混合数据,从规则中取值
     * @param <mixed> $value value值
     * @param <string> $key key值
     * @param <array> $return 返回数据
     * @param $mainTable 主表
     * @access private
     * @return void
     */
    private function mixedDataCallBack($value,$key){
        if(empty($key)) return;
	if(isset($this->return[$this->tmpName]) && empty($this->return[$this->tmpName])) return array();
        $tableName = $key;
        if(empty($tableName) || !is_string($tableName)) throwException('请填写当前操作的数据表名');

        $condition = isset($this->link[$tableName][$tableName.'Condition']) && is_array($this->link[$tableName][$tableName.'Condition']) ? $this->link[$tableName][$tableName.'Condition'] : array();
        
        if($tableName != $this->tmpName && isset($this->link[$tableName]['condition']) && is_array($this->link[$tableName]['condition'])){
            $tmp = array();
            if(isset($this->return[$this->tmpName]) && is_array($this->return[$this->tmpName]) && $this->return[$this->tmpName]){
                foreach($this->link[$tableName]['condition'] as $k=>$v){
                    foreach($this->return[$this->tmpName] as $value){//循环主表
                            $tmp[$k]["'".$value[$v]."'"] = '';
                    }

                    $key = array_keys($tmp[$k]);
                    $tmp[$k] = "$k in(".implode(',',$key).")";
                }
            }
            if(isset($condition['condition']) && $tmp){
                if(is_array($condition['condition'])){
                    $this->mixedWhere = '';
                    $this->whereFields($condition['condition'],false );
                    $condition['condition'] = substr($this->mixedWhere,5);
                }
                $condition['condition'] .= ' AND '.implode(' AND ',$tmp);
            }else{
                $condition['condition'] = implode(' AND ',$tmp);
            }
            //p($condition['condition']);
        }
        $tableContent = $this->getData($condition,$tableName);
        $r = array();
        if(isset($this->link[$tableName]['map'])){
            array_walk($tableContent,array('self','addMapCallBack'),array('map'=>$this->link[$tableName]['map'],'return'=>&$r));
            $tableContent = $r;
        }
        $this->return[$tableName] = $tableContent;
    }

    /**
     * @param <array> $condi 规则中书写的条件
     * @param $tableName 数据表名
     * @access private
     * @return <void>
     */
    private function getData($con,$tableName){
        if(!is_array($con)) return;
        $this->tableName($tableName);//防止直接写入tableName
        extract($con);
        return  $this->selectFields(isset($fields) ? $fields : '' )->whereFields(isset($condition) ? $condition : '' )->
                groupFields(isset($group) ? $group : '' )->havingFields(isset($having) ? $having : '' )->
                orderFields(isset($order) ? $order : '' )->limitFields(isset($limit) ? $limit : '' )->select();
    }
    /**
     * 添加映射
     * @param <array> $value
     * @param <string> $k
     * @param <string> $param
     * @access private
     * @return void
     */
    private function addMapCallBack($value,$k,$param){
        if(!is_array($value)) return;
        foreach($value as $key=>$v){
            if(isset($param['map'][$key])){
                unset($value[$key]);
                $value[$param['map'][$key]] = $v;
            }
        }
        $param['return'][$k] = $value;
    }
}
?>

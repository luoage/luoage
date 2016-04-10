<?php
/**
 * mysql处理基类
 * @package luoage
 * @author luoage@msn.cn
 * @version $$version
 */
if(!defined('LUOAGE')) exit;
class Mysql {
    /**
     * mysql连接标志
     * @var <resource> $db
	 * @static
     * @access public
     */
    public static $db = '';
    /**
     * 数据表名称
     * @var <string> $tableName
     * @access protected
     */
    protected $tableName = '';
    /**
     * sql语句
     * @var <string> $sql
     * @static
     * @access public
     */
    public static $sql = '';

    /**
     * 连接mysql,并将值赋值给self::$db
     * @param string|int $dbApp 选择数据库
     * @access public
     * @return <string> 失败返回错误码及其信息,成功不返回
     */
    public function connect($dbApp = ''){
        $c = c();
        $dbApp && isset($c[$dbApp]) && c($c[$dbApp]);
        if(self::$db && !$dbApp) return;
        $connectStart = microtime(true);
//         ob_start();
        self::$db = @mysql_connect(c('DB_HOST').':'.c('DB_PORT'),c('DB_USER'),c('DB_PWD'));
//         ob_end_clean();
        $connectEnd = microtime(true);
        self::$sql['linkTime'] = round($connectEnd - $connectStart,4).'s';
        if(empty(self::$db)) return mysql_errno().':'.mysql_error();
    }
    /**
     * 只为了得到mysql版本
     * @param void
     * @access public
     * @return string
     */
    public static function mysqlVersion(){
        self::connect();
        return mysql_get_server_info(self::$db);
    }
    /**
     * 选择数据库
     * @param void
     * @access public
     * @return <string> 失败返回错误码及其信息,成功不返回
     */
    public function selectDb(){
        $database = mysql_select_db(c('DB_NAME'),self::$db);
        if(empty($database)) return mysql_errno().':'.mysql_error();
    }
    /**
     * 字符集设置
     * @param void
     * @access public
     * @return <string> 失败返回错误码及其信息,成功不返回
     */
    public function charset(){
        $charset = mysql_query(sprintf('SET NAMES %s',c('DB_CHARSET')));
        if(empty($charset)) return mysql_errno().':'.mysql_error();
    }
    /**
     * 返回表名并将值赋于$this->tableName
     * @param <string> $tableName
     * @access public
     * @return void
     */
    public function tableName($tableName){
        $this->tableName = c('DB_PREFIX').$tableName.c('DB_SUFIX');
    }

    /**
     * 获取表字段及其属性
     * @param <string> $tableName
     * @access public
     * @return mixed
     */
    public function fields($tableName = ''){
        $result = $this->sql(sprintf('DESC `%s`',$tableName ? $tableName : $this->tableName));
        if(is_string($result)) return $result;
        $fields = array();
        foreach($result as $key=>$row) {
            if ($row['Key'] == 'PRI') {
                if (!isset($fields['pri']))
                    $fields['pri'] = $row['Field'];
                else
                    $fields['pri' . $key] = $row['Field'];
                continue;
            }
            $fields[] = $row['Field'];
        }
        return $fields;
    }
    /**
     * 单表查询
     * @param <string> $select 表字段
     * @param <string> $tableName 表名
     * @param <string> $where 查询条件
     * @param <string> $group 分组
     * @param <string> $order 排序
     * @param <string> $limit 限制查询数量
     * @access protected
     * @return <array> array | string
     */
    protected function refer($select = '',$tableName = '',$where = '',$group = '',$having = '',$order = '',$limit = ''){
        return $this->sql(sprintf('SELECT%sFROM`%s`%s %s %s %s %s',$select,$tableName,$where,$group,$having,$order,$limit));
    }
    /**
     * 单表写入操作
     * @param <string> $tableName 表名
     * @param <string> $value 写入的字段和值
     * @access protected
     * @return <mixed> int | string
     */
    protected function resave($tableName = '',$value = ''){
        return $this->sql(sprintf('INSERT INTO`%s`%s',$tableName,$value));
        
    }
    /**
     * 单表更新操作
     * @param <string> $tableName 表名
     * @param <string> $update 更新字段加值
     * @param <string> $where 更新条件
     * @return <mixed> int | string
     */
    protected function renew($tableName = '',$update = '',$where = '',$limit = ''){
        return $this->sql(sprintf('UPDATE`%s`%s %s %s',$tableName,$update,$where,$limit));
    }
    /**
     * 单表删除操作
     * @param <string> $tableName 表名
     * @param <string> $where 删除条件
     * @return <mixed> int | string
     */
    protected function remove($tableName = '',$where = '',$limit = ''){
        return $this->sql(sprintf('DELETE FROM`%s`%s %s ',$tableName,$where,$limit));
    }
    /**
     * 通过sql语句查询数据,记录运行时间
     * @param <string> $sql sql语句
     * @access public
     * @return mixed
     */
    public function query($sql){
        return self::sql($sql);
    }
    
    public function query_update($sql){
    	return mysql_query($sql);
    }
    /**
     * 通过sql语句查询数据并返回相应的数据集
     * @param <string> $sql sql语句
     * @access private
     * @return mixed
     */
    protected function sql($sql){
        //echo $sql = trim($sql),'<br />';
        $sql = trim($sql);
        $timeStart = microtime(true);
        $result = mysql_query($sql,self::$db);
        $timeEnd = microtime(true);
        self::$sql[] = round($timeEnd-$timeStart,4).'s: '.$sql;
        if(empty($result)) return mysql_errno().':'.mysql_error().'<br />[SQL]:<br />'.$sql;
        $pattern = array(
                'select' =>'/^select/i',
                'insert' => '/^insert/i',
                'update' =>'/^update/i',
                'delete' =>'/^delete/i',
				'replace' =>'/^replace/i',
                'desc'=>'/^desc/i',
                'create'=>'/^create/i',
                'show'=>'/^show/i'
        );
		$keys = '';
        foreach($pattern as $key=>$value){
			if(preg_match($value,$sql)===1){
				$keys = $key;
				break;
			}
		}
        $return = false;
        switch($keys){
            case 'select':
            case 'desc':
            case 'show':
                $return = array();
                while($row = mysql_fetch_assoc($result)) {
                    $return[] = $row;
                }
                break;
            case 'insert':
                $return = mysql_insert_id();
                break;
            case 'update':
            case 'delete':
			case 'replace':
            case 'create':
                $return = mysql_affected_rows();
                break;
            default:
                $return = $result;
        }
        return $return;
    }
}
?>

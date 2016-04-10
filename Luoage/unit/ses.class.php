<?php
/**
 * 重写session
 * @package luoage
 * @author luoage@msncn
 * @version $$version
 */
if(!defined('LUOAGE')) exit;
class ses extends model{
    public function __construct(){
        parent::__construct();
        session_set_save_handler(
            array(&$this,"open"),
            array(&$this,"close"),
            array(&$this,"read"),
            array(&$this,"write"),
            array(&$this,"destroy"),
            array(&$this,"gc")
        );
        session_start();
    }

    public static function open(){
        return true;
    }
    public static function close(){
        return true;
    }
    /**
     * 根据session键，读取值
     * @access public
     * @static
     * @param void
     * @return array
     */
    public static function read(){
		$id = self::setCookies();
		if(! $id) return false;
		$val = self::query('select ses_value from '.self::sTableName('session')." where ses_key='{$id}' and sign='".c('SESSION_SIGN')."'");
		if(! is_array($val)) throwException($val);
		if($val) return $val[0]['ses_value'];else return false;
    }
    /**
     * 写入session值
     * @access public
     * @static
     * $ids null
     * @sessData session方式的字符串
     */
    public static function write($ids, $sessData){
        $id = self::setCookies();
		if(! $id) return false;
        return self::query('replace into '.self::sTableName('session')." set ses_key='{$id}', ses_value='{$sessData}',time=".time().",sign='".c('SESSION_SIGN')."'");
    }
    /**
     * 删除超时session
     * @access public
     * @static
     * @param void
     * @return array
     */
    public static function destroy(){
		return self::query('delete from '.self::sTableName('session')." where time<".(time()-c('SESSION_TIMEOUT')).' and sign=\''.c('SESSION_SIGN')."'");
    }
    public static function gc(){
        return true;
    }
    /**
     * 生成luoageId,sessionKey
     * @access public
     * @static
     * @param void
     * @return string $id
     */
	public static function setCookies(){
		
		if(isset($_COOKIE['loginTime'])){
			$loginTime = $_COOKIE['loginTime'];
		}else{
			$loginTime = time();
			@setcookie('loginTime',$loginTime,time()+c('COOKIE_TIMEOUT'),c('COOKIE_PATH'),c('COOKIE_DOMAIN'),c('COOKIE_SECURE'));
		}

		$id = '';
		$ip = isset($_SERVER['REMOTE_ADDR']) ? str_replace('0',c('SESSION_DEFAULT_REPLACE'),$_SERVER['REMOTE_ADDR']) : c('SESSION_DEFAULT_IP');
		$ua = isset($_SERVER['HTTP_USER_AGENT']) ? str_replace('0',c('SESSION_DEFAULT_REPLACE'),$_SERVER['HTTP_USER_AGENT']) : c('SESSION_DEFAULT_AGE');

		$sessionKey = md5(str_replace('0',c('SESSION_DEFAULT_REPLACE'),$ip.$ua.$loginTime));
		
		if(! isset($_COOKIE['luoageId'])){
			$id = md5(uniqid(mt_rand()).$ip);

			@setcookie('luoageId',$id,time()+c('COOKIE_TIMEOUT'),c('COOKIE_PATH'),c('COOKIE_DOMAIN'),c('COOKIE_SECURE'));
			@setcookie('sessionKey',$sessionKey,time()+c('COOKIE_TIMEOUT'),c('COOKIE_PATH'),c('COOKIE_DOMAIN'),c('COOKIE_SECURE'));
		}else{
			if(isset($_COOKIE['sessionKey']) && $sessionKey === $_COOKIE['sessionKey']){
				$id = $_COOKIE['luoageId'];
			}else{
				setcookie('luoageId','',time()-3600,c('COOKIE_PATH'),c('COOKIE_DOMAIN'),c('COOKIE_SECURE'));
				setcookie('sessionKey','',time()-3600,c('COOKIE_PATH'),c('COOKIE_DOMAIN'),c('COOKIE_SECURE'));
				setcookie('loginTime','',time()-3600,c('COOKIE_PATH'),c('COOKIE_DOMAIN'),c('COOKIE_SECURE'));
			}
		}
		return $id;
	}
    /**
     * 清除所有session值
     * @access public
     * @static
     * @param void
     * @return mixed
     */
	public static function clearSession(){
		$id = self::setCookies();
		return self::query('delete from '.self::sTableName('session')." where ses_key='{$id}' and sign='".c('SESSION_SIGN')."'");
	}
    public function __destruct(){
		self::destroy();
    }
}
?>

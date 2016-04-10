<?php
/**
 * @abstract 异常抛出
 *
 * @author luoage@msn.cn
 *
 * @version $$version
 */
if(!defined('LUOAGE')) exit;
class TinyException extends Exception{
		/**
		 *
		 */
		public function e(){
				return $this->getMessage();
		}
}
?>

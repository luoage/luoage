<?php
/**
 * 渲染组件
 * @package luoage
 * @author luoage@msn.cn
 */
class publicComponent extends component{
	
	/**
	 * debug
	 */
	 public function debug(){
		 $this->assign('layoutDebug',entry::debug());
	 }
}
?>

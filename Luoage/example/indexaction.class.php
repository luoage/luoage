<?php
//示例文件
class IndexAction extends Action {
    public function  __construct() {
        $this->assign('elementOne',$this->element());
        //基类中不存在构造函数,这里不需要继承
    }
    public function index() {
        //exit;
        $this->assign('title','这是一个神奇的网站');
        $this->render();
    }
}
?>
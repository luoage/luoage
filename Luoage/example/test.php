<?php
//示例文件
class IndexAction extends Action {
    public function  __construct(){
        //基类中不存在构造函数,这里不需要继承
    }
    public function index() {
        $mysql = new Model();
        //exit;
        $param = array(
            //'fields'=>'id,username',
            //'condition'=>array('username'=>1),
            //'order'=>array('id'=>'asc'),
            'table'=>'op_deal'
        );
//        $return = $mysql->tableName('user')->selectFields(array('id'))->select();
//        $return = $mysql->tableName('op_deal')->selectFields(array('id'))->select();
        //$return = $mysql->whereFields(array('id'=>1,array('username'=>'1000')))->selectFields()->refer();
        //$return = $mysql->selectFields()->limitFields()->refer();
        //$return = $mysql->select('select * from user
        //$return = $mysql->select();
        p($return);
        //$return = $mysql->whereFields(array('id'=>1,array('username'=>'1')));
        //p($return);
        //p($mysql->mixedWhere);updateFields(array('id'=>1,'username'=>1))->
        //p($mysql->mixedUpdate);
//        //使用元素,元素中调用组件User,等方法index/indexx
//        $useComponent = array('User'=>array('index','indexx'));
//        $useElements = $this->element($useComponent);
//        $this->assign('elementOne',$useElements);
//        $this->assign('title','这是一个神奇的网站');
         $this->render();
    }
}
?>

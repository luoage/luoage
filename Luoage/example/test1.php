<?php
//示例文件
class IndexAction extends Action {
    public function  __construct() {
        //基类中不存在构造函数,这里不需要继承
    }
    public function index(){
        $m = new Model();
        $m->link = array(
                'user'=>array(//主表
                        'userCondition'=>array(
                                'update'=>array(
                                        'email'=>'luoage@msn.com12',
                                        'username'=>'金1岭2',
                                ),
                            'fields'=>array('email','username','id'),
                            'condition'=>array(
                                'id'=>1,
                            ),
                        ),
                ),
                'exmail'=>array(
                        'condition'=>array('user_id'=>'id'),
                        'map'=>array('username'=>'xlllllll'),
                        'exmailCondition'=>array(
                            'update'=>array('username'=>'xm1l2'),
                            'fields'=>array('user_id','username'),
                        )
                ),
              'op_deal'=>array(
                        'condition'=>array('deal_title'=>'id'),
                        'op_dealCondition'=>array(
                            'update'=>array('deal_value'=>'1213'),
                            'fields'=>'deal_value,deal_title',
                        )
                ),
        );
        $linkResult = $m->relationUpdate();
        p($linkResult);
        $this->render();
    }
}
?>

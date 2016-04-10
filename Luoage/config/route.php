<?php
/**
 * 路由图
 * @package luoage
 * @author luoage@msn.cn
 * @varsion $$version
 * @return array
 */
 return array(
        //class文件
	'entry'=>LUOAGE.'/class/entry.class.php',
	'delimiter'=>LUOAGE.'/class/delimiter.class.php',
	'tinyexception'=>LUOAGE.'/class/tinyexception.class.php',
        //unit文件
	'ses'=>LUOAGE.'/unit/ses.class.php',
	'plug'=>LUOAGE.'/unit/plug.class.php',
	'image'=>LUOAGE.'/unit/image.class.php',
	'static_template'=>LUOAGE.'/unit/static_template.class.php',
	'verify'=>LUOAGE.'/unit/verify.class.php',

        //operation文件
	'tiny'=>LUOAGE.'/operation/tiny.class.php',
        //model
	'model'=>LUOAGE.'/operation/model/model.class.php',
	'mysql'=>LUOAGE.'/operation/model/mysql.class.php',
        'redis'=>LUOAGE.'/operation/model/redis.class.php',
        //action
	'action'=>LUOAGE.'/operation/action/action.class.php',
        //component
	'component'=>LUOAGE.'/operation/component/component.class.php',
        //template
	'template'=>LUOAGE.'/operation/template/template.class.php',

        //appAction应用控制器类
	'appAction'=>LUOAGE_APP.'/operation/action',
        //appComponent应用组件类
	'appComponent'=>LUOAGE_APP.'/operation/action/component',
        //appModel 应用model类
	'appModel'=>LUOAGE_APP.'/operation/model',
        //appCustom 应用custom类
	'appCustom'=>LUOAGE_APP.'/custom',
 );
?>
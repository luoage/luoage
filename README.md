## PHP框架

这是一个非常老的PHP框架了，写于2012年，然后就不再维护了，使用的PHP版本较低，可用于学习，不可用于生产环境。


##一，luoage框架基本功能及介绍：

1. 基本的数据库连接配置，数据获取，增删改查和sql语句执行方法。

2. 模板引用，没有配备模板引擎(原因略)，增加了简单的replace标签，和变量输出功能，外链文件收集到头部，增加布局文件和元素(渲染元素的是组件)。

3. 动态页面/静态页面缓存(可控制时间)。

4. session重写机制。目前只支持重写到mysql(mem有缓存溢出，redis是nosql数据库，目前并没有尝试，并且nosql还不算成熟)。

5. URL路由

6. 文件路由。

7. action类和组件类，包括了分配变量到viewer中的方法和渲染页面的方法，是网站逻辑功能的主体部分。

8. Exception 错误收集。

9. 自动创建应用目录。自动创建的目录包括一下部分:*(重要)，$(可删除重新生成)
	
	'operation'
	'operation/model' 		--数据库连接类，主要放置公共数据执行方法,目前使用类model
	'operation/action',		--主要逻辑部份 *
	'operation/action/component',	--组件类类，用于渲染元素模板或者公共使用方法
	'custom',			--自定义类，主要存放公用功能，比如图片上传类，验证码等。
	'template',
	'template/index',		
	'template/layout',		--模板布局文件
	'template/elements',		--模板元素文件（比如，一个排行榜在多个页面调用，那么这个元素在多个页面同时载入即可，一般与组件类共同使用，单独使用时是静态模板）
	'config',			--配置文件
	'cache',			--缓存文件
	'cache/error_log',		--错误日志文件，通过配置文件的控制，可以将错误日志，显示或者存入文件，主要存放框架手动抛出的错误
	'cache/base_log',		--基础日志，存放基础日志，比如某个重要数据的更新日志
	'cache/shell',			--shelL脚本
	'cache/temp',
	'cache/temp/php',		--php缓存目录 $
	'cache/temp/static',		--html静态缓存目录 $
	'cache/database',		--数据库结构缓存目录(若数据结构更改，databases内的文件必须删除重新自动生成，它的存在是为了检测数据操作时字段的一致性) $

10. 框架目录介绍：

	class/delimiter.class.php	--URL传值处理
	class/entry.class.php		--操作入口文件
	class/tinyexception.class.php	--简单的异常抛出继承
	config/				--默认配置文件及配置项限定
	example/			--用例和初始化
	operation/tiny.class.php	--组件和逻辑类通用方法，主要处于被继承方向
	operation/template/		--简单的模板替换功能
	operation/action/
	operation/model/
	public/				--公共方法(暂未完善)
	unit/				--公共类/插件类
		
##二，框架使用细节
1. 规则
 - 文件名必须为 xxx.class.php，其中文件内容为 xxx的类，使用时，直接new xxx便可在任何地方调用。
 - URL路由分为三种方式，具体见框架config.php文件。
 - 逻辑部分存放在action目录下，使用统一的入口访问这些逻辑程序，访问方法为根据URL路由的不容而不同，但是一次为 class类名，方法，变量键值，变量内容，变量键值，变量内容..
 - 模板后缀名为.html在配置文件中可更改。

2.  数据库操作方法：
 - 使用model类
	>有两种方式使用model类，第一种，直接new model(),如果是多数据库操作，括号里写入该数据库名称，默认为config.php中的数据库连接。
	>第二种，在model目录下继承model类，在继承时，必须继承model的构造函数 __construct,即，parent::__construct($param,..);
	>第三种 m(),函数有参数可跨数据库,具体看代码,不加参数默认使用databases.php默认配置数据库名,具体$model->可以由 m()->操作

 - 数据库操作包括两个部分:
	- 单表操作
		调用方法
		写入数据	insert()
		更改数据	update()
		删除数据	delete()
		查询数据	select()
		数据操作有两种方法，假设$model 为model的实例化，以查询数据为例,
		第一种，参数传递 
		$model->select(
			array(
				'tableName'=>'',//表名
				'fields'=>'',//需要查询的字段(可数组，可多维数组，具体看配置文件)，默认所有字段
				'condition'=>'',//查询条件,比如，id=1
				'group'=>'',//分组
				'having'=>'',//二次晒训
				'order'=>'',//排序，默认为主键倒序，无主键为第一个字段的倒序
				'limit'=>''//限制
			)
		);

	- 第二种，query链式（select最后，其余方法无顺序之分）

		$model->tableName($tableName)->
			selectFields(isset($fields) ? $fields : '' )->
			whereFields(isset($condition) ? $condition : '' )->
			groupFields(isset($group) ? $group : '' )->
			havingFields(isset($having) ? $having : '' )->
			orderFields(isset($order) ? $order : '' )->
			limitFields(isset($limit) ? $limit : '' )->
			select();

	- 第三种,混合操作
		$model->tableName(tableName)->select(array(...));

	- 多表联合操作。（复杂，具体看代码。）
3. 模板调用
	使用action的render方法，比如逻辑文件类为a,方法为b,在逻辑文件b方法最后加入,$this->render();默认调用应用目录的template/a/b.html，如果选择调用同目录文件c，
	可以写成$this->render('c');如果调用template下d文件目录e，可写成$this->render('d/e');

4. PHP变量在模板中使用
	$this->assign('key','value')这一步已经把value赋值给了$key,在模板中输出value可以写成<?php echo $key;?>或者<{$key}>，其中<{}>为替换标签，可在配置文件中设定
5. 模板中调用公共元素文件
	$this->element('abc');它的目的是调用文件template/elements/abc.html，元素文件必须存放在elements下。
	还可以通过组件进行动态渲染元素文件，使用方法：
	$this->element('abc',array('public'=>'abc','public'=>'bcd')),表示使用组件publiccomponent.class.php的方法abc,bcd共同渲染元素abc.html后输出至页面。
	新增缓存机制,具体看代码
6. JS前置head
	$this->addition(string|array),将JS放入head头部,默认/js/目录下,若大小写不对应linux下操作有误,需要加上具体路径,如 $this->addition('Js/...')
7. 错误调试
	配置文件选项
	'DEBUG_SHOW' => true, //页面显示错误
'DEBUG_LOG' => false, //文件记录错误
8. 功能方法

	$this->jump();//无渲染跳转
	p();//格式化输出
	
	具体方法见框架目录 config/base.php
9. 优化功能
	1,静态缓存
	2,session重写
10. 入口文件

	define('LUOAGE','../LuoAge');//定义框架目录
	define('LUOAGE_APP','../tool_app');//定义应用目录
	include(LUOAGE.'/LuoAge.php');//定义框架入口文件


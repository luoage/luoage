<?php
/**
 * 上传图片类
 * 单个头像/图片上传，支持自定义宽高
 * 如果开启时间文件夹，dirName必须存在，否则有可能写入有误。创建的只是时间文件夹
 * 图片名称为md5(microtime().rand(0,99999))
 * @package luoage
 * @example $result=new image($_FILES['upfile']);
 *	$result->dirName = '/abc';
 *	echo $result->show();//如果有误直接得到
 *	var_dump($result->newImageName);//获取上传的图片路径和名称
 *
 * @author luoage@msn.cn
 *
 */

if(!defined('LUOAGE')) exit;

class image{
	public $typeName = array('jpg','jpeg','jpe','gif','png','wbmp');//文件后缀
	public $fileName;//
	public $mimetyp =array('image/jpeg','image/png','image/gif','image/vnd.wap.wbmp');//文件mime类型
	public $subfix;
	public $size = 1024000;//1M
	public $timeDir = true;//是否启用时间文件夹,true,开启自动时间文件夹
	public $dirName;
	public $scales = false;//缩放,true, 开启自动缩放
	public $thumb;
	public $width = 128;//缩放宽度
	public $height = 152;//缩放高度
	public $newImageName;
	public $imageInfo = array();
	public $isWidth = true;//保持宽度等比缩放
	public $mime = array();

	public function __construct($fileName){
		$this->fileName = $fileName;
	}

	/**
	 * 错误类型
	 * @access public
	 * @param void
	 * @return string
	 */
	public function errors(){
		$mess = 0;
		if($this->fileName['error']){
			switch($this->fileName['error']){
			case -1:
				$mess='文件类型不符合！';
				break;
			case -2:
				$mess='文件后缀名不符！';
				break;
			case -3:
				$mess='文件超过指定大小！';
				break;
			case -4:
				$mess='不是上传类型的文件！';
				break;
			case -5:
				$mess='未移动成功！';
				break;
			case 1:
				$mess='上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值！';
				break;
			case 2:
				$mess='上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值！ ';
				break;
				
			case 3:
				$mess='文件只有部分被上传！ ';
				break;
				
			case 4:
				$mess='没有文件被上传！ ';
				break;
				
			case 6:
				$mess='找不到临时文件夹！';
				break;
				
			case 7:
				$mess='文件写入失败！';
				break;
			}
			return $mess;
		}
	}

	/**
	 * 判断文件是否符合上传类型
	 * @access public
	 * @param void
	 * @return string
	 */
	public function checkImage(){
		$this->mime = $mime = getimagesize($this->fileName['tmp_name']);
		if(!in_array($this->fileName['type'],$this->mimetyp) || !in_array($mime['mime'],$this->mimetyp)){
			$this->fileName['error']=-1;
		}
		$this->subfix=array_pop(explode('.',$this->fileName['name']));

		if(!in_array($this->subfix,$this->typeName)){
			$this->fileName['error']=-2;
		}

		if($this->fileName['size']>$this->size){
			$this->fileName['error']=-3;
		}

		if(!is_uploaded_file($this->fileName['tmp_name'])){
			$this->fileName['error']=-4;
		}
		return $this->errors();
	}

	/**
	 * 创建以时间为基准的文件夹
	 * @access public
	 * @param boolen $sign 上传模式 
	 * @return void
	 */
	public function newName($sign){//是否启用时间文件夹
		if($this->timeDir==true){
			$this->dirName=rtrim($this->dirName,'/').'/';
			$newTime=date('Ym',time());
			if(!file_exists($this->dirName.$newTime)){
				mkdir($this->dirName.$newTime,755);
			}
			if($sign === false){
				if(!file_exists($this->dirName.$newTime.'/src')){
					mkdir($this->dirName.$newTime.'/src',755);
				}
			}			
			$this->dirName=$this->dirName.$newTime.'/';
		}
	}

	/**
	 * 移动保存源图片文件，并自动生成缩略图
	 * @access public
	 * @param void
	 * @return int
	 */
	public function upImage(){
		$imgName = md5(uniqid().rand(0,9).time());
		$this->newImageName['src'] = $this->dirName.'src/'.$imgName.'.'.$this->subfix;

		//移动源文件
		$isMove = move_uploaded_file($this->fileName['tmp_name'],$this->newImageName['src']);
		if(! $isMove) return $this->fileName['error']=7;


		//对本地源文件进行处理
		$this->fileName['tmp_name'] = $this->newImageName['src'];

		$this->imageInfo['path'] = $this->dirName;
		$this->imageInfo['name'] = $imgName.'.'.$this->subfix;
	}

	/**
	 * 追加生成图片
	 * @access public
	 * @param $filePath 生成图片文件夹，与src,small同一级目录
	 * @param $width 图片宽度
	 * @param $height 图片高度
	 * @return string
	 */
	 public function normalImage($fileName = 'normal',$width = 0,$height = 0){
		//创建文件夹
		$fileName = trim($fileName,'/');
		if(! file_exists($this->dirName.$fileName)){
			mkdir($this->dirName.$fileName,755);
		}

		$this->fileName['tmp_name'] = $this->newImageName['src'];
		$this->width = $width ? intval($width) : 350;
		$this->height = $height ? intval($height) : 350;

		$this->scales();
		$thumb=$this->thumb;
		switch($this->subfix){
		case 'jpg':
		case 'jpeg':
		case 'jpe':
			$result='imagejpeg';
			break;
		case 'gif':
			$result='imagegif';
			break;
		case 'wbmp':
			$result='imagewbmp';
			break;
		case 'png':
			$result='imagepng';
			break;
		}
		$isMove = $result($thumb,$this->imageInfo['path'].$fileName.'/'.$this->imageInfo['name'].'.'.$this->subfix);
		if(! $isMove) $this->fileName['error']=7;
		return $this->errors();
	 }
	/**
	 * 只移动一种类型的文件
	 * @param void
	 * @return void
	 */
	public function moves(){//移动文件
		$imgName = md5(microtime().rand(0,99999));
		$this->newImageName=$this->dirName.$imgName.'.'.$this->subfix;

		if($this->scales!=true){
			return move_uploaded_file($this->fileName['tmp_name'],$this->newImageName);
		}else{
			$this->scales();
			$thumb=$this->thumb;
			switch($this->subfix){
			case 'jpg':
			case 'jpeg':
			case 'jpe':
				$result='imagejpeg';
				break;
			case 'gif':
				$result='imagegif';
				break;
			case 'wbmp':
				$result='imagewbmp';
				break;
			case 'png':
				$result='imagepng';
				break;
			}
			return $result($thumb,$this->newImageName);
		}
	}

	/**
	 * 缩略图片,保持图片比例，背景颜色为纯白色,不给予手动设置
	 * @access public
	 * @param void
	 * @return void
	 */
	public function scales(){
		list($width, $height) = $this->mime;//获取原图片的高度和宽度
		$this->thumb = imagecreatetruecolor($this->width, $this->height);
		$bgColor = imagecolorallocate($this->thumb,255,255,255);//白色
		imagefilledrectangle($this->thumb,0,0,$this->width,$this->height,$bgColor);
		$destY = 0;
		if($this->isWidth){//保持宽度等比缩放
			$return = $this->keepSrcWidth($width,$height);
			$destY = $return['destY'];
			//$height = $return['srcHeight'];
		}
		$source = imagecreatefromjpeg($this->fileName['tmp_name']);
		imagecopyresized($this->thumb, $source, 0,$destY, 0, 0, $this->width, $this->height, $width, $height);
	}
	/**
	 * 保持等比缩放，不让图片变形
	 * @access public
	 * @param $width
	 * @height $height
	 * @return array
	 */
	 public function keepSrcWidth($width,$height){
		 $return = array();
		 
		 $height = intval($height * $this->width/$width);
		

		 if($height > $this->height){
			 $return['destY'] = 0;
			 $this->height = $height;
		 }else{
			 $return['destY'] = ($this->height-$height)/2;
			 $this->height = $height;
		 }
		 return $return;
	 }

	/**
	 * 图片上传调用方法
	 * @access public
	 * @param $sign boolen 是否使用单模式上传,false->上传源文件和缩略图两种形式的图片
	 * @return string 如果没有错误，返回 0 
	 */
	public function show($sign = false){
		$check = $this->checkImage();
		if($check){//如果不是可上传类型
			return $check;
		}
		$this->newName($sign);
		if($sign === false) $this->upImage();else $this->moves();
		return $this->errors();
	}
}

?>

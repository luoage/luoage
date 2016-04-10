<?php
//验证码
//png格式，验证码个数$fontnum，画布宽度$width，高度$height，位置宽度调节，$wid高度调节$hei，点的个数$dian，线个数$line，圈个数$circle
class verify{
	public $fontnum = 4;//
	public $imagename;
	public $width = 100;//
	public $height = 25;//
	public $rectangle;
	public $scolor;
	public $qcolor;
	public $wid = 8;//
	public $hei = 15;//
	public $dian = 50;//
	public $line = 0;//
	public $circle = 4;//
	public $isWhite = true;//开启白色背景
	public $verify = '';

	function __construct(){
		//$this->showimage();
	}


	public function showimage(){
		$this->image();
		$this->color();
		$this->rectangle();
		$this->char();
		$this->line();
		$this->circle();
		$this->dian();
		$this->show();
	}
	public function Font(){
		$result = '';
		for($i=0;$i<$this->fontnum;$i++){
			$rand=rand(0,2);
			switch($rand){
			case 0:
				$font=rand(48,57);//数字
				break;
			case 1:
				$font=rand(65,90);//大写字母
				break;
			case 2:
				$font=rand(97,122);
				break;
			}
			$result.=sprintf('%c',$font);
		}
		return $this->verify=$result;
	}
	public function image(){
		$this->imagename=imagecreatetruecolor($this->width,$this->height);
	}
	public function rectangle(){
		$this->rectangle=imagefilledrectangle($this->imagename,0,0,$this->width,$this->height,$this->qcolor);
	}
	public function color(){
		$this->scolor=imagecolorallocate($this->imagename,rand(0,110),rand(0,110),rand(0,110));
		$this->qcolor=imagecolorallocate($this->imagename,rand(130,255),rand(130,255),rand(130,255));
		if($this->isWhite == true) $this->qcolor=imagecolorallocate($this->imagename,255,255,255);
	}
	public function char(){
		$font=$this->font();
		$strlen=strlen($font);
		for($i=0;$i<$strlen;$i++){
			$x=ceil($this->width/$strlen)*$i+$this->wid;
			imagechar($this->imagename,5,$x,rand(0,($this->height-$this->hei)),$font[$i],$this->scolor);
		}
	}
	public function dian(){
		for($i=0;$i<$this->dian;$i++){
			imagesetpixel($this->imagename,rand(0,$this->width),rand(0,$this->height),$this->scolor);
		}
	}
	public function line(){
		for($i=0;$i<$this->line;$i++){
			imageline($this->imagename,rand(0,$this->width),rand(0,$this->height),rand(0,$this->width),rand(0,$this->height),$this->scolor);
		}
	}
	public function circle(){
		for($i=0;$i<$this->circle;$i++){
			imagearc($this->imagename,rand(0,$this->width),rand(0,$this->height),rand(0,$this->width/2),rand(0,$this->height/2),rand(0,180),rand(240,360),$this->scolor);
		}
	}
	public function show(){
		header('content-type:image/png');
		imagepng($this->imagename);
	}
	function __destruct(){
		imagedestroy($this->imagename);
	}
}
?>

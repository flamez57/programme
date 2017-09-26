<?php
namespace Flamez57;
/*
** Flamez57Upload使用说明：
** ===========================================================
** 实例化文件上传器
** $mysql = new \Flamez57\Flamez57Upload(['filepath' => $filepath, '$allowtype' => array('gif', 'jpg', 'png', 'jpeg'), 'maxsize' => '1000000', 'israndname' => true]); 
** ===========================================================
*/
class Flamez57Upload 
{
	/*
	** 指定上传文件保存的路径
	*/
	private $filepath;

	/*
	** 充许上传文件的类型
	*/
	private $allowtype=array('gif', 'jpg', 'png', 'jpeg'); 

	/*
	** 允上传文件的最大长度 1M
	*/
	private $maxsize=1000000; 

	/*
	** 是否随机重命名， false不随机，使用原文件名
	*/
	private $israndname=true; 

	/*
	** 是否覆盖同名文件
	*/
	private $over_write=false;

	/*
	**	源文件名称
	*/
	private $originName; 

	/*
	** 临时文件名
	*/
	private $tmpFileName; 

	/*
	** 文件类型
	*/
	private $fileType; 

	/*
	** 文件大小
	*/
	private $fileSize; 

	/*
	** 新文件名
	*/
	private $newFileName; 

	/*
	** 错误号
	*/
	private $errorNum=0; 

	/*
	** 用来提供错误报告
	*/
	private $errorMess=""; 

	/*
	** 用于对上传文件初使化
	** $options = [
	** 		'filepath' => $filepath, 指定上传路径
	**		'$allowtype' => array('gif', 'jpg', 'png', 'jpeg'), 充许的类型
	**		'maxsize' => '1000000', 限制大小
	**		'israndname' => true 是否使用随机文件名称
	** ];
	*/
	public function __construct($options=array())
	{
		foreach($options as $key=>$val){
			$key=strtolower($key);
			//查看用户参数中数组的下标是否和成员属性名相同
			if(!in_array($key,get_class_vars(get_class($this)))){
				continue;
			}
			$this->setOption($key, $val);
		}
	}

	/*
	** 为单个成员属性设置值
	*/
	private function setOption($key, $val) 
	{
		$this->$key = $val;
	}

	/*
	** 定义错误信息
	*/
	private function getError()
	{
		$str="上传文件<font color='red'>{$this->originName}</font>时出错：";
		switch($this->errorNum){
			case 4: 
				$str .= "没有文件被上传"; 
				break;
			case 3: 
				$str .= "文件只被部分上传"; 
				break;
			case 2: 
				$str .= "上传文件超过了HTML表单中MAX_FILE_SIZE选项指定的值"; 
				break;
			case 1: 
				$str .= "上传文件超过了php.ini 中upload_max_filesize选项的值"; 
				break;
			case -1: 
				$str .= "末充许的类型"; 
				break;
			case -2: 
				$str .= "文件过大，上传文件不能超过{$this->maxSize}个字节"; 
				break;
			case -3: 
				$str .= "上传失败"; 
				break;
			case -4: 
				$str .= "建立存放上传文件目录失败，请重新指定上传目录"; 
				break;
			case -5: 
				$str .= "必须指定上传文件的路径"; 
				break;
			case -6:
				$str .= "同名文件已存在！";
				break;
			case -7:
				$str .= "文件不存在";
				break;
			default: 
				$str .= "末知错误";
		}
		return $str.'<br>';
	}

	/*
	** 用来检查文件上传路径
	*/
	private function checkFilePath()
	{
		if(empty($this->filepath)) {
			$this->setOption('errorNum', -5);
			return false;
		}
		if(!file_exists($this->filepath) || !is_writable($this->filepath)){
			if(chmod($this->filepath, 0755)){
				$this->setOption('errorNum', -4);
				return false;
			}
		}
		return true;
	}

	/*
	** 用来检查文件上传的大小
	*/
	private function checkFileSize()
	{
		if($this->fileSize > $this->maxsize){
			$this->setOPtion('errorNum', '-2');
			return false;
		}else{
			return true;
		}
	}

	/*
	** 用于检查文件上传类型
	*/
	private function checkFileType() 
	{
		if(in_array(strtolower($this->fileType), $this->allowtype)) {
			return true;
		}else{
			$this->setOption('errorNum', -1);
			return false;
		}
	}

	/*
	** 设置上传后的文件名称
	*/
	private function setNewFileName()
	{
		if($this->israndname){
			$this->setOption('newFileName', $this->proRandName());
		} else {
			$this->setOption('newFileName', $this->originName);
		}
	}

	/*
	** 设置随机文件名称
	*/
	private function proRandName()
	{
		$fileName=date("YmdHis").rand(100,999);
		return $fileName.'.'.$this->fileType;
	}

	/*
	** 用来上传一个文件
	*/
	public function uploadFile($fileField)
	{
		$return=true;
		//检查文件上传路径
		if(!$this->checkFilePath()){
			$this->errorMess=$this->getError();
			return false;
		}

		$name=$_FILES[$fileField]['name'];
		$tmp_name=$_FILES[$fileField]['tmp_name'];
		$size=$_FILES[$fileField]['size'];
		$error=$_FILES[$fileField]['error'];

		if(is_Array($name)){
			$errors=array();
			for($i=0; $i<count($name); $i++){
				if($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i])){
					if(!$this->checkFileSize() || !$this->checkFileType()){
						$errors[]=$this->getError();
						$return=false;
					}
				}else{
					$error[]=$this->getError();
					$return=false;
				}
				if(!$return)
					$this->setFiles();
			}
			if($return){
				$fileNames=array();
				for($i=0; $i<count($name); $i++){
					if($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i])){
						$this->setNewFileName();
						if(!$this->copyFile()){
							$errors=$this->getError();
							$return=false;
						}else{
							$fileNames[]=$this->newFileName;
						}
					}
				}
				$this->newFileName=$fileNames;
			}
			$this->errorMess=$errors;
			return $return;
		} else {
			if($this->setFiles($name, $tmp_name, $size, $error)){
				if($this->checkFileSize() && $this->checkFileType()){
					$this->setNewFileName();
					if($this->copyFile()){
						return true;
					}else{
						$return=false;
					}
				}else{
					$return=false;
				}
			}else{
				$return=false;
			}
			if(!$return)
				$this->errorMess=$this->getError();
			return $return;
		}
	}

	/*
	** 复制上传文件到指定的位置
	*/
	private function copyFile()
	{
		if(!$this->errorNum){
			$filepath=rtrim($this->filepath, '/').'/';
			$filepath.=$this->newFileName;
			if(@move_uploaded_file($this->tmpFileName, $filepath)) {
				return true;
			}else{
				$this->setOption('errorNum', -3);
				return false;
			}
		}else{
			return false;
		}
	}

	/*
	** 设置和$_FILES有关的内容
	*/
	private function setFiles($name="", $tmp_name='', $size=0, $error=0)
	{
		$this->setOption('errorNum', $error);
		if($error){
			return false;
		}
		$this->setOption('originName', $name);
		$this->setOption('tmpFileName', $tmp_name);
		$arrStr=explode('.', $name);
		$this->setOption('fileType', strtolower($arrStr[count($arrStr)-1]));
		$this->setOption('fileSize', $size);
		return true;
	}

	/*
	** 检查是否有同名文件，是否覆盖
	*/
	private function check_same_file($filename)
	{
		if(file_exists($filename)&&$this->over_write!=true){
			$this->setOption('errorNum', -6);
			return false;
		}    
	}

	/*
	** 检查文件是否是通过 HTTP POST 上传的
	*/
	private function is_upload_file($tmp_name)
	{
		if(!is_uploaded_file($tmp_name)){
			$this->setOption('errorNum', -7);
			return false;	
		}
	}

	/*
	** 用于获取上传后文件的文件名
	*/
	public function getNewFileName()
	{
		return $this->newFileName;
	}

	/*
	** 上传如果失败，则调用这个方法，就可以查看错误报告
	*/
	public function getErrorMsg() 
	{
		return $this->errorMess;
	}
 
/*
** 生成缩略图
*/
//最大宽：120，高：120
public function create_simg($img_w,$img_h)
{
$name=$this->set_name();
$folder=$this->creat_mulu();
$new_name="../../".$folder."/s_".$name;      
$imgsize=getimagesize($this->files_name());
 
switch ($imgsize[2]){
case 1:
if(!function_exists("imagecreatefromgif")){
echo "你的GD库不能使用GIF格式的图片，请使用Jpeg或PNG格式！返回";
exit();
}
$im = imagecreatefromgif($this->files_name());
break;
case 2:
if(!function_exists("imagecreatefromjpeg")){
echo "你的GD库不能使用jpeg格式的图片，请使用其它格式的图片！返回";
exit();
}
$im = imagecreatefromjpeg($this->files_name());
break;
case 3:
$im = imagecreatefrompng($this->files_name());
break;
case 4:
$im = imagecreatefromwbmp($this->files_name());
break;
default:
die("is not filetype right");
exit;
}
 
$src_w=imagesx($im);//获得图像宽度
$src_h=imagesy($im);//获得图像高度
$new_wh=($img_w/$img_h);//新图像宽与高的比值
$src_wh=($src_w/$src_h);//原图像宽与高的比值
if($new_wh<=$src_wh){
$f_w=$img_w;
$f_h=$f_w*($src_h/$src_w);
}else{
$f_h=$img_h;
$f_w=$f_h*($src_w/$src_h);
}
if($src_w>$img_w||$src_h>$img_h){      
if(function_exists("imagecreatetruecolor")){//检查函数是否已定义
$new_img=imagecreatetruecolor($f_w,$f_h);
if($new_img){
imagecopyresampled($new_img,$im,0,0,0,0,$f_w,$f_h,$src_w,$src_h);//重采样拷贝部分图像并调整大小
}else{
$new_img=imagecreate($f_w,$f_h);
imagecopyresized($new_img,$im,0,0,0,0,$f_w,$f_h,$src_w,$src_h);
}
}else{
$$new_img=imagecreate($f_w,$f_h);
imagecopyresized($new_img,$im,0,0,0,0,$f_w,$f_h,$src_w,$src_h);
}
if(function_exists('imagejpeg')){
imagejpeg($new_img,$new_name);
}else{
imagepng($new_img,$new_name);
}
imagedestroy($new_img);
}
//imagedestroy($new_img);
return $new_name;
} 	
}

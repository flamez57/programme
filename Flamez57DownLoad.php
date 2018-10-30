<?php
header("Content-type:text/html ; charset=utf-8");
  
if (!empty($_POST['submit'])){
 $url = $_POST['url'];
 $pictureName = $_POST['pictureName'];
 $img = getPicture($url,$pictureName);
 echo '<pre><img src="'.$img.'"></pre>';
 }
function getPicture($url,$pictureName){
 if ($url == "") return false;
 //获取图片的扩展名
 $info = getimagesize($url);
 $mime = $info['mime'];
 $type = substr(strrchr($mime,'/'), 1);
 //不同的图片类型选择不同的图片生成和保存函数
 switch($type){
 case 'jpeg':
  $img_create_func = 'imagecreatefromjpeg';
  $img_save_func = 'imagejpeg';
  $new_img_ext = 'jpg';
  break;
 case 'png':
  $img_create_func = 'imagecreatefrompng';
  $img_save_func = 'imagepng';
  $new_img_ext = 'png';
  break;
 case 'bmp':
  $img_create_func = 'imagecreatefrombmp';
  $img_save_func = 'imagebmp';
  $new_img_ext = 'bmp';
  break;
 case 'gif':
  $img_create_func = 'imagecreatefromgif';
  $img_save_func = 'imagegif';
  $new_img_ext = 'gif';
  break;
 case 'vnd.wap.wbmp':
  $img_create_func = 'imagecreatefromwbmp';
  $img_save_func = 'imagewbmp';
  $new_img_ext = 'bmp';
  break;
 case 'xbm':
  $img_create_func = 'imagecreatefromxbm';
  $img_save_func = 'imagexbm';
  $new_img_ext = 'xbm';
  break;
 default:
  $img_create_func = 'imagecreatefromjpeg';
  $img_save_func = 'imagejpeg';
  $new_img_ext = 'jpg';   
 }
 if ($pictureName == ""){
 $pictureName = time().".{$new_img_ext}";
 }else{
 $pictureName = $pictureName.".{$new_img_ext}";
 }
 $src_im = $img_create_func($url); //由url创建新图片
 $img_save_func($src_im, $pictureName); //输出文件到文件
 return $pictureName;
}
  
?>
<form method="POST" action="">
远程url地址：<input type="text" name="url" size=20 /><br />
文件名称：<input type="text" name="pictureName" size=20 />
<input type="submit" name="submit" value="下载" />
</form>

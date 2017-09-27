# programme
收集有趣的编程


下面是关于文件上传的使用示例

```shell
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<form enctype="multipart/form-data" method="post">

<input type="file" name="uploadfile"/>
<input type="submit" value="submit">
</form>
</body>
</html>



<?php
include('Flamez57Upload.php');
$upload = new \Flamez57\Flamez57Upload(['filepath' => 'upload', '$allowtype' => array('gif', 'jpg', 'png', 'jpeg'), 'maxsize' => '1000000', 'israndname' => true]); 

if ($upload->uploadFile('uploadfile')) {
	var_dump($upload->getNewFileName());
}

```

<?php
// 传入页码
$page = $_GET['p'];
// 根据页码取出数据：php->mysql处理
$host = 'localhost';
$username = 'root';
$password = '';
$db = 'test';
//连接数据库
$conn = mysql_connect($host,$username,$password);
if(!$conn){
	echo '数据库连接失败';
	exit;
}
//选择所需要操作的数据库
mysql_select_db($db);
//设置编码格式
mysql_set_charset('utf8');
//编写sql获取分页数据select*from表名limit起始位置，显示条数
$sql = "select * from page limit".($page-1)*10.",10";
//发送sql语句
$result = mysql_query($sql);
//处理我们的数据
while($row = mysql_fetch_assoc($result)){
	echo $row['id'];
	echo $row['name'];
}
//释放结果，关闭连接
mysql_free_result($result);
// 获取数据总数
$total_sql = "select counnt(*) from page";
$total_result = mysql_fetch_array(mysql_query($total_sql));
$total = $total_result[0];
//计算页数
$total_pages = ceil($total/$pageSize);
mysql_close($conn);
//显示数据+分页条
$page_banner = '';
//计算偏移量
$pageoffset = ($showPage-1)/2;
if($page>1){
	$page_banner .= "<a href'".$_SERVER['PHP_SELF']."?p=1'>首页</a>";
	$page_banner .="<a href='".$_SERVER['PHP_SELF']."?p=".($page-1)."'>上一页</a>";
}
if($page<$total_pages){
	$page_banner.="<a href='".$_SERVER['PHP_SELF']."?p=".($page+1)."'>下一页</a>";
	$page_banner.="<a href='".$_SERVER['PHP_SELF']."?p=".($total_pages)."'>尾页</a>";
}

echo $page_banner;
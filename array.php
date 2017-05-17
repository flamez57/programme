<!DOCTYPE html>
<html>
<head>
	<title>数学编程题</title>
	<meta charset="utf-8">
</head>
<body>
<h2> 问题 </h2>
<p>已知数列：发f(1)=f(2)=1,f(3)=0,f(n)=f(n-1)-2f(n-2)+f(n-3)  (n>2)。编程：</p>
<p>a） 显示f(1)到f(50)的所有项，每行显示6个值；</p>
<p>b）求改50项的最大值，最小值，及平均值，并显示</p>
<h4> 答案 </h4>
<?php
$f = array(
	'1'=>1,
	'2'=>1,
	'3'=>0
	);
function fun($f,$n){
	return $f[$n-1]-2*$f[$n-2]+$f[$n-3];
}

for($i=4;$i<51;$i++){
	$f[$i] = fun($f,$i);
}

$sum = 0;
foreach($f as $k=>$v){
	echo $v.'&nbsp;&nbsp;&nbsp;&nbsp;';
	if($k%6==0) echo '<br>';
	$sum += $v;
}


$pos = array_search(max($f), $f);
$poss = array_search(min($f), $f);
echo '<br>';
echo '最大值为:'.$f[$pos].'位于:'.$pos.'<br>';
echo '最小值为:'.$f[$poss].'位于:'.$poss.'<br>';
echo '平均值为:'.$sum/50;
?>
</body>
</html>

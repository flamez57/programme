<?php

$url = $_GET["url"];

shuchu($url);
$url = curl_post_302($url) ;
preg_match_all('/^Location:(.*)$/mi', $url['header'], $matches);

foreach ($matches[1] as $key => $value) {
	shuchu($value);
}


function shuchu($url){
	echo $url;
	echo "<br>";
	echo "<br>";
}

/**
 * 
 */
function curl_post_302($url,$data=null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//如果成功只将结果返回，不自动输出任何内容。
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 获取转向头部信息 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_NOBODY, 1);//不输出内容
     
    $data = curl_exec($ch);
    $Headers = curl_getinfo($ch); 
    curl_close($ch);

    if($data != $Headers){ 
        return array('url' => $Headers["url"], 'header'=>$data); 
    }else{
            return false;
    }
}

$url = "http://www.amazon.com/Bengoo-Portable-Desktop-Electric-Rechargeable/dp/tech-data/B01F70ZGYW%3FSubscriptionId%3DAKIAJWXT2MCY6ZQDW7VQ%26tag%3DASSOCIATETAG%26linkCode%3Dsp1%26camp%3D2025%26creative%3D386001%26creativeASIN%3DB01F70ZGYW";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Fiddler");
curl_setopt($ch, CURLOPT_HEADER, TRUE);
$response = curl_exec($ch);
curl_close($ch);
preg_match_all('/^Location:(.*)$/mi', $response, $matches);

echo ! empty($matches[1]) ? trim($matches[1][0]) : 'No redirect found';


    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

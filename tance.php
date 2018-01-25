<?php
/*
** 连接追踪工具
** <Flamez57@mysweet95.com>
*/
class tance
{
	public function index()
	{
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>链接追踪工具</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content"nofollow,="" noindex"="">
	</head>
	<body>
		<style type="text/css">
			*{-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;}
			#wrapper {max-width: 728px;margin: 2em auto 0 auto;padding: 0px;font-family: arial, verdana, sans-serif;font-size: 16px;}
			#wrapper h1{margin: 0px;padding: 15px;text-align: center;}
			#logo {text-align: center;}
			#logo_container {max-width: 501px;margin: 0px auto;}
			#outer_color {-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;width: 100%;padding: 10px;background: #E9B1D1;-webkit-border-radius: 10px;-moz-border-radius: 10px;border-radius: 10px;margin-bottom: 10px;}
			#search_body {padding: 10px 10px 10px 20px;background: #ffffff;-webkit-border-radius: 10px;-moz-border-radius: 10px;border-radius: 10px;margin: 0px auto;position: relative;}
			#arrow_right {width: 0; height: 0;border-top: 15px solid transparent;border-bottom: 15px solid transparent;border-left: 15px solid #E9B1D1;position: absolute;left: 0;}
			#general_body {padding: 10px;min-height: 20px;} 
			#content {padding: 10px;background: #ffffff;-webkit-border-radius: 10px;-moz-border-radius: 10px;border-radius: 10px;}
			#content.tracecontent{word-break: break-all;}
			#wrapper a{color: #000000;text-decoration: none;font-size: 12px;}
			div.arrow{margin: 15px 0;}
			.redirtext {font-weight: bold;color: #246E3B;padding-top: 3px;}
			.infotext {color: #000000;margin-top: 1em;}
			#form_text {border: 1px solid #cccccc;height: 32px;padding: 0 1ex 0 1ex;-moz-border-radius-topleft: 10px;-webkit-border-top-left-radius: 10px;border-top-left-radius: 10px;-moz-border-radius-bottomleft: 10px;-webkit-border-bottom-left-radius: 10px;border-bottom-left-radius: 10px;width: 100%;}
			#form_button{height: 32px;padding: 8px;margin-left: 10px;background: #C8A9DA;border: none;-moz-border-radius-topright: 10px;-webkit-border-top-right-radius: 10px;border-top-right-radius: 10px;-moz-border-radius-bottomright: 10px;-webkit-border-bottom-right-radius: 10px;border-bottom-right-radius: 10px;-webkit-box-shadow: 3px 3px 4px 0px rgba(50, 50, 50, 0.21);-moz-box-shadow:3px 3px 4px 0px rgba(50, 50, 50, 0.21);box-shadow:3px 3px 4px 0px rgba(50, 50, 50, 0.21);}
			#form_button:hover {cursor: pointer;}
			.good_status {margin-top: 1em;display: inline-block;padding: 1ex;border: #339900 solid 2px;color: #339900;-webkit-border-radius: 10px;-moz-border-radius: 10px;border-radius: 10px;font-weight: bold;}	
			#url {display: none;}
		</style>
		<div id="wrapper">
			<div id="logo">
				<div id="logo_container">
					<h2>链接追踪工具</h2>
				</div>
			</div>
			<div id="outer_color">		
				<div id="search_body">	
					<div id="arrow_right"></div>
					<table border="0" cellspacing="0" cellpadding="0">
						<tbody>
							<tr>
								<td align="left" valign="top" style="width:100%;">
									<input id="form_text" type="text" name="traceme" value="" placeholder="enter http://....">
								</td>
								<td align="left" valign="top">
									<input id="url" type="text" value="" name="url">
									<input id="form_button" type="submit" value="跟踪网址" alt="Trace URL" onclick="done()">	
								</td>
							
							</tr>
						</tbody>
					</table>
				</div> <!-- search_body -->
					 
				<div class="general_body">
					<h1>追踪结果</h1>
				</div>

				<div id="content" class="tracecontent">			 	 			 	
					
				</div> <!-- content -->
			</div><!-- outer_color -->
			<script type="text/javascript">
				function done(){
					document.getElementById("content").innerHTML = '<img src="./load.gif">';
					var url = document.getElementById('form_text').value;
					var formData = new FormData();
					formData.append("url", url);
					var request = new XMLHttpRequest();
					request.open("POST", "./tracer.php?a=done");
					request.send(formData);
					request.onreadystatechange = function () {

						if (request.readyState == 4 && request.status == 200) {
							var result = request.responseText;
							document.getElementById("content").innerHTML = result;
						}
					}
				}
			</script>
		</div> <!-- wrapper -->
	</body>
</html>
<?php
	}

	public function done()
	{
		try {
			// $url = "http://www.amazon.com/Bengoo-Portable-Desktop-Electric-Rechargeable/dp/tech-data/B01F70ZGYW%3FSubscriptionId%3DAKIAJWXT2MCY6ZQDW7VQ%26tag%3DASSOCIATETAG%26linkCode%3Dsp1%26camp%3D2025%26creative%3D386001%26creativeASIN%3DB01F70ZGYW";
			// $url = "https://www.aliexpressonlineshop.cc/test-6cd/chushi-2.php?c6=11860513&c7=222222";
			$url = $_POST['url'];
			
			$list = $this->curl_post_302($url);
			if ($list && is_array($list)) {
				$html = $url.'<br>';
				foreach ($list as $_list) {
					$html .= '<div class="arrow">';
					$html .= '<img align="middle" src="./arrow.gif" alt="">';
					$html .= '<span class="redirtext">'.$_list['type'].'</span>';
					$html .= '</div>';
					$html .= $_list['url'].'<br>';
				}
				$html .= '<div class="good_status">跟踪完成</div>';
				echo $html;
			} else {
				throw new Exception('没有跳转');
			}
		} catch (\Exception $e) {
			echo '<div class="infotext">'.$e->getMessage().'</div>';
		}
	}

	private function curl_post_302($url,$data=null)
	{
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
	    curl_close($ch);
	    preg_match_all('/^HTTP(.*)$/mi', $data, $typeF);
	    preg_match_all('/^Location:(.*)$/mi', $data, $urlF);
	    foreach ($urlF[1] as $_key => $_url) {
	    	$list[] = [
	    		'url' => $_url,
	    		'type' => $typeF[0][$_key],
	    	];
	    }
    	return $list;
	}

	private function dump($data)
	{
		echo '<pre>';
	    print_r($data);
	    echo '</pre>';
	}
}
$tance = new tance();
$index = isset($_GET['a']) ? $_GET['a'] : 'index';
$tance->$index();
?>

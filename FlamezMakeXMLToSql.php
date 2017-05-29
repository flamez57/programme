<?php
class FlamezMakeXMLToSql{
    private static $_instance = NULL;
    private $url;
    private $host;
	private $username;
	private $password;
	private $db;
	private $db_name;
    /**
     * @return FlamezMakeXMLToSql
     */
    final public static function getInstance()
    {
        if (!isset(self::$_instance) || !self::$_instance instanceof self) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    
    public function getSql($url,$host,$username,$password,$db,$db_name)
    {
    	$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->db = $db;
    	$this->url = $url;
    	$this->db_name = $db_name;
    	$sql = "INSERT INTO {$db_name}(conversion_id,conversion_date,offer_id,offer_name,campaign_id,subid_1,subid_2,subid_3,subid_4,subid_5,price,disposition) VALUES";
    	$row = 0;
    	$price = 0;
		// var_dump($this->getArray());
		$tt = $this->toMysql();
		$hl = $this->getArray();
		for($i=0;$i<$hl['row_count'];$i++){
			if(!in_array($hl['con'][$i]['conversion_id'],$tt)){
				$sql .="('{$hl['con'][$i]['conversion_id']}','{$hl['con'][$i]['conversion_date']}','{$hl['con'][$i]['offer_id']}','{$hl['con'][$i]['offer_name']}','{$hl['con'][$i]['campaign_id']}','{$hl['con'][$i]['subid_1']}','','{$hl['con'][$i]['subid_3']}','','{$hl['con'][$i]['subid_5']}','{$hl['con'][$i]['price']}','{$hl['con'][$i]['disposition']}'),";
				$row += 1;
				$price += $hl['con'][$i]['price'];
			}	
		}
		$data['row_count'] = $row;
		$data['price'] = $price;
		$data['sql'] = rtrim($sql,',');
		return $data;
    }
    private function getXml()
    {
    	$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$this->url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_HEADER,0);

		$output = curl_exec($ch);
		curl_close($ch);
		return trim($output);
    }
    private function XmlToArray($xml)
    {
    	$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS); 
		return @json_decode(@json_encode($xml),1);
    }
    private function getArray()
    {
    	$chu = $this->XmlToArray($this->getXml());
    	$data['row_count'] = $chu['row_count'];
    	$data['price'] = $chu['summary']['price'];
    	$data['con'] = $chu['conversions']['conversion'];
    	return $data;
    }
    private function toMysql()
    {
	$dsn="mysql:dbname={$this->db};host={$this->host}";
	try{
	    $pdo=new PDO($dsn,$this->username,$this->password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));
	}catch(PDOException $e){
	    return '数据库连接失败'.$e->getMessage();
	}
	$sql = "select conversion_id from {$this->db_name}";
	$res=$pdo->query($sql);
	foreach($res as $row){
	$rows[] = $row['conversion_id'];
	}
	return $rows;
    }
}
		
// $host = 'localhost';
// $username = 'root';
// $password = '';
// $db = 'text';
// $str = FlamezMakeXMLToSql::getInstance()->getSql('http://etmanonline.com/api/conv.xml',$host,$username,$password,$db,'conver');
// var_dump($str);

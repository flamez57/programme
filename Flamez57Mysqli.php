<?php
define("HOSTNAME","localhost");
define("USERNAME","root");
define("PASSWORD","");
define("DATANAME","");
define("PORT","3306");
define("CHARSET","UTF8");

/*
 * Flamez57Mysqli使用说明：
 * ===========================================================
 * 实例化连接器
 * $mysql = new Flamez57Mysqli(tablename);
 * ===========================================================
 * 获取当前表字段
 * $mysql->fields();
 * -----------------------------------------------------------
 * 条件查询
 * $mysql
 *		->where()
 *		->order(field,[desc])
 *		->group(field)
 *		->having()
 *		->limit(size,[start])
 *		->find(field);
 *
 *
 */

class Flamez57Mysqli
{

    //数据库连接
    private $dbLink;  

    //要操作的数据表
    private $tableName;  

    //当前表字段
    private $fields;  

    private $method = [
    "where" => "",
    "order" => "",
    "limit" => "",
    "group" => "",
    "having" => ""
    ];

    /*
     *	连接数据库
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
        try{
            $this->dbLink = mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DATANAME,PORT);
            mysqli_set_charset($this->dbLink,CHARSET);
        }catch(Exception $e){
            $this->errorMsg("数据库连接失败".mysqli_connect_error());
        }
        $this->fields();
    }

    /*
	 *	释放数据库连接
	 */
    public function __destruct()
    {
        mysqli_close($this->dbLink);
    }

    /*
	   * 返回错误信息
     */
    private function errorMsg($msg)
    {
    	echo "Flamez57Bug:".$msg;
    }

    /*
     * 查看表字段
     */
    public function fields()
    {
        $sql = " DESC {$this -> tableName}; ";
        $res = mysqli_query($this -> dbLink,$sql);
        $datas = mysqli_fetch_all($res,MYSQLI_ASSOC);
        for($i = 0 ;$i < count($datas);$i++){
            $fields[] = $datas[$i]['Field'];
        }
        $this->fields = $fields;
        return $fields;
    }

    /*
	   * 自动查找调用的方法执行后返回对象本身
     */
    public function __call($name,$value)
    {
        $name = strtolower($name);
        
        if(array_key_exists($name,$this -> method)){
        	switch ($name) {
        		case 'order':
        			$this->method['order'] = "ORDER BY ".$value[0]." ".(isset($value[1]) ? $value[1] : "ASC");
        			break;
        		case 'group':
        			$this->method['group'] = "GROUP BY ".$value[0];
        			break;
        		case 'limit':
        			$this->method['limit'] = "LIMIT ".(isset($value[1]) ? $value[1]."," : "").$value[0];
        			break;
        		default:
        			$this->method[$name] = $name." ".$value[0];
        	}
        }else{
            $this->errorMsg("the method is not found!");
        }
        return $this;
    }

    /*
	   * 将被掉到的方法拼接
     */
    public function method()
    {
        return " {$this -> method['where']} {$this -> method['group']} {$this -> method['having']} {$this -> method['order']} {$this -> method['limit']} ; ";
    }

    /*
     * 条件查询
     */
    public function find($field = "*")
    {
        if (in_array($field, $this->fields) || $field == '*') {
            $sql = "SELECT {$field} FROM {$this->tableName} {$this->method()} ";
        } else {
            $sql = "SELECT * FROM {$this->tableName}";
        }

        $res = mysqli_query($this->dbLink,$sql);
        $arr = mysqli_fetch_all($res,MYSQLI_ASSOC);
        return $arr;
    }
}

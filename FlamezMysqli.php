<?php
namespace Flamez57;
/*
 * FlamezMysqli使用说明：
 * ===========================================================
 * 实例化连接器
 * $mysql = new /Flamez57/FlamezMysqli(HOSTNAME, USERNAME, PASSWORD, DATANAME, PORT, CHARSET); 
 * ===========================================================
 * 获取最后执行的sql语句
 * $mysql->getLastQuery();
 * 获取最后报错
 * $mysql->lastError();
 * 获取最后插入的ID
 * $mysql->getInsertId();
 * 执行sql语句
 * $mysql->query($sql);
 * 转意过滤
 * $mysql->escape($unescaped, true);
 * 插入数据
 * $mysql->insert($table, $data, $isReplace = false);
 * 修改
 * $mysql->update($table, $data, $where);
 * 查询
 * $mysql->
 *		->order(field,[desc])
 *		->group(field)
 *		->having()
 *		->limit(size,[start])
 *		->select($table, $where, $fields = '*');
 * 查询一条
 * $mysql->getRow($sql,$array);
 * 查询全部
 * $mysql->getAll($sql,$array);
 * 统计条数
 * $mysql->count($table, $where=array());
 * 删除
 * $mysql->delete($table, $where=array());
 * 安全模式执行sql
 * $mysql->safeQuery($query, $bindParams, $paramType=NULL);
 * 开启事务
 * $mysql->beginTransaction();
 * 提交
 * $mysql->commit();
 * 回滚
 * $mysql->rollback();
 */

class FlamezMysqli
{
	/*
     * 数据库对象
     */
    protected $mysqli;

    /*
     *  最后一次执行的sql语句
     */
    private $last_query = "";

    private $method = [
        "order" => "",
        "limit" => "",
        "group" => "",
        "having" => ""
	];
    
    /*
	 * 链接数据库
     */
    public  function __construct($hostname, $username, $password, $dataname, $port, $charset)
    {
        if (!isset($this->mysqli)) {
            $this->mysqli = new \mysqli($hostname, $username, $password, $dataname, $port);
            $this->mysqli->set_charset($charset);
            if($this->mysqli->connect_errno){
                $this->errorMsg($this->mysqli->connect_errno);
            }
       	} 
    }

    /*
	 * 返回错误信息
     */
    private function errorMsg($msg)
    {
    	echo "Flamez57Bug:".$msg;
    }

    /*
	 * 获取最后执行的sql语句
     */
    public function getLastQuery()
    {
        return $this->last_query;
    }

    /*
	 * 设置最后执行的sql语句
     */
    public function setLastQuery($sql)
    {
        $this->last_query=$query;
    }

    /*
     *  执行sql语句并将sql暂存
     */
    public function query($sql)
    {
        $this->setLastQuery($sql);
        return $this->mysqli->query($sql);
    }

    /*
     *  获取插入数据的id
     */
    public function getInsertId()
    {
        return $this->mysqli->insert_id;
    }


    /*
     *  获取执行任务的错误信息
     */
    public function lastError()
    {
        return $this->mysqli->error;
    }

    /*
     * 查看表字段
     */
    public function fields($table)
    {
        $sql = "DESC {$table}";
        $datas = $this->query($sql);
        for($i = 0 ;$i < count($datas);$i++){
            $fields[] = $datas[$i]['Field'];
        }
        return $fields;
    }

    /*
	 * 检测调用的方法是否存在
     */
    public function __call($name, $arguments)
    {
        if(method_exists($this->mysqli, $name)){
            return call_user_func_array(array($this->mysqli,$name), $arguments);
        } elseif(array_key_exists($name,$this->method)){
        	switch ($name) {
        		case 'order':
        			$this->method['order'] = "ORDER BY ".$arguments[0]." ".(isset($arguments[1]) ? $arguments[1] : "ASC");
        			break;
        		case 'group':
        			$this->method['group'] = "GROUP BY ".$arguments[0];
        			break;
        		case 'limit':
        			$this->method['limit'] = "LIMIT ".(isset($arguments[1]) ? $arguments[1]."," : "").$arguments[0];
        			break;
        		default:
        			$this->method[$name] = $name." ".$arguments[0];
        	}
        }
        $this->errorMsg("没有找到方法 $name!");
    }
    

    /*
    **  检测调用的属性是否存在
    */
    public function __get($name)
    {
        if(property_exists($this->mysqli, $name)){
            return $this->mysqli->$name;
        }
        $this->errorMsg("没有找到属性 $name!");
    }

    /*
	 * 将被掉到的方法拼接
     */
    public function method()
    {
        return " {$this -> method['where']} {$this -> method['group']} {$this -> method['having']} {$this -> method['order']} {$this -> method['limit']} ; ";
    }

    /*
	 * 转意过滤
     */
    public function escape($var, $recurse_escape=TRUE)
    {
       
        if (!is_array($var)) {
            $res = $this->mysqli->real_escape_string($var);
        } else {
            $res = array();
            foreach ($var as $key=>$value) {
                if ($recurse_escape) {
                    $res[$key] = $this->escape($value, $recurse_escape);
                } else {
                    $res[$key] = $value;
                }
                
            }
        }
        return $res;
    }

    /*
     *  插入数据
     */
    public function insert($table, $data, $isReplace = false)
    {
        $data = $this->escape($data);    
        
        $query = ($isReplace?"REPLACE":"INSERT")." INTO {$table} (".join(',', array_keys($data)).") VALUES ('".join('\',\'', $data)."')"
       
        $this->query($query);
        return $this->mysqli->insert_id;
    }

    /*
	 * 条件where组装
     */
    private function getWhere($where)
    {
        if (empty($where)) {
            return "";
        } else {

	 		$where = $this->escape($where);
	        foreach ($where as $k => $v)
	        {                       
	            $query_w[] = "`" . $k .  "`=?";
	        }
            return " WHERE ".implode(" AND ", $query_w);
        }
    }

    /*
	 *  修改
     */
    public function update($table, $data, $where)
    {        
        $query_v = array();
                
        foreach($data as $k=>$v)
        {              
            $query_v[] = "`" . $k .  "`=? ";           
        }
        $where_condition = $this->getWhere($where);

        $query = "UPDATE {$table} SET " . implode(", ", $query_v) . $where_condition;
        $this->safeQuery($query, array_merge(array_values($data), array_values($where)));
        return $this->mysqli->affected_rows;
    }

    /*
	 * 查询
     */
    public function select($table, $where, $fields = '*')
    {
        $query="SELECT {$fields} FROM {$table} ";
        if (empty($where)) {
            return $this->query($query);
        }

        $where_condition = $this->getWhere($where);
 
        $query .= $where_condition;
        $query .= $this->method();
        
        return $this->safeQuery($query, array_values($where));
    }

    /*
	 * 查一条
     */
    public function getRow($sql, $where = array())
    {
    	$data = $this->safeQuery($sql, array_values($where));
    	if ($data[0]) {
    		return $data[0];
    	} else {
    		return array();
    	}
    }

    /*
	 * 查全部
     */
    public function getAll($sql, $where = array())
    {
    	return $this->safeQuery($sql, array_values($where));
    }

    /*
	 *  条件统计
     */
    public function count($table, $where=array())
    {

        $where_condition = $this->getWhereCondition($where);  
   
        $query = "SELECT count(*) as count FROM $table $where_condition";
        if ($data = $this->getRow($query, $where)) {
        	return $data['count'];
        } else {
        	return 0;
        }
    }

    public function delete($table, $where=array())
    {
        $where_condition = $this->getWhere($where);
        $query = "DELETE FROM $table ".$where_condition;
        $this->safeQuery($query, array_values($where));
        return $this->mysqli->affected_rows;
    }

    /*
     *  释放数据
     */
    public function __destruct()
    {
        if(isset($this->mysqli)){
        	$this->mysqli->free();
            $this->mysqli->close();
        }
        
    }

    /*
	 * 获取参数类型
     */
     protected function determineType($item)
    {
        switch (gettype($item)) {
            case 'NULL':
            case 'string':
                return 's';
                break;

            case 'integer':
                return 'i';
                break;

            case 'blob':
                return 'b';
                break;

            case 'double':
                return 'd';
                break;
        }
        return '';
    }

    /*
	 * 判断是否是关联数组
     */
    private function isArrayAssoc($array){
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    protected function refValues($arr)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = array();
            foreach ($arr as $key => $value) {
                $refs[$key] = & $arr[$key];
            }
            return $refs;
        }
        return $arr;
    }

    /*
     *  安全执行sql
     */
    public function safeQuery($query, $bindParams, $paramType=NULL)
    {
        $this->setLastQuery($query);       
        if (!is_array($bindParams)) {
            $bindParams = array($bindParams);
        }
        $stmt = $this->mysqli->prepare($query);
        if ($this->isArrayAssoc($bindParams)) {
            foreach ($bindParams as $key => $value) {
                $stmt->bind_param($key, $value);
            }
        } else {
           if (!is_null($paramType)) {
                if (is_array($paramType)) {
                    $params[0] = implode("", $paramType);
                } else {
                    $params[0] = $paramType;        
                }
                
            } else {
                $params[0]="";
            }

            foreach ($bindParams as $prop => $val) {
                if (is_null($paramType)) {
                    $params[0] .= $this->determineType($val);    
                }
            
                array_push($params, $bindParams[$prop]);
            }
           call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));             
        }
      
        
        if (!$stmt->execute()) {
            return FALSE;
        }
        if (stripos($query, "SELECT") !== FALSE) {
            $return_value = $stmt->get_result();             
        } else {
            $return_value = TRUE;
        }
        $stmt->free_result();
        $stmt->close();
        return $return_value;
           
    }

    /*
	 * 开启事务
     */
    public function beginTransaction()
    {
    	return $this->mysqli->autocommit(0);
    }

    /*
	 * 提交
     */
    public function commit()
    {
	    $this->mysqli->commit();
	    $this->mysqli->autocommit(1);
	  	$this->mysqli->close();
    }

    /*
	 * 回滚
     */
    public function rollback()
    {
    	$this->mysqli->rollback();
    	$this->mysqli->autocommit(1);
  		$this->mysqli->close();
    }

}

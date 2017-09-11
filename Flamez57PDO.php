<?php
namespace Flamez57;
/*
** Flamez57PDO使用说明：
** ===========================================================
** 实例化连接器
** $mysql = new \Flamez57\Flamez57PDO($hostname, $username, $password, $dataname, $port, $charset, false); 
** ===========================================================
*/
class Flamez57PDO
{
	/*
	** 数据库链接对象
	*/
	private $pdo;

	private $debug = false;
	private $error = 'nothing error';
    
	/*
	** 链接数据库
	*/
	public  function __construct($hostname, $username, $password, $dataname, $port, $charset, $debug)
	{
		$this->debug = $debug;
		if (!isset($this->pdo)) {
			$dsn="mysql:dbname={$dataname};host={$hostname};port={$port}";
			try{
				$this->pdo =  new \PDO($dsn,$username,$password,array(\PDO::MYSQL_ATTR_INIT_COMMAND => "set names {$charset}"));
				$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);    //设置异常处理方式
				$this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);   //设置默认关联索引遍历
			}catch(\PDOException $e){
				$this->errorMsg('数据库连接失败'.$e->getMessage());
			}
		} 
	}

	/*
	** 返回错误信息
	*/
	private function errorMsg($msg)
	{
		if (is_array($msg)) {
			echo "<strong>Flamez57Bug:</strong>";
			var_dump($msg);
		} else {
			echo "<strong>Flamez57Bug:</strong>".$msg;
		}
	}

    
	/*
	**	执行sql 返回受影响行数
	**	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	**	@param string $sql
	**	@return string
	*/
	public function query($sql)
	{
		try{
			$pre = $this->pdo->query($sql);
		}catch(\PDOException $e){
			$this->error = $e->getMessage();
			return 0; 	
		} 
		return $pre->rowCount();
	}

	/*
	** 检测调用的方法是否存在
	*/
	public function __call($name, $arguments)
	{
		if(method_exists($this->pdo, $name)){
			return call_user_func_array(array($this->pdo,$name), $arguments);
		}
		$this->errorMsg("没有找到方法 $name!");
	}


	/*
	**  检测调用的属性是否存在
	*/
	public function __get($name)
	{
		if(property_exists($this->pdo, $name)){
			return $this->pdo->$name;
		}
		$this->errorMsg("没有找到属性 $name!");
	}

	/*
	**	插入数据
	**	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	**	@param string $table
	**	@param array $data = ['id' => '5'];
	**	@param bool $isReplace
	**	@return array
	*/
	public function insert($table, $data, $isReplace = false)
	{
		$sql = ($isReplace?"REPLACE":"INSERT")." INTO {$table} (".join(',', array_keys($data)).") VALUES (:".join(',:', array_keys($data)).")";
		$pre = $this->pdo->prepare($sql);
		try{
			$pre->execute($data);
		}catch(\PDOException $e){
			$this->error = $e->getMessage();
			return 0; 	
		} 
		return $this->pdo->lastInsertId();
	}

	/*
	**	修改 (条件仅限 and = )
	**	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	**	@param string $table 表名
	**	@param array $params 待修改数据
	**	@parma array $wheres  条件
	**	@return string  1/0
	*/
	public function update($table, $params, $wheres)
	{
		$where = '';
		$param = '';
		foreach ($params as $k => $v) {
			$param .= $k."=:".$k.",";
		}
		foreach ($wheres as $k => $v) {
			$where .= $k."=:".$k." AND";
		}
		$where = rtrim($where, 'AND');
		$param = rtrim($param, ',');
		$sql="UPDATE {$table} SET {$param} WHERE {$where}";
		$data = array_merge($params,$wheres);
		$pre = $this->pdo->prepare($sql);
		try{
			$pre->execute($data);
		}catch(\PDOException $e){
			$this->error = $e->getMessage();
			return 0; 	
		} 
		return $pre->rowCount();
	}

	/*
	**	删除 (条件仅限 and = )
	**	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	**	@param string $table 表名
	**	@parma array $wheres  条件
	**	@return string 1/0
	*/
	public function delete($table, $wheres)
	{
		$where = '';
		foreach ($wheres as $k => $v) {
			$where .= $k."=:".$k." AND";
		}
		$where = rtrim($where, 'AND');
		$sql="DELETE FROM {$table} WHERE {$where}";
		$pre = $this->pdo->prepare($sql);
		try{
			$pre->execute($wheres);
		}catch(\PDOException $e){
			$this->error = $e->getMessage();
			return 0; 	
		} 
		return $pre->rowCount();       
	}

	/*
	**	查询全部
	**	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	**	@param string $sql = "SELECT * FROM user WHERE id>:id";
	**	@param array $data = ['id' => '5'];
	**	@return array
	*/
	public function getAll($sql, $data = array())
	{
		$pre = $this->pdo->prepare($sql);
		try{
			$pre->execute($data);
		}catch(\PDOException $e){
			$this->error = $e->getMessage();
			return 0; 	
		} 
		return $pre->fetchAll();
	}

	/*
	**	查询一行
	**	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	**	@param string $sql = "SELECT * FROM user WHERE id=:id";
	**	@param array $data = ['id' => '5'];
	**	return array
	*/
	public function getRow($sql, $data = array())
	{
		$pre = $this->pdo->prepare($sql);
		try{
			$pre->execute($data);
		}catch(\PDOException $e){
			$this->error = $e->getMessage();
			return 0; 	
		} 
		return $pre->fetch();
	}

	/*
	**  释放数据
	*/
	public function __destruct()
	{
		if(isset($this->pdo)){
			if ($this->debug) {
				$this->errorMsg($this->error);
			}
			$this->pdo = null;
		}    	        
	}

	/*
	** 开启事务处理
	*/
	public function beginTransaction()
	{
		return $this->pdo->beginTransaction();
	}

	/*
	** 提交事务
	*/
	public function commit()
	{
		return $this->pdo->commit();
	}

	/*
	** 事务回滚
	*/
	public function rollback()
	{
		return $this->pdo->rollBack();
	}
}

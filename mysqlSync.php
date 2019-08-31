<?php
/**
 * 同步两个库的表的表结构
 * User: flamez57
 * Date: 2019/7/9
 * Time: 11:09
 */
class mysqlSync{

	private $selfpdo;
	private $sourcepdo;
	private $sourceConf; //源配置信息
	private $selfConf;   //目标配置信息
	private $canNotTable = array(); //无法处理的表

	public function __construct($sourceConf,$selfConf)
	{
		$this->sourceConf = $sourceConf;
		$this->selfConf = $selfConf;
		$this->getConnection($selfConf,'selfpdo');
		$this->getConnection($sourceConf,'sourcepdo');
		$this->CreateTableToSelf();
	}
	private function getConnection($conf,$hl,$charset = 'utf8')
	{
		$dsn="mysql:dbname={$conf['db']};host={$conf['host']};port={$conf['port']};charset={$charset}";
		try{
		 	$this->$hl =  new PDO($dsn,$conf['user'],$conf['pass'],[PDO::ATTR_PERSISTENT=>true]);
		}catch(PDOException $e){
		   	echo $hl.'数据库连接失败'.$e->getMessage();
		}
	}

	// 查看表索引
	public function getIndex($conf,$table_name)
	{
		$stmt = $this->$conf->prepare('show keys from '.$table_name);  
		$stmt->execute();  
		$datai = $stmt->fetchAll(PDO::FETCH_ASSOC); 
		foreach($datai as $v){
			$data[$v['Key_name']][$v['Seq_in_index']] = $v['Column_name'];
			$data[$v['Key_name']]['Index_type'] = $v['Index_type'];
			$data[$v['Key_name']]['Non_unique'] = $v['Non_unique'];
		}
		return $data;
	}

	// 查看库里面所有的表
	public function showTable($conf)
	{
		$stmt = $this->$conf->prepare('show tables');  
		$stmt->execute();  
		$dataz = $stmt->fetchAll(PDO::FETCH_NUM);
		foreach($dataz as $k=>$v){
			$data[$k] = $v[0];
		}
		return $data;
	}

	// 获取表字段
	public function getField($conf,$table_name,$db)
	{
		$stmt = $this->$conf->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table_name}' AND table_schema = '{$db}'");  
		$stmt->execute();  
		$dataz = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($dataz as $v){
			$data[$v['COLUMN_NAME']] = $v;
		}
		return $data;
	}

	// 查看建表语句
	public function showCreate($conf,$table_name)
	{
		$stmt = $this->$conf->prepare('show create table '.$table_name);  
		$stmt->execute();  
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	//源有目标没有
	public function diffTable()
	{
		if(empty($this->showTable('selfpdo'))){
			return $this->showTable('sourcepdo');
		}else{
			//无法处理的表下面打印的是
			$this->canNotTable = array_diff($this->showTable('sourcepdo'),$this->showTable('selfpdo'));
			return array_diff($this->showTable('sourcepdo'),$this->showTable('selfpdo'));
		}
	}

	//目标数据库特有的表
	public function getSelfNewTable()
	{
		return array_diff($this->showTable('selfpdo'),$this->showTable('sourcepdo'));
	}

	//给目标数据库建立没有的表
	public function CreateTableToSelf()
	{
		$data = $this->diffTable();
		foreach($data as $v){
			$stmt = $this->selfpdo->prepare($this->showCreate('sourcepdo',$v)['Create Table']);  
			$stmt->execute(); 
		}
	}

	// 对比存在不一致的字段属性
	public function diff_field_attr($source,$self)
	{

		$key = array_keys($source);
		foreach($key as $v){
			if(!empty($self[$v])){
				//防止库名影响对比
				unset($source[$v]['TABLE_SCHEMA']);
				unset($self[$v]['TABLE_SCHEMA']);
				//防止索引影响 COLUMN_KEY
				unset($source[$v]['COLUMN_KEY']);
				unset($self[$v]['COLUMN_KEY']);
				// COLUMN_DEFAULT
				unset($source[$v]['COLUMN_DEFAULT']);
				unset($self[$v]['COLUMN_DEFAULT']);
				//PRIVILEGES
				unset($source[$v]['PRIVILEGES']);
				unset($self[$v]['PRIVILEGES']);
				
				unset($source[$v]['CHARACTER_OCTET_LENGTH']);
                                unset($self[$v]['CHARACTER_OCTET_LENGTH']);

                                unset($source[$v]['CHARACTER_SET_NAME']);
                                unset($self[$v]['CHARACTER_SET_NAME']);


                                unset($source[$v]['COLLATION_NAME']);
                                unset($self[$v]['COLLATION_NAME']);

                                unset($source[$v]['ORDINAL_POSITION']);
                                unset($self[$v]['ORDINAL_POSITION']);

				
				if(array_diff($source[$v],$self[$v]) || array_diff($self[$v],$source[$v])){
					$data[] = $source[$v];
				}
			}	
		}
		return $data;
	}

	// 对比联合索引里的字段
	public function diff_index_attr($source,$self)
	{

		$key = array_intersect_key($source,$self);
		$key2 = array_intersect_key($self,$source);

		foreach($key as $k=>$v){
				if(array_diff($v,$key2[$k]) || array_diff($key2[$k],$v)){
					$data[$k] = $v;
				}
		}
		return $data;
	}

	//按照源来处理目标表字段
	public function disposeField()
	{
		$datah = array();
		foreach($this->showTable('selfpdo') as $v){
			if(!empty($this->getField('sourcepdo',$v,$this->sourceConf['db']))){
				//目标多余的字段要删除的
				if(!empty(array_diff_key($this->getField('selfpdo',$v,$this->selfConf['db']),$this->getField('sourcepdo',$v,$this->sourceConf['db'])))){
					$sqll = array_values(array_diff_key($this->getField('selfpdo',$v,$this->selfConf['db']),$this->getField('sourcepdo',$v,$this->sourceConf['db'])));
					foreach($sqll as $vl){
						$datah[] = "ALTER TABLE `{$vl['TABLE_NAME']}` DROP `{$vl['COLUMN_NAME']}`";
					}
				}
			}
		}
		foreach($this->showTable('sourcepdo') as $v){
			if(!empty($this->getField('selfpdo',$v,$this->selfConf['db']))){
				//源有目标没有要添加的
				if(!empty(array_diff_key($this->getField('sourcepdo',$v,$this->sourceConf['db']),$this->getField('selfpdo',$v,$this->selfConf['db'])))){
					$sqlh = array_values(array_diff_key($this->getField('sourcepdo',$v,$this->sourceConf['db']),$this->getField('selfpdo',$v,$this->selfConf['db'])));
					foreach($sqlh as $vh){
						$ifnull = $vh['IS_NULLABLE'] == 'NO' ? 'NOT NULL':'NULL';
						$datah[] = "ALTER TABLE `{$vh['TABLE_NAME']}` ADD `{$vh['COLUMN_NAME']}` {$vh['COLUMN_TYPE']} {$ifnull} {$vh['EXTRA']} COMMENT '{$vh['COLUMN_COMMENT']}'";
					}
				}
			}
		}
		foreach($this->showTable('sourcepdo') as $v){
			if(!empty($this->getField('selfpdo',$v,$this->selfConf['db']))){
				//源与目标字段不一致的情况
				if(!empty($this->diff_field_attr($this->getField('sourcepdo',$v,$this->sourceConf['db']),$this->getField('selfpdo',$v,$this->selfConf['db'])))){
					$sqlh = array_values($this->diff_field_attr($this->getField('sourcepdo',$v,$this->sourceConf['db']),$this->getField('selfpdo',$v,$this->selfConf['db'])));
					foreach($sqlh as $vh){
						$ifnull = $vh['IS_NULLABLE'] == 'NO' ? 'NOT NULL':'NULL';
						$datah[] = "ALTER TABLE `{$vh['TABLE_NAME']}` CHANGE `{$vh['COLUMN_NAME']}` `{$vh['COLUMN_NAME']}` {$vh['COLUMN_TYPE']} {$ifnull} {$vh['EXTRA']} COMMENT '{$vh['COLUMN_COMMENT']}'";
					}
				}
			}
		}
		$this->duSql($datah);
		return $datah;
	}

	//处理索引
	public function disposeIndex()
	{
		$datal = array();
		foreach($this->showTable('sourcepdo') as $v){

			if(!empty($this->getIndex('sourcepdo',$v)) && !empty($this->getIndex('selfpdo',$v))){ //两者都不为空就对比
				//目标多余的字段要删除的
				
				if(!empty(array_diff_key($this->getIndex('selfpdo',$v),$this->getIndex('sourcepdo',$v)))){  //目标有多余删除
					$sqll = array_diff_key($this->getIndex('selfpdo',$v),$this->getIndex('sourcepdo',$v));
					foreach($sqll as $ik=>$iv){
						if($ik == 'PRIMARY'){
							$datal[] = "ALTER TABLE {$v} DROP PRIMARY KEY";
						}else{
							$datal[] = "ALTER TABLE {$v} DROP INDEX {$ik}";
						}
					}
				}
				if(!empty(array_diff_key($this->getIndex('sourcepdo',$v),$this->getIndex('selfpdo',$v)))){  //源有多余就增加
					$sqll = array_diff_key($this->getIndex('sourcepdo',$v),$this->getIndex('selfpdo',$v));
					foreach($sqll as $ik=>$iv){
						if($iv['Index_type'] ){
							$btree = "USING {$iv['Index_type']}";
						}else{
							$btree = "";
						}
						unset($iv['Index_type']);
						if($ik == 'PRIMARY'){
							$datal[] = "ALTER TABLE `{$v}` ADD PRIMARY KEY(`{$iv[1]}`) {$btree}";
						}else{
							if ($iv['Non_unique'] == 0) {
                                                                $unique = 'UNIQUE';
                                                        } else {
                                                                $unique = '';
                                                        }

							$con = '';
							foreach($iv as $vv){
								$con .= '`'.$vv.'`,';
							}
							$con = rtrim($con,',');
							$datal[] = "ALTER TABLE `{$v}` ADD {$unique} INDEX {$ik} ( {$con}) {$btree}";
						}
					}
				}
				if(!empty($this->diff_index_attr($this->getIndex('sourcepdo',$v),$this->getIndex('selfpdo',$v)))){  //对比索引里面的字段
					$sqll = $this->diff_index_attr($this->getIndex('sourcepdo',$v),$this->getIndex('selfpdo',$v));

					foreach($sqll as $ik=>$iv){
						if($iv['Index_type'] ){
							$btree = "USING {$iv['Index_type']}";
						}else{
							$btree = "";
						}
						unset($iv['Index_type']);
						if($ik == 'PRIMARY'){
							$datal[] = "ALTER TABLE `{$v}` ADD PRIMARY KEY(`{$iv[1]}`) {$btree}";
						}else{
							if ($iv['Non_unique'] == 0) {
                                                                $unique = 'UNIQUE';
                                                        } else {
                                                                $unique = '';
                                                        }

							$con = '';
							foreach($iv as $vv){
								$con .= '`'.$vv.'`,';
							}
							$con = rtrim($con,',');
							$datal[] = "ALTER TABLE `{$v}` DROP INDEX `{$ik}`, ADD {$unique} INDEX `{$ik}` ({$con}) {$btree}";
						}
					}
				}
			}elseif(empty($this->getIndex('sourcepdo',$v)) && !empty($this->getIndex('selfpdo',$v))){  //源为null则删除
				foreach($this->getIndex('selfpdo',$v) as $ik=>$iv){
					if($ik == 'PRIMARY'){
						$datal[] = "ALTER TABLE {$v} DROP PRIMARY KEY";
					}else{
						$datal[] = "ALTER TABLE {$v} DROP INDEX {$ik}";
					}
				}
			}elseif(empty($this->getIndex('selfpdo',$v)) && !empty($this->getIndex('sourcepdo',$v))){   //目标为空就增加
				foreach($this->getIndex('sourcepdo',$v) as $ik=>$iv){
					if($iv['Index_type'] ){
						$btree = "USING {$iv['Index_type']}";
					}else{
						$btree = "";
					}
					unset($iv['Index_type']);
					if($ik == 'PRIMARY'){
						$datal[] = "ALTER TABLE `{$v}` ADD PRIMARY KEY(`{$iv[1]}`) {$btree}";
					}else{
						if ($iv['Non_unique'] == 0) {
                                                                $unique = 'UNIQUE';
                                                        } else {
                                                                $unique = '';
                                                        }

						$con = '';
						foreach($iv as $vv){
							$con .= '`'.$vv.'`,';
						}
						$con = rtrim($con,',');
						$datal[] = "ALTER TABLE `{$v}` ADD {$unique} INDEX {$ik} ( {$con}) {$btree}";
					}
				}
			}
		}
		$this->duSql($datal);
		return $datal;
	}

	//执行sql语句
	public function duSql($data)
	{
		foreach($data as $v){
			$stmt = $this->selfpdo->prepare($v);  
			$stmt->execute(); 
		}
	}

	//运行
	public function run()
	{
		echo '<h2>同步表结构</h2>';
		echo '<hr>';
		echo '<h4>同步索引执行的sql语句</h4>';
		foreach($this->disposeIndex() as $v){
			echo $v.'<br>';
		}
		echo '<hr>';
		echo '<h4>同步表字段执行的sql语句</h4>';
		foreach($this->disposeField() as $v){
			echo $v.'<br>';
		}
		echo '<hr>';
		echo '<h4>目标数据库特有的表:</h4>';
		foreach($this->getSelfNewTable() as $v){
			echo $v.'<br>';
		}
		echo '<hr>';
		echo '<h4>需要手动处理的表:</h4>';
		foreach($this->canNotTable as $v){
			echo $v.'<br>';
		}
		echo '<hr>';
	}
}

//目标数据库
$selfConf = array(
	'host'=>'localhost',
	'user'=>'root',
	'pass'=>'',
	'db'=>'text',
	'port'=>'3306'
);
//源数据库
$sourceConf = array(
	'host'=>'localhost',
	'user'=>'root',
	'pass'=>'',
	'db'=>'weixin',
	'port'=>'3306'
);
$mysql = new mysqlSync($sourceConf,$selfConf);
$mysql->run();

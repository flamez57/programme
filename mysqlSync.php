<?php

class mysqlSync{

	private $selfpdo;
	private $sourcepdo;
	private $sourceConf; //源配置信息
	private $selfConf;   //目标配置信息

	public function __construct($sourceConf,$selfConf)
	{
		$this->sourceConf = $sourceConf;
		$this->selfConf = $selfConf;
		$this->getConnection($selfConf,'selfpdo');
		$this->getConnection($sourceConf,'sourcepdo');
		$this->CreateTableToSelf();
	}
	private function getConnection($conf,$hl)
	{
		$dsn="mysql:dbname={$conf['db']};host={$conf['host']}";
		try{
		 	$this->$hl =  new PDO($dsn,$conf['user'],$conf['pass'],array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));
		}catch(PDOException $e){
		   	echo $hl.'数据库连接失败'.$e->getMessage();
		}
	}

	// 查看表索引
	public function getIndex($conf)
	{
		$stmt = $this->$conf->prepare('show keys from ims_bj_qmxk_channel_list');  
		$stmt->execute();  
		return $stmt->fetchAll(PDO::FETCH_ASSOC); 
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
		//无法处理的表下面打印的是
		//var_dump(array_diff($this->showTable('sourcepdo'),$this->showTable('selfpdo')));
		return array_diff($this->showTable('sourcepdo'),$this->showTable('selfpdo'));
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
			if(array_diff($source[$v],$self[$v])){
				$data[] = $source;
			}
		}
		// var_dump($source);
		// var_dump(array_keys($source));
		// var_dump($self);
		var_dump($data);
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
						$datah[] = "ALTER TABLE `{$vh['TABLE_NAME']}` ADD `{$vh['COLUMN_NAME']}` {$vh['COLUMN_TYPE']} {$ifnull} COMMENT '{$vh['COLUMN_COMMENT']}'";
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
						$datah[] = "ALTER TABLE `{$vh['TABLE_NAME']}` CHANGE `{$vh['COLUMN_NAME']}` `{$vh['COLUMN_NAME']}` {$vh['COLUMN_TYPE']} {$ifnull} COMMENT '{$vh['COLUMN_COMMENT']}'";
					}
				}
			}
		}
		return $datah;
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
		echo '<h4>同步表字段执行的sql语句</h4>';
		foreach($this->disposeField() as $v){
			echo $v.'<br>';
		}
		echo '<h4>目标数据库特有的表:</h4>';
		foreach($this->getSelfNewTable() as $v){
			echo $v.'<br>';
		}
		echo '<hr>';
	}
}


$selfConf = array(
	'host'=>'localhost',
	'user'=>'root',
	'pass'=>'',
	'db'=>'weixin2'
);
$sourceConf = array(
	'host'=>'localhost',
	'user'=>'root',
	'pass'=>'',
	'db'=>'weixin'
);
$mysql = new mysqlSync($sourceConf,$selfConf);

// var_dump($mysql->getIndex('selfpdo'));

// var_dump($mysql->getIndex('sourcepdo'));
// echo '<hr>';

// $mysql->disposeField();

// ALTER TABLE `ims_bj_qmxk_activity_entry` DROP `updatetime`
// ALTER TABLE `ims_bj_qmxk_channel_list` ADD `channel_type` INT(2) NOT NULL COMMENT '所选择的模式';
// ALTER TABLE `ims_bj_qmxk_activity_entry` CHANGE `updatetime` `updatetime` INT(10) NULL DEFAULT NULL COMMENT '修改时间';
// ALTER TABLE `ims_bj_qmxk_channel_module_content` ADD INDEX( `module_id`, `c_order`, `status`);
// ALTER TABLE ims_bj_qmxk_activity_entry DROP PRIMARY KEY
// ALTER TABLE ims_bj_qmxk_channel_list DROP INDEX channel_order;

$mysql->run();

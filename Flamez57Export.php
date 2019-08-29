<?php
$type     = 'mysql'; //数据库类型
$db_name  = $argv[1].''; //数据库名
/*  php Flamez57Export.php 库名*/
/*
$host     = '127.0.0.1';
$port     = '3306';
$username = 'root';
$password = '123456';
$charset = 'utf8';
*/

$host     = 'ip';
$port     = '3306';
$username = 'root';
$password = 'root';
$charset = 'utf8';

$with_cmt = true; 
$is_alter_type = false;  //是否返回ALTER TABLE 方式，否则 DROP TABLE , CREATE TABLE
$utf8Type = 'utf8mb4'; //$db_name == 'discovery' ? 'utf8mb4' : 'utf8'; //强制转换目标字符集
$dataType = isset($argv[2]) && $argv[2] == 'array' ? 'array' : 'str';
$withData = true;//explode('|', 'sl_admin|sl_common_setting|sl_cron_task|sl_district|sl_express|sl_member_act_tag|sl_menu|sl_pay_client|sl_pay_method|sl_rbac_node|sl_rbac_node_group|sl_rbac_role|sl_rbac_role_node|sl_rbac_role_user|sl_refund_reason|sl_seller_node'); //bool|array 表名
//这里是要绕过得表
$excludeTbArr = explode(
    '|',
    'yp_single_page_log'
);
$backupFilePath = dirname(__FILE__);

$dsn = "$type:host=$host;port=$port;dbname=$db_name;charset=$charset";

//sl_admin

try {
    //建立持久化的PDO连接
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_PERSISTENT=>true]);  
} catch (Exception $e) {
    die('连接数据库失败!'.$e);    
}

class Act{
    private static $pdo = null;
    public function __construct(){
        global $pdo;
        static::$pdo = &$pdo;
    }

    public function getTables($database){
        $sql = "SHOW TABLES FROM `$database`";
        $stmt = static::$pdo->query($sql);

        $arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $arr[] = $row["Tables_in_$database"];
        }

        return $arr;
    }

    public function exportDDL($database, $with_cmt=true, $is_alter_type=false, $utf8Type=false, $withData = false){
        global $excludeTbArr;
        $tables = $this->getTables($database);

        $strArr = [];
        $sql = $tmp = '';
        $stmt = $row = $lines = null;
        foreach ($tables as $key => $v) {
            if (in_array($v, $excludeTbArr)) {
                continue;
            }

            $sql = "SHOW CREATE TABLE `$v`";
            $stmt = static::$pdo->query($sql);

            $tableSql = '';
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tmp = $row['Create Table'];

            //remove index
            $tmp = preg_replace('#,[\s]+KEY[\s\S]+[)]\n#', "\n", $tmp);
            $tmp = preg_replace('#,[\s]+UNIQUE\sKEY[\s\S]+\n#', "\n", $tmp);


            if($with_cmt==false){
                $tmp = preg_replace("#\\sCOMMENT\\s'[^']+'#U", '', $tmp);
            }
            if($utf8Type === 'utf8'){
                $tmp = str_replace('CHARSET=utf8mb4', 'CHARSET=utf8', $tmp);
            } elseif($utf8Type === 'utf8mb4'){
                $tmp = str_replace('CHARSET=utf8 ', 'CHARSET=utf8mb4 ', $tmp);
            }

            if($is_alter_type){
                $tableSql = $tableSql . "-- 表的结构: $v --\n";
                $lines = explode("\n", $tmp);
                if(!empty($lines)){
                    $lines = array_slice($lines, 1, -1);
                }
                foreach ($lines as $lk => $lval) {
                    if(strpos($lval, ' KEY ')===false){
                        $tableSql = $tableSql . "ALTER TABLE `$v` " . trim($lval, ', ') .";\n";
                    }
                }

                $tableSql = $tableSql . "\n";
            }else{
                $tableSql = $tableSql . "-- 表的结构: $v --\n";
                $tableSql = $tableSql . "DROP TABLE IF EXISTS `$v`;\n";
                $tableSql = $tableSql . $tmp .";\n\n";
            }
            //reset AUTO_INCREMENT
            $tableSql = preg_replace("#\\sAUTO_INCREMENT=[0-9]+\\s#U", ' ', $tableSql);
            $strArr[$v] = $tableSql;

            if ($withData === true || in_array($v, $withData)) {
                $strArr[$v . '_insert_sql'] = $this->exportSql($v);
            }

            $stmt = null;
        }

        return $strArr;
    }

    public function exportSql($tableName)
    {
        $sql = "SELECT * FROM `$tableName`";
        $stmt = static::$pdo->query($sql);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->genInsertBatch($tableName, $rows, true);
    }

    private function genInsertBatch($tableName, $rows, $byReplace = false)
    {
        if (empty($rows)) {
            return false;
        }

        $fields = [];
        $row = reset($rows);
        foreach ($row as $field => $val) {
            $fields[] = "`$field`";
        }
        $insertSql = $byReplace ? 'REPLACE' : 'INSERT';
        $sql = "/**/ {$insertSql} INTO " . $tableName . " (" . implode(',', $fields) . ") VALUES ";

        foreach ($rows as $k => $row) {
            $rowStr = '';
            foreach ($row as $field => $val) {
                $rowStr .= sprintf("'%s',", addslashes(trim($val)));
            }
            $sql = $sql . sprintf('(%s),', substr($rowStr, 0, -1));
        }
        $sql = substr($sql, 0, strlen($sql) - 1) . ';';

        return $sql;
    }

    public function saveToFile($db_name, $with_cmt, $is_alter_type, $utf8Type, $withData, $dataType)
    {
        global $backupFilePath;
        $backupFileName = $db_name;
        $backupFile = $backupFilePath . '/' . $backupFileName . '.sql';
        $oldData = '';
        if (is_file($backupFile)) {
            $oldData = file_get_contents($backupFile);
        }
        $newData = $this->exportDDL($db_name, $with_cmt, $is_alter_type, $utf8Type, $withData);
        if ($dataType == 'array') {
            $newData = json_encode($newData, 256);
        } else {
            $newDataStr = '';
            foreach ($newData as $item) {
                $newDataStr .= $item;
            }
            $newData = $newDataStr;
        }
        
        if (md5($oldData) != md5($newData)) {
            //转存旧数据
            if (!empty($oldData)) {
                $oldBackupFile = $backupFilePath . '/' . $backupFileName . '_' . date('YmdHis') . '.sql';
                file_put_contents($oldBackupFile, $oldData);
            }
            file_put_contents($backupFile, $newData);
        }
    }
}

$act = new Act();
echo $act->saveToFile($db_name, $with_cmt, $is_alter_type, $utf8Type, $withData, $dataType);


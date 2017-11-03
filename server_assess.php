<?php  
// while(1){  
    exec('top -b -n 1 -d 3', $out); 
    // var_dump($out); 
    echo '<hr>';
    echo '<table border=1>';
    echo '<tr><th>系统当前时间 | 系统运行时间 </th><th> 当前系统登录用户数量 </th><th> 负载均衡 1分钟</th><th> 5分钟</th><th> 15分钟 </th></tr>';
    $outs = explode(',', $out[0]);
    echo '<tr>';
    foreach ($outs as $_out) {
    	echo '<td>'.$_out.'</td>';
    }
    echo '</tr>';
    echo '<tr><th>总的进程数 </th><th> 正在运行的进程数 </th><th> 挂起的进程数 </th><th> 停止的进程数 </th><th> 僵尸进程数 </th></tr>';
    $outs = explode(',', $out[1]);
    echo '<tr>';
    foreach ($outs as $_out) {
    	echo '<td>'.$_out.'</td>';
    }
    echo '</tr>';
    echo '<tr><th>用户空间占用CPU百分比</th><th>内核空间占用CPU百分比</th><th>用户空间内改变优先级的进程占用CPU百分比</th><th>空闲CPU百分比 </th><th>等待输入输出百分比 </th><th> CPU服务于硬件终端所消耗的CPU百分比 </th><th> CPU服务于软件终端所消耗的CPU百分比</th><th>steal Time </th></tr>';
    $outs = explode(',', $out[2]);
    echo '<tr>';
    foreach ($outs as $_out) {
    	echo '<td>'.$_out.'</td>';
    }
    echo '</tr>';
    echo '<tr><th>物理总内存 </th><th> 已使用物理内存 </th><th> 空闲内存量 </th><th> 缓冲区内存总量</th></tr>';
    $outs = explode(',', $out[3]);
    echo '<tr>';
    foreach ($outs as $_out) {
    	echo '<td>'.$_out.'</td>';
    }
    echo '</tr>';
    echo '<tr><th>交换区总量</th><th>已使用的交换区总量</th><th>空闲交换区总量 </th><th>缓冲的交换区总量</th></tr>';
    $outs = explode(',', $out[4]);
    echo '<tr>';
    foreach ($outs as $_out) {
    	echo '<td>'.$_out.'</td>';
    }
    echo '</tr>';
    echo '</table>';
    echo '<table border=1>';
    echo '<tr><th>进程ID</th>
    <th>进程的所用者</th>
    <th>优先级</th>
    <th>nice值</th>
    <th>进程使用的虚拟内存总量</th>
    <th>进程使用的未被换出的物理内存</th>
    <th>共享内存大小</th>
    <th>进程状态</th>
    <th>进程占用CPU百分比</th>
    <th>物理内存百分比</th>
    <th>进程使用CPU总时间</th>
    <th>命令行</th></tr>';
    
    for ($i=6;$i<99996;$i++) {
    	if (!isset($out[$i])) {
    		// echo $i.'------';
    		break;
    	}
    	echo '<tr>';
    	// $out[$i] = ltrim($out[$i], ' ');
    	$outs = explode(' ', $out[$i]);
    	$outs = array_filter($outs, function($v, $k){ return $v != '';}, ARRAY_FILTER_USE_BOTH);
    	foreach ($outs as $_out) {
    		echo '<td>'.$_out.'</td>';
    	}
    	echo '</tr>';
    }
    echo '</table>';
    /*
    $Cpu = explode('  ', $out[2]);  
    $Mem = explode('  ', $out[3]);  
    $Swap = explode('  ', $out[4]);  
    var_dump($Cpu,$Mem,$Swap);  
      
    $cpu = str_replace(array('%us,',' '),'',$Cpu[1]);  
    $mem = str_replace(array('k used,',' '),'',$Mem[2]);  
    $swap = str_replace(array('k cached',' '),'',$Swap[5]);  
    var_dump($cpu,$mem,$swap);*/
    // echo date('m d H').' '.$cpu.'    '.intval($mem/1024).'   '.intval($swap/1024).chr(10);  
    // sleep(10);  
// }  

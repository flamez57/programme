<?php  
// while(1){  
    exec('top -b -n 1 -d 3', $out); 
    // var_dump($out); 
    echo '<hr>';
    echo '系统当前时间 | 系统运行时间 | 当前系统登录用户数量 | 负载均衡 1分钟 5分钟 15分钟 <br>';
    echo $out[0].'<br>';
    echo '总的进程数 | 正在运行的进程数 | 挂起的进程数 | 停止的进程数 | 僵尸进程数 <br>';
    echo $out[1].'<br>';
    echo '用户空间占用CPU百分比 | 内核空间占用CPU百分比 | 用户空间内改变优先级的进程占用CPU百分比 | 空闲CPU百分比 | 等待输入输出百分比 | CPU服务于硬件终端所消耗的CPU百分比 | CPU服务于软件终端所消耗的CPU百分比 | steal Time <br>';
    echo $out[2].'<br>';
    echo '物理总内存 | 已使用物理内存 | 空闲内存量 | 缓冲区内存总量';
    echo $out[3].'<br>';
    echo '交换区总量 | 已使用的交换区总量 | 空闲交换区总量 | 缓冲的交换区总量';
    echo $out[4].'<br>';
    echo '进程ID | 进程的所用者 | 优先级 | nicc值 | 进程使用的虚拟内存总量 | 进程使用的未被换出的物理内存 | 共享内存大小 | 进程状态 | 进程占用CPU百分比 | 物理内存百分比 | 进程使用CPU总时间 | 命令行<br>';
    for ($i=6;$i<99996;$i++) {
    	if (!isset($out[$i])) {
    		// echo $i.'------';
    		break;
    	}
    	echo $out[$i].'<br>';
    }
    $Cpu = explode('  ', $out[2]);  
    $Mem = explode('  ', $out[3]);  
    $Swap = explode('  ', $out[4]);  
    var_dump($Cpu,$Mem,$Swap);  
      
    $cpu = str_replace(array('%us,',' '),'',$Cpu[1]);  
    $mem = str_replace(array('k used,',' '),'',$Mem[2]);  
    $swap = str_replace(array('k cached',' '),'',$Swap[5]);  
    var_dump($cpu,$mem,$swap);
    // echo date('m d H').' '.$cpu.'    '.intval($mem/1024).'   '.intval($swap/1024).chr(10);  
    // sleep(10);  
// }  

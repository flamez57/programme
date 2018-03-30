<?php  
header( 'Content-Type:text/html;charset=utf-8 ');
class HLCpu
{
    private $top;
    public $out;

    public function __construct()
    {
        exec('top -b -n 1 -d 3', $this->top);
    }

    public function top()
    {
        $outs = explode('load average:', $this->top[0]);
        $_tmp1 = explode(',', $outs[0]);
        array_pop($_tmp1);
        $_tmp2 = explode(', ', $outs[1]);
        $data = [
            ['name' => '当前系统登录用户数量', 'value' => (int) array_pop($_tmp1)],
            ['name' => '系统当前时间 | 系统运行时间', 'value' => implode('', $_tmp1)],
            ['name' => '负载均衡 1分钟', 'value' => $_tmp2[0]],
            ['name' => '负载均衡 5分钟', 'value' => $_tmp2[1]],
            ['name' => '负载均衡 15分钟', 'value' => $_tmp2[2]],
        ];
        $this->out['top'] = $data;
        return $data;
    }

    public function tasks()
    {
        $outs = explode(',', $this->top[1]);
        $data = [
            ['name' => '总的进程数', 'value' => (int) ltrim($outs[0], 'Tasks:')],
            ['name' => '正在运行的进程数', 'value' => (int) $outs[1]],
            ['name' => '挂起的进程数', 'value' => (int) $outs[2]],
            ['name' => '停止的进程数', 'value' => (int) $outs[3]],
            ['name' => '僵尸进程数', 'value' => (int) $outs[4]],
        ];
        $this->out['tasks'] = $data;
        return $data;
    }

    public function cpu()
    {
        $outs = explode(',', $this->top[2]);
        $data = [
            ['name' => '用户空间占用CPU百分比', 'value' => (float) ltrim($outs[0], 'Cpu(s):')],
            ['name' => '内核空间占用CPU百分比', 'value' => (float) $outs[1]],
            ['name' => '用户空间内改变优先级的进程占用CPU百分比', 'value' => (float) $outs[2]],
            ['name' => '空闲CPU百分比', 'value' => (float) $outs[3]],
            ['name' => '等待输入输出百分比', 'value' => (float) $outs[4]],
            ['name' => 'CPU服务于硬件终端所消耗的CPU百分比', 'value' => (float) $outs[5]],
            ['name' => 'CPU服务于软件终端所消耗的CPU百分比', 'value' => (float) $outs[6]],
            ['name' => 'steal Time', 'value' => (float) $outs[7]],
        ];
        $this->out['cpu'] = $data;
        return $data;
    }

    public function mem()
    {
        $outs = explode(',', $this->top[3]);
        $data = [
            ['name' => '物理总内存', 'value' => (int) ltrim($outs['0'], 'Mem:')],
            ['name' => '已使用物理内存', 'value' => (int) $outs['1']],
            ['name' => '空闲内存量', 'value' => (int) $outs['2']],
            ['name' => '缓冲区内存总量', 'value' => (int) $outs['3']],
        ];
        $this->out['mem'] = $data;
        return $data;
    }

    public function swap()
    {
        $outs = explode(',', $this->top[4]);
        $data = [
            ['name' => '交换区总量', 'value' => (int) ltrim($outs[0], 'Swap:')],
            ['name' => '已使用的交换区总量', 'value' => (int) $outs[1]],
            ['name' => '空闲交换区总量', 'value' => (int) $outs[2]],
            ['name' => '缓冲的交换区总量', 'value' => (int) $outs[3]],
        ];
        $this->out['swap'] = $data;
        return $data;
    }

    public function detail()
    {
        $chName = [
        '进程ID','进程的所用者','优先级','nice值','进程使用的虚拟内存总量','进程使用的未被换出的物理内存',
        '共享内存大小','进程状态','进程占用CPU百分比','物理内存百分比','进程使用CPU总时间','命令行'
        ];
        $enName = array_values(array_filter(explode(' ', $this->top[6])));
        foreach ($this->top as $_key => $_value) {
            if ($_key > 6) {
                $tmp = array_values(array_filter(explode(' ', $_value), function ($_v) {
                    if ($_v == '') {
                        return false;
                    } else {
                        return true;
                    }
                }));
                $data[] = array_map(function ($_chName, $_enName, $_tmp) {
                    $_data['ch_name'] = $_chName;
                    $_data['en_name'] = $_enName;
                    $_data['value'] = $_tmp;
                    return $_data;
                }, $chName, $enName, $tmp);
            }
        }
        $this->out['detail'] = $data;
        return $data;
    }

    public function all()
    {
        $this->top();
        $this->tasks();
        $this->cpu();
        $this->mem();
        $this->swap();
        $this->detail();
    }
}

$cpu = new HLCpu();
$cpu->all();
echo '<pre>';
print_r($cpu->out);
echo '</pre>';

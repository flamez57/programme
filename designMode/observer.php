<?php
//开启报错显示
ini_set("display_errors", "On");
error_reporting(E_ALL);
/**
 * 观察者模式应用场景实例
 * 存在问题原因主要是程序的"紧密耦合"，使用观察模式将目前的业务逻辑优化成"松耦合"，达到易维护、易修改的目的，
 * 同时也符合面向接口编程的思想。
 *
 * 观察者模式典型实现方式：
 * 1、定义2个接口：观察者（通知）接口、被观察者（主题）接口
 * 2、定义2个类，观察者对象实现观察者接口、主题类实现被观者接口
 * 3、主题类注册自己需要通知的观察者
 * 4、主题类某个业务逻辑发生时通知观察者对象，每个观察者执行自己的业务逻辑。
 * 韦小宝有七个老婆每个都有不同的要求，我们就看看他是否可以幸福生活呢
 * 示例：如以下代码
 *
 */
#===================定义观察者、被观察者接口============
//主体必须实现的接口 丈夫
interface Husband {
    public function attach(Wife $observer); //让妻子了解丈夫
    public function detach(Wife $observer); //拒绝妻子了解丈夫
    public function notify(); //丈夫的决定
}

//观察者必须实现的接口 妻子
interface Wife {
    public function mind(Husband $subject); //妻子争对丈夫的内心活动
}
#====================主题类实现========================
//主题 韦小宝的实现
class WeiXiaoBao implements Husband {
    public $money = 0;
    public $bub = 0;
    protected $wifeList = array();
    function __construct($money, $bub)
    {
        $this->money = $money;
        $this->bub = $bub;
    }
    public function attach(Wife $observer)
    {
        $this->wifeList[] = $observer;
    }
    public function detach(Wife $observer)
    {
        foreach ($this->wifeList as $key => $value) {
            if ($observer === $value) {
                unset($this->wifeList[$key]);
            }
        }
    }
    public function notify()
    {
        foreach ($this->wifeList as $value) {
            if (!$value->mind($this)) {
                return false;
            }
        }
        return true;
    }
    //幸福生活
    public function happyLife() {
        echo "我是韦小宝，我的生活是这样的：<br>";
        if (!$this->notify()) {
            echo '不幸福！';
        } else {
            echo '幸福！';
        }
    }
}
#====================观察者实现========================
//苏荃
class SuQuan implements Wife {
    public function mind(Husband $subject) {
        echo "我叫苏荃{$subject->money}";
        if ($subject->money >= 500) {
            echo "够我花好久了呢<br>";
            return true;
        } else {
            echo "都不够我塞牙<br>";
            return false;
        }
    }
}
//方怡
class FangYi implements Wife {
    public function mind(Husband $subject) {
        echo "我叫方怡{$subject->bub}";
        if ($subject->bub >= 18) {
            echo "CM 好威武<br>";
            return true;
        } else {
            echo "CM 不够看<br>";
            return false;
        }
    }
}
//沐剑屏、双儿、曾柔、建宁、阿珂 留给你来实现

$weiXiaoBao = new WeiXiaoBao('500', '15');
//苏荃作陪
$weiXiaoBao->attach(new SuQuan());
//苏荃作陪
$weiXiaoBao->attach(new FangYi());
//韦小宝的幸福生活
$weiXiaoBao->happyLife();


/*
 * 输入结果是这样的
我是韦小宝，我的生活是这样的：
我叫苏荃500够我花好久了呢
我叫方怡15CM 不够看
不幸福！
*/

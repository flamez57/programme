<?php

/**
** Class IDCardFilter
** 身份证号规则
** 15位: 省份(2位) + 地级市(2位) + 县级市(2位) + 出生年(2位) + 出生月(2位) + 出生日(2位) + 顺序号(3位)
** 18位: 省份(2位) + 地级市(2位) + 县级市(2位) + 出生年(4位) + 出生月(2位) + 出生日(2位) + 顺序号(3位) + 校验位(1位)
** 相比之下，18位 比 15位 多出生年 2位、校验位 1位。
** 其中，顺序号如果是偶数，则说明是女生，顺序号是奇数，则说明是男生。
*/
class IDCardFilter
{
    /*
     * 校验身份证号码正则
     */
    const REGX = '#(^\d{15}$)|(^\d{17}(\d|X)$)#';

    /**
    ** 检测是否是身份证号码
    ** @param  string $idCard
    ** @return boolean
    */
    public function isCardNumber($idCard)
    {
        return preg_match(self::REGX, $idCard);
    }

    /**
    ** 15位转18位
    ** @param  string $idCard
    ** @return void
    */
    public function fifteen2Eighteen($idCard)
    {
        if (strlen($idCard) != 15) {
            return $idCard;
        }
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        $code = array_search(substr($idCard, 12, 3), [996, 997, 998, 999]) !== false ? '18' : '19';

        $idCardBase = substr($idCard, 0, 6) . $code . substr($idCard, 6, 9);
        return $idCardBase . $this->genCode($idCardBase);
    }

    /**
    ** 生成校验码
    ** 有17位数字，分别是：7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2
    ** 分别用身份证的前 17 位乘以上面相应位置的数字，然后相加。
    ** 接着用相加的和对 11 取模。
    ** 用获得的值在下面 11 个字符里查找对应位置的字符，这个字符就是校验位。
    ** '1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'
    ** @param  string $idCardBase
    ** @return void
    */
    final protected function genCode($idCardBase)
    {
        $idCardLength = strlen($idCardBase);
        if ($idCardLength != 17) {
            return false;
        }
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $verifyNumbers = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $sum = 0;
        for ($i = 0; $i < $idCardLength; $i++) {
            $sum += substr($idCardBase, $i, 1) * $factor[$i];
        }
        $index = $sum % 11;
        return $verifyNumbers[$index];
    }

    protected $provinces = [
        11 => "北京", 12 => "天津", 13 => "河北",   14 => "山西", 15 => "内蒙古",
        21 => "辽宁", 22 => "吉林", 23 => "黑龙江", 31 => "上海", 32 => "江苏",
        33 => "浙江", 34 => "安徽", 35 => "福建",   36 => "江西", 37 => "山东", 41 => "河南",
        42 => "湖北", 43 => "湖南", 44 => "广东",   45 => "广西", 46 => "海南", 50 => "重庆",
        51 => "四川", 52 => "贵州", 53 => "云南",   54 => "西藏", 61 => "陕西", 62 => "甘肃",
        63 => "青海", 64 => "宁夏", 65 => "新疆",   71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外"
    ];

    /**
    ** 检查省份是否正确
    ** @param  strin $idCard
    ** @return void
    */
    public function checkProvince($idCard)
    {
        $provinceNumber = substr($idCard, 0, 2);
        return isset($this->provinces[$provinceNumber]);
    }

    /**
    ** 检测生日是否正确
    ** @param  strin $idCard
    ** @return void
    */
    public function checkBirthday($idCard)
    {
        $regx = '#^\d{6}(\d{4})(\d{2})(\d{2})\d{3}[0-9X]$#';
        if (!preg_match($regx, $idCard, $matches)) {
            return false;
        }
        array_shift($matches);
        list($year, $month, $day) = $matches;
        return checkdate($month, $day, $year);
    }

    /**
    ** 校验码比对
    ** @param  string $idCard
    ** @return void
    */
    public function checkCode($idCard)
    {
        $idCardBase = substr($idCard, 0, 17);
        $code = $this->genCode($idCardBase);
        return $idCard == ($idCardBase . $code);
    }

    /**
    ** 身份证号码校验
    ** @param  string $idCard
    ** @return bool
    */
    public function vaild($idCard)
    {
        // 基础的校验，校验身份证格式是否正确
        if (!$this->isCardNumber($idCard)) {
            return false;
        }

        // 将 15 位转换成 18 位
        $idCard = $this->fifteen2Eighteen($idCard);

        // 检查省是否存在
        if (!$this->checkProvince($idCard)) {
            return false;
        }

        // 检查生日是否正确
        if (!$this->checkBirthday($idCard)) {
            return false;
        }

        // 检查校验码
        return $this->checkCode($idCard);
    }
}

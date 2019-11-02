<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/1
 * Time: 22:10
 * use 
   $p = $_GET['p'];
   $pageSize = 10;
   $count = 10000;
   $param = []; // 携带参数
   $page = new Page('Course/content', $p, $pageSize, $count, $param);
   $show = $page->show(7);
 */


class Flamez57Page
{
    private $url;
    private $currentPage;
    private $limit;
    private $total;
    private $option = '';

    /*
     * @param $url string 跳转连接
     * @param $currentPage int 当前页
     * @param $limit int 页容量
     * @param $total int 总条数
     * @param $option array 携带参数
     */
    public function __construct($url, $currentPage, $limit, $total, $option = [])
    {
        $this->url = $url;
        $this->currentPage = $currentPage;
        $this->limit = $limit;
        $this->total = $total;
        if (!empty($option) && is_array($option)) {
            foreach ($option as $_opk => $_op) {
                $this->option .= "&{$_opk}={$_op}";
            }
        }
    }

    //显示分页
    public function show($type = 0)
    {
        $css = 'css'.$type;
        $str = $this->$css();
        $str .= $this->page($type);
        return $str;
    }

    private function page($type = 0)
    {
        if ($this->total <= $this->limit) {
            return '';
        }
        if ($this->limit < 1) {
            $this->limit = 1;
        }
        $pages = ceil($this->total / $this->limit);
        //前一页
        if ($this->currentPage > 1) {
            $str = '<li><a href="'.$this->url.'?p='.($this->currentPage - 1).$this->option.'">«</a></li>';
        } else {
            $str = '<li><a href="#">«</a></li>';
        }

        //中间页
        if ($pages < 7) {
            for ($i = 1; $i <= $pages; $i++) {
                if ($i == $this->currentPage) {
                    $str .= '<li><a class="active" href="'.$this->url.'?p='.$i.$this->option.'">'.$i.'</a></li>';
                } else {
                    $str .= '<li><a href="'.$this->url.'?p='.$i.$this->option.'">'.$i.'</a></li>';
                }
            }
        } else {
            $start = $this->currentPage - 3;
            if ($start < 1) {
                $end = $this->currentPage + 3 - $start;
                $start = 1;
            } else {
                $end = $this->currentPage + 3;
                if ($end > $pages) {
                    $start = $this->currentPage - 3 - ($end - $pages);
                    $end = $pages;
                }
            }
            for ($i = $start; $i <= $end; $i++) {
                if ($i == $this->currentPage) {
                    $str .= '<li><a class="active" href="'.$this->url.'?p='.$i.$this->option.'">'.$i.'</a></li>';
                } else {
                    $str .= '<li><a href="'.$this->url.'?p='.$i.$this->option.'">'.$i.'</a></li>';
                }
            }
        }

        //下一页
        if ($this->currentPage < $pages) {
            $str .= '<li><a href="'.$this->url.'?p='.($this->currentPage + 1).$this->option.'">»</a></li>';
        } else {
            $str .= '<li><a href="#">»</a></li>';
        }
        $style = 'pagination'.$type;
        $page = <<<PAGE
<ul class="{$style}">
    {$str}
</ul>
PAGE;
        return $page;
    }

    private function css0()
    {
        $css =<<<CSS
<style>
    ul.pagination0{
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    
    ul.pagination0 li {display: inline}
    ul.pagination0 li a{
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
    }
</style>
CSS;
        return $css;
    }

    private function css1()
    {
        $css =<<<CSS
<style>
    ul.pagination1{
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    
    ul.pagination1 li{display: inline;}
    ul.pagination1 li a{
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
    }
    
    
    ul.pagination1 li a.active{
        background-color: #4CAF50;
        color: white;
    }
    
    ul.pagination1 li a:hover:not(.active){
        background-color: #dddddd;
    }
</style>
CSS;
        return $css;
    }

    private function css2()
    {
        $css = <<<CSS
<style>
    ul.pagination2{
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    
    ul.pagination2 li {
        display: inline;
    }
    
    ul.pagination2 li a {
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 5px;
    }
    
    ul.pagination2 li a.active{
        background-color: #4CAF50;
        color: white;
        border-radius: 5px;
    }
    
    ul.pagination2 li a:hover:not(.active){
        background-color: #dddddd;
    }
</style>
CSS;
        return $css;
    }

    private function css3()
    {
        $css = <<<CSS
<style>
    ul.pagination3{
        padding: 0;
        margin: 0;
        display: inline-block;
    }
    
    ul.pagination3 li{
        display: inline;
    }
    
    ul.pagination3 li a{
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 1.0s;
    }
    
    
    ul.pagination3 li a.active{
        background-color: #4CAF50;
        color: white;
    }
    
    ul.pagination3 li a:hover:not(.active){
        background-color: #dddddd;
    }
</style>
CSS;
        return $css;
    }

    private function css4()
    {
        $css = <<<CSS
<style>
    ul.pagination4{
        padding: 0;
        margin: 0;
        display: inline-block;
    }
    
    ul.pagination4 li{
        display: inline;
    }
    
    ul.pagination4 li a{
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        transition: background-color 1.0s;
        border: 1px solid #dddddd;
    }
    
    ul.pagination4 li a.active{
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }
    
    ul.pagination4 li a:hover:not(.active){
        background-color: #777777;
    }
</style>
CSS;
        return $css;
    }

    private function css5()
    {
        $css = <<<CSS
<style>
    ul.pagination5{
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    
    ul.pagination5 li {
        display: inline;
    }
    
    ul.pagination5 li a{
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        transition: background-color 1.0s;
        border: 1px solid #dddddd;
    }
    
    ul.pagination5 li:first-child a{
        border-top-left-radius: 5px;
        border-bottom-left-radius: 5px;
    }
    
    ul.pagination5 li:last-child a{
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
    }
    
    ul.pagination5 li a.active{
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }
    
    ul.pagination5 li a:hover:not(.active){
        background-color: #777777;
    }
</style>
CSS;
        return $css;
    }

    private function css6()
    {
        $css = <<<CSS
<style>
    ul.pagination6{
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    
    ul.pagination6 li{
        display: inline;
    }
    
    ul.pagination6 li a{
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        transition: background-color 1.0s;
        border: 1px solid #777777;
        margin: 0 4px;
    }
    
    ul.pagination6 li a.active{
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }
    
    ul.pagination6 li a:hover:not(.active){
        background-color: #777777;
    }
</style>
CSS;
        return $css;
    }

    private function css7()
    {
        $css = <<<CSS
<style>
    ul.pagination7 {
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    
    ul.pagination7 li {display: inline;}
    
    ul.pagination7 li a {
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        transition: background-color .3s;
        border: 1px solid #ddd;
        font-size: 22px;
    }
    
    ul.pagination7 li a.active {
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }
    
    ul.pagination7 li a:hover:not(.active) {background-color: #ddd;}
</style>
CSS;
        return $css;
    }

    private function css8()
    {
        $css = <<<CSS
<style>
    ul.pagination8 {
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    
    ul.pagination8 li {display: inline;}
    
    ul.pagination8 li a {
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        transition: background-color .3s;
        border: 1px solid #ddd;
    }
    
    ul.pagination8 li a.active {
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }
    
    ul.pagination8 li a:hover:not(.active) {background-color: #ddd;}
</style>
CSS;
        return $css;
    }

    private function css9()
    {
        $css = <<<CSS
<style>
    ul.pagination9 {
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    
    ul.pagination9 li {display: inline;}
    
    ul.pagination9 li a {
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        transition: background-color .3s;
        border: 1px solid #ddd;
        font-size: 18px;
    }
    
    ul.pagination9 li a.active {
        background-color: #eee;
        color: black;
        border: 1px solid #ddd;
    }
    
    ul.pagination9 li a:hover:not(.active) {background-color: #ddd;}
</style>
CSS;
        return $css;
    }
}

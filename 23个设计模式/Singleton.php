<?php
class Singleton{
    private static $instance;       //不让直接调用
    private $count = 0;

    private function __construct(){  //不能直接使用 new 创建
        echo "this is singleton class\n";
    }

    public static function getInstance(){
        if(! (self::$instance instanceof self)){
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function getCount(){
        echo ++$this->count, "\n";
    }
}

$instance = Singleton::getInstance();  //只能静态调用
$instance = Singleton::getInstance();
$instance = Singleton::getInstance();
$instance->getCount();
$instance->getCount();
$instance->getCount();
$instance->getCount();


/***
 * Output:
 * this is singleton class     只打印了一次证明只初始化了一次
 * 1     数字一直增加，证明是同一个对象
 * 2
 * 3
 * 4
 */

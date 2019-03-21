<?php
class Ioc{
    public function make(string $className){
        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();
        $params = [];

        if(! empty($constructor)){
            $initParamsRef = $constructor->getParameters();

            foreach($initParamsRef as $k => $v){
                if($v->getClass() instanceof ReflectionClass){
                    $params[] = $this->make($v->getClass()->name);
                } else{
                    if($v->isOptional()){  //有默认值的参数
                        $params[] = $v->getDefaultValue();
                    } else{   //没有默认值的参数
                        $null = '';
                        $paramType = $v->getType()->getName();
                        $params[] = settype($null, $paramType);
                    }
                }
            }
        }

        $instance = $reflectionClass->newInstanceArgs($params);
        return $instance;
    }
}

class People{
    private $name = 'jackx';
    private $sex = 'male';
    private $favorite;

    //这里使用依赖注入的方式得到 Sex 对象
    public function __construct(Sex $sex, string $name, $favorite='water'){
        echo "start create people\n";
        $this->sex = $sex->title;
        $this->name = empty($name) ? $this->name : $name;
        $this->favorite = $favorite;
    }

    public function __set(string $property, $value){
        if(property_exists($this, $property)){
            $this->$property = $value;
        }

        return $this;
    }

    public function __get(string $property){
        if(property_exists($this, $property)){
            return $this->$property;
        }

        return;
    }
}

class Sex{
    public $title = 'male';
    public function __construct(){}
}

//普通调用方式
$sex = new Sex;
$sex->title = 'man';
$people = new People($sex, 'xdc');
printf("Name:%s\tSex:%s\tFavorite:%s\n", $people->name, $people->sex, $people->favorite);

//依赖注入调用方式，如果依赖层数很多，这种方式的优势就提现出来了
$container = new Ioc();
$people = $container->make(People::class);
printf("Name:%s\tSex:%s\tFavorite:%s\n", $people->name, $people->sex, $people->favorite);


## 依赖注入DI，主要使用IOC的方式。
* DI:Dependency Injection
* IOC:Inversion of Controll

## 目的：将调用者与被调用者分离，也就是解耦

> 主要是需要一个IOC容器，所有对象的创建都使用IOC容器来操作，当需要某个对象时，就去创建它的实例。这样对象直接只有依赖关系，代码变得更清晰，没有复杂的对象穿插。

## 实现方式
[IOC代码](code/Ioc.php)

普通方式：
```
class People{
    private $name = 'jackx';
    private $sex = 'male';

    public function __construct(Sex $sex){
        echo "start create people\n";
        $this->sex = $sex->title;
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

$sex = new Sex;
$sex->title = 'man';
$people = new People($sex);
printf("Name:%s\tSex:%s\n", $people->name, $people->sex);
```


依赖注入方式（添加IOC容器）：
```
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


$container = new Ioc();
$people = $container->make(People::class);    //这样生成对象生成的对象会自己去解决依赖关系对象
printf("Name:%s\tSex:%s\n", $people->name, $people->sex);
```

Laravel 中的使用：
````
//通过DI创建对象
public function make($abstract, array $parameters = [])
    {
        ……
        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->make($concrete, $parameters);
        }
        ……
        $this->resolved[$abstract] = true;

        return $object;
    }

//解决依赖关系，自动实例化被依赖的对象
 public function build($concrete, array $parameters = [])
    {
        ……
        $reflector = new ReflectionClass($concrete);
      ……
        $constructor = $reflector->getConstructor();
      ……
        $dependencies = $constructor->getParameters();

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.
        $parameters = $this->keyParametersByArgument(
            $dependencies, $parameters
        );

        $instances = $this->getDependencies(
            $dependencies, $parameters
        );

        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($instances);
    }
```

让Laravel完全使用DI的部分
```
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
```

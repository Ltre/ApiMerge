# ApiMerge
Merge API with PHP

示例API
```php
class ApiDemo {
    function api1(){
        return 'this is api1';
    }
    
    function api2($a, $b){
        return compact('a', 'b');
    }
    
    function api3($content){
        file_put_contents(DI_LOG_PATH.'log.api3.txt', $content);//do something
    }
}

```
测试用例
```php
import('ApiMerge');

class ApiMergeTest {
    static function test1(){ //已实现
        $am = new ApiMerge();
        $am->invoke(array('ApiDemo/api1','ApiDemo/api2'=>array('a1', 'b2')), function($A1, $A2){
            dump(compact('A1', 'A2'));
        });
        $am->invoke(array('ApiDemo/api3'=>array('this is log')));
    }
    static function test2(){ //已实现
        $am = new ApiMerge();
        $am->define('mypack', function(){
            echo 'this is mypack<br>';
        });
        $am->invoke(array('mypack'), function(){
            echo 'this is callback with mypack<br>';
        });
    }
    static function test3(){ //已实现
        $am = new ApiMerge();
        $am->define('mypack', function($p1){
            echo 'this is mypack and param<p1> is '.$p1.'<br>';
            return 'ret of mypack';
        });
        $am->invoke(array('mypack'=>array('p1_value')), function($M){
            echo 'this is callback with mypack<br>';
            echo 'parameter<M> is in callback：';
            echo $M;
        });
    }
    static function test4(){ //已实现
        $am = new ApiMerge();
        $results = $am->invoke(array(
            'ApiDemo/api1' => array(),
            'ApiDemo/api2' => array('a', 'b'),
            'ApiDemo/api3' => array('contents')
        ), function($A1, $A2, $A3){
            dump(compact('A1', 'A2', 'A3'));
        });
        dump($results);
    }
    static function test5(){ //已实现
        $am = new ApiMerge();
        $am->config(array(
            'base' => 'ApiDemo',
            'alias' => array(
                'fooAlia' => 'ApiDemo/api3'
            ),
        ));
        $results = $am->invoke(array(
            'api1' => array(),
            'api2' => array('a', 'b'),
            'fooAlia' => array('contents')
        ), function($A1, $A2, $A3){
            dump(compact('A1', 'A2', 'A3'));
        });
        dump($results);
    }
}

ApiMergeTest::test5();
```

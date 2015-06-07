# ApiMerge
Merge API with PHP

实例API
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
    static function test1(){ //实现中
        $am = new ApiMerge();
        $am->import(array('ApiDemo/api1','ApiDemo/api2'), function($A1, $A2){
            dump(compact('A1', 'A2'));
        });
    }
    static function test2(){ //实现中
        $am = new ApiMerge();
        $am->define('mypack', function($require, $export){
            $LIB = $require('lib/mylib');
            $export['foo'] = $LIB->foo();
        });
        $am->import(array('mypack'), function($M){
            dump($M);
        });
    }
    static function test3(){ //实现中
        $am = new ApiMerge();
        $am->define('mypack', function($require, $export, $module){
            $LIB = $require('lib/mylib');
            $module['export'] = array(
                'foo' => $LIB->foo(),
                'bar' => $LIB->bar(),
            );
        });
        $am->import(array('mypack'), function($M){
            dump($M);
        });
        dump($am);
    }
    static function test4(){ //已实现
        $am = new ApiMerge();
        $results = $am->import(array(
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
        $results = $am->import(array(
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

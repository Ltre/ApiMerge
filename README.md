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
    static function test1(){
        $am = new ApiMerge();
        $am->invoke(array('ApiDemo/api1','ApiDemo/api2'=>array('a1', 'b2')), function($A1, $A2){
            dump(compact('A1', 'A2'));
        });
        $am->invoke(array('ApiDemo/api3'=>array('this is log')));
    }
    static function test2(){
        $am = new ApiMerge();
        $am->define('mypack', function(){
            echo 'this is mypack<br>';
        });
        $am->invoke(array('mypack'), function(){
            echo 'this is callback with mypack<br>';
        });
    }
    static function test3(){
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
    static function test4(){
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
    static function test5(){
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
    static function test6(){
        $am = new ApiMerge();
        $am->config(array(
        	'base' => 'ApiDemo',
            'alias' => array(
                'foo' => 'a/b/c/d'
            )
        ));
        $am->define('a/b/c/d', function($p) use ($am) {
            echo "this is $p <br>";
            return $am->invoke('api1');
        });
        $am->invoke(array('foo'=>'valueP'));
    }
    static function test7(){ //客户端原始请求
        if (! arg('flag')) {
            $script = '$.get("./?test/apiMerge",{reqList:{req1:[1,2,3],req2:["a","b","c"],req3:null}, flag:1},function(j){},"jsonp")';
            $html = '<!DOCTYPE html><head><script src="http://cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script><script>function sendMulti(){'.$script.'}</script></head><body><button onclick="sendMulti()">发送数据</button></body></html>';
            exit($html);
        }
        $am = new ApiMerge();
        $am->define('req1', function($p1,$p2,$p3){return array('p1'=>++$p1,'p2'=>++$p2,'p3'=>++$p3);});
        $am->define('req2', function($pa,$pb,$pc){return $pa.$pb.$pc;});
        $am->define('req3', function(){return !!rand(0, 1);});
        $util = function($callback) use ($am) {
            $reqList = arg('reqList');
            foreach ($reqList as &$r) { $r = (array)$r; }
            return $am->invoke($reqList, $callback);
        };
        @$ret += $util(function($R1,$R2,$R3) use (&$ret){
            $ret['sha1'] = sha1(serialize($R1).serialize($R2).serialize($R3));
            $ret['md5'] = md5(serialize($R1).serialize($R2).serialize($R3));
        });
        var_dump($ret);
    }
    static function test8(){ //封装客户端原始请求
        if (! arg('flag')) {
            $script = '$.get("./?test/apiMerge",{reqList:{req1:[1,2,3],req2:["a","b","c"],req3:null}, flag:1},function(j){},"jsonp")';
            $html = '<!DOCTYPE html><head><script src="http://cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script><script>function sendMulti(){'.$script.'}</script></head><body><button onclick="sendMulti()">发送数据</button></body></html>';
            exit($html);
        }
        $am = new ApiMerge();
        $am->define('req1', function($p1,$p2,$p3){return array('p1'=>++$p1,'p2'=>++$p2,'p3'=>++$p3);});
        $am->define('req2', function($pa,$pb,$pc){return $pa.$pb.$pc;});
        $am->define('req3', function(){return !!rand(0, 1);});
        @$ret += $am->easyInvoke(function($R1,$R2,$R3) use (&$ret){
            $ret['sha1'] = sha1(serialize($R1).serialize($R2).serialize($R3));
            $ret['md5'] = md5(serialize($R1).serialize($R2).serialize($R3));
        });
        var_dump($ret);
    }
}

ApiMergeTest::test8();
```

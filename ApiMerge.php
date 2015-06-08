<?php
/**
 * 用于合并API调用的工具
 * @author Ltre<ltrele@yeah.net>
 * @since 2015-06-04
 */
class ApiMerge extends ApiMergeGlue {
    function define($id, $cmd){
        return call_user_func_array($this->_invoker_define, func_get_args());
    }
    function import(array $apis, Closure $callback){
        return call_user_func_array($this->_invoker_import, func_get_args());
    }
    function config(array $conf){
        call_user_func_array($this->_invoker_config, func_get_args());
    }
}


class ApiMergeBase {
    //模块池，使用后期静态绑定，以便子节点的操作结果存储在父节点
    static public $pool = array();
    static public $base = '';
    static public $alias = array();
}


//封装模块
class ApiMergeDefine extends ApiMergeBase {
    public $require = null;
    public $export = null;
    
    private function _setRequire(){
        $this->require = function(){};
    }
    
    private function _setExport(){
        $this->export = function(){};
    }
    
    public function __construct(){
        $this->_setRequire();
        $this->_setExport();
    }
    
    public function define($id, $cmd){
        echo 'ApiMergeDefine::define()<br>';
        static::$pool[$id] = $cmd;
    }
}


//引用模块
class ApiMergeImport extends ApiMergeBase {
    public $apis = array();
    public $result = array();
    
    public function import($apis, $callback){
    	echo 'ApiMergeImport::import()<br>';
    	$results = array();
    	foreach ($apis as $id => $a) {
    	    $id = $this->_getRealID($id);
    	    $callee = $this->_getCallee($id);
    	    $results[$id] = call_user_func_array($callee, $a);
    	}
    	call_user_func_array($callback, $results);
    	return $results;
    }
    //取实际ID的优先级：alias > base
    private function _getRealID($id){
        foreach (static::$alias as $k => $v) {
            if ($k == $id) return $v;
        }
        return static::$base ? (static::$base.'/'.$id) : $id;
    }
    //获取需要回调的callable
    private function _getCallee($id){
        @$callee = static::$pool[$id];
        $inPool = $callee instanceof Closure;
        if (! $inPool) {
            $ex = explode('/', $id);
            $callee = array(new $ex[0], $ex[1]);
        }
        return $callee;
    }
}


//配置模块共有属性
class ApiMergeConfig extends ApiMergeBase {
    public function config($conf){
        echo 'ApiMergeConfig::config()<br>';
        @static::$base = $conf['base'] ?: '';
        @static::$alias = $conf['alias'] ?: array();
    }
}


//合并模块
class ApiMergeGlue extends ApiMergeBase {
    private $_names = array('define', 'import', 'config');
    
    private function _init(){
        static::$pool = array();
        static::$base = '';
        static::$alias = array();
    }
    
    private function _combine(){
        foreach ($this->_names as $n) {
            $class = 'ApiMerge'.ucfirst($n);
            $o = new $class();
            foreach ($o as $i => $e) { $this->$i = $e; }
            $this->{'_invoker_'.$n} = function() use ($o, $n) {
                return call_user_func_array(array($o, $n), func_get_args());
            };
        }
    }
    
    //在重新初始化组合对象ApiMerge时，清空所有静态属性
    public function __construct(){
        $this->_init();
        $this->_combine();
    }
}





//示例API
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
//测试用例
class ApiMergeTest {
    static function test1(){
        $am = new ApiMerge();
        $am->import(array('ApiDemo/api1','ApiDemo/api2'), function($A1, $A2){
            dump(compact('A1', 'A2'));
        });
    }
    static function test2(){
        $am = new ApiMerge();
        $am->define('mypack', function($require, $export){
            $LIB = $require('lib/mylib');
            $export['foo'] = $LIB->foo();
        });
        $am->import(array('mypack'), function($M){
            dump($M);
        });
    }
    static function test3(){
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

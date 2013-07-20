<?php
/**
 * Singleton design pattern implementation for PHP5
 * http://snipplr.com/view/1982/php5-singleton-design-pattern/
 */
class apex{
    public static $loaded = array();
    public static $controller;
    public static $start_time;
    public static $expires = 0;
    public static $event_log = array();
    public static $modules = null;
	public static $rendered;

    public static function lib($libname){return self::loader('lib', $libname);}
    public static function model($modelname){return self::loader('model', $modelname, 'model');}
    public static function deprecated_ui($type, $id){return self::ui_loader($type,$id);}
    
    public static function go(){
		hook('before_go');
		self::$start_time = microtime_float(); 

		self::$modules = @lib('config')->modules;

		$page_render = '';
		$cacheName = md5(lib('uri')->_selfURL());
		$cacheFile = APP.'/cache/html/'.$cacheName;

		if(file_exists($cacheFile)){
			$file = file_get_contents ($cacheFile); 
			list($expiry,$file) = explode('###apex_expiry###',$file);
			if(self::$start_time<$expiry){
				hook('using_page_cache');
				$page_render = $file;
			}
		}
		
		if($page_render == ''){
			hook('rendering_page');
			lib('user');
			
			if (lib('uri')->show404==false){
			
				$controller_path = lib('uri')->controllerPath;
				$controller_name = lib('uri')->controller;
				$method_name = lib('uri')->method;
				self::$controller = self::loader('controller',$controller_path);
		
				if(method_exists(self::$controller, $method_name)){
					call_user_func_array(array(self::$controller, $method_name), lib('uri')->args);
				}elseif(method_exists(self::$controller, '_default')){
					array_unshift(lib('uri')->args,$method_name);
					call_user_func_array(array(self::$controller, '_default'), lib('uri')->args);
				}else{
					self::show404();
				}
			}else{
				// lib uri did not locate a controller
				self::show404();
			}
	
			if ( lib('page')->autorender==true ) {
				$page_render = lib('page')->render();
				if(self::$expires>0){
					$handle = fopen($cacheFile, 'w+');
					fwrite($handle, (self::$start_time+(self::$expires*60)).'###apex_expiry###'.$page_render);//$myxml is given by Flash LoadVars
					fclose($handle);
				}
			}
		}
		
		if(strrpos ( $page_render , '<ui' ) > -1 ){
			$page_render = lib('page')->ui($page_render);
		}
		
        $timer = floor((microtime_float() - self::$start_time)*1000)/1000;
        $mem = floor((memory_get_usage()/1024/1024)*1000)/1000;
        $peakmem = floor((memory_get_peak_usage()/1024/1024)*1000)/1000;
        $rendered = str_replace(array('{elapsed_time}','{mem_usage}'),array($timer.' Sec', $mem.'Mb ('.$peakmem.'Mb)'),$page_render);
	
	self::$rendered = $rendered;
	hook('before_display');
	print self::$rendered;
	hook('after_display');
	
	if(@$_GET['debug']==true){
	    ?><pre style="direction:ltr;"><?
	    print_r(apex::$event_log);
	}
    }
    
    public static function loader($type, $path, $wrapper=''){
	$name = explode('/',$path);
	$name = $name[sizeof($name)-1];

	if(@!array_key_exists( $path, self::$loaded[$type] )){
		flexiLoader ( $type,$path );
		$classname = $name.'_'.$type;
		if($wrapper!=''){
			self::$loaded[ $type ][ $name ] = new $wrapper;
			self::$loaded[ $type ][ $name ]->inner = new $classname;
		}else{
			self::$loaded[ $type ][ $name ] = new $classname;
		}
	}
	return self::$loaded[ $type ][ $name ];
    }

    function debug(){
	    ?><pre><?
	    print_r(self::$loaded);
    }

    public static function show404(){
	flexiloader('error','404');
    }
    
    public static function modules(){
	if(self::$modules==null){
	    $modules = array();
	    $dir = APP.'/modules/';
	    if ($handle = opendir($dir)) {
		    while (false !== ($file = readdir($handle))) {
			    if($file!='.' && $file!='..' && is_dir($dir.$file)){
				    $modules[] = $dir.$file;
			    }
		    }
		    closedir($handle);
	    }
	    array_unshift ( $modules, APP );
	    array_push ( $modules, SYS );
	    self::$modules = $modules;
	}
	return self::$modules;
    }
    
    public final function __clone(){trigger_error( "Cannot clone instance of Singleton pattern", E_USER_ERROR );}
}

function event($message){apex::$event_log[]=microtime_float().' : '.$message;}
function ui($type){$className = $type.'_ui'; if(!class_exists($className, false)){flexiLoader ( 'ui', $type );} return new $className;}
function lib($libID){return apex::lib($libID);}
function model($modelID){return apex::model($modelID);}
function controller(){return apex::$controller;}

function hook($name){
    if(@apex::$loaded['lib']){
		foreach(apex::$loaded['lib'] as $lib){
			$hook_fn_name = "hook_$name";
			if ( method_exists($lib, $hook_fn_name) )
			$lib->$hook_fn_name();
		}
    }
    lib('hooks')->$name();
}


function view( $view, $params=array(), $return=false ){
    ob_start();
    extract($params);
    require flexiPath($view.'.php');
    if($return==false){
	print ob_get_clean();
    }else{
	return ob_get_clean();
    }
}


function __autoload($className){
    if ( substr($className,0,6 ) == 'Model_'){
		$nm = strtolower(substr($className,6));
		if(!file_exists(flexiPath('fuses/'.$nm.'.php'))){
			eval("class $className extends fuse{};");
			return false;
		}else{
			flexiLoader('fuse',$nm);
			return true;
		}
	}

    if(!flexiLoader('prototype', $className)){
	    $trc = debug_backtrace();
	    die("class <b>$className</b> not found in File: <b>{$trc[0]['file']}</b> Line: <b>{$trc[0]['line']}</b>. The class definition should be located at <b>".APP."/prototypes/{$className}.php</b>");
    }
}

function flexiPath($path){
    foreach( APEX::modules() as $module ){
	if( file_exists($module.'/'.$path) ){
	    return $module.'/'.$path;
	}
    }
    return $path;
}

function flexiLoader($type, $name){
    $path = flexiPath("{$type}s/{$name}.php");
    if( file_exists($path) ){
	include $path;
	return true;
    }else{
	echo("file <b>{$type}s/{$name}.php</b> not found");
	?><pre><?
	print_r(debug_backtrace());
	?></pre><?
	die();
    }
}

function redirect($where, $fullURL = false){
    lib('user')->perpetuate();
    if(!$fullURL){
	$where = lib('uri')->base_url.$where;
    }
    header('Location: '.$where);
    die();
}

function microtime_float (){ 
    list ($msec, $sec) = explode(' ', microtime()); 
    $microtime = (float)$msec + (float)$sec; 
    return $microtime; 
} 

function rand_string($lenth = 8){ 
    // makes a random alpha numeric string of a given lenth 
    $aZ09 = array_merge(range('A', 'Z'), range('a', 'z'),range(0, 9)); 
    $out =''; 
    for($c=0;$c < $lenth;$c++){ 
       $out .= $aZ09[mt_rand(0,count($aZ09)-1)]; 
    } 
    return $out; 
} 

function cache($expires=0){
    apex::$expires = $expires;
}

function prep_alt($txt){
    $txt = str_replace('"','``',$txt);
    return $txt;
}

function array_to_object($array = array()) {
    //http://www.lost-in-code.com/programming/php-code/php-array-to-object/
    if (!empty($array)) {
        $data = false;

        foreach ($array as $akey => $aval) {
			if( is_array($aval) ){
				$aval = array_to_object($aval);
			}
            $data -> {$akey} = $aval;
        }

        return $data;
    }

    return false;
}

function object_to_array($data){
    // http://codesnippets.joyent.com/posts/show/1641
    if(is_array($data) || is_object($data)){
	$result = array(); 
	foreach($data as $key => $value){
	    $result[$key] = object_to_array($value); 
	}
	return $result;
    }
    return $data;
}

class F{
    static function __callStatic($name, $args){
	$name = explode( '_',$name );
	$helper = array_shift( $name );
	$name = implode( '_',$name );
	if( !function_exists ($name) ){
	    flexiLoader('helper', $helper);
	}
	if( $name!='' ){
	    return call_user_func_array ( $name, $args );
	}
    }
}

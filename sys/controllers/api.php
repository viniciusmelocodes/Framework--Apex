<?
class api_controller{
    function __construct(){
        lib('page')->autorender = false;
    }

    function index(){
        // ALL MODELS LIST - API FRONT PAGE
        ?><h1>Available API Models</h1><?
        if ($handle = opendir(APP.'/models')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $model_class = str_replace('.php','',$file);
                    model($model_class);
                    $reflector = new ReflectionClass($model_class.'_model');
    
                    if($reflector->hasProperty('api')){
                        ?><h2>api/<?=$model_class?></h2><?
                        ?><pre><?=$reflector->getDocComment()?></pre><?
                        if($reflector->getProperty('api')==true){
                            foreach($reflector->getMethods() as $method){
                                ?>
                                <div style="background-color:#def;padding:20px;margin-bottom:20px;">
                                    <h3 style="margin:0;">
                                        <span style="color:#aaa;"><?=lib('uri')->controller?>/<?=$model_class?>/</span><?=$method->name?><?
                                            $params = array();
                                            foreach($method->getParameters() as $param){
                                                $params[] = '@'.$param->name.'';
                                            }
                                            ?><span style="color:#00a;font-size:70%;">/<?=implode ('/',$params)?></span>
                                    </h3>
                                    <hr/>
                                    <pre><?=$method->getDocComment()?></pre>
                                </div>
                                <?
                                //print_r($method);
                                //print_r($method->getParameters());
                            }
                        }
                    }
                    
                    
                    //echo "$file\n";
                }
            }
            closedir($handle);
        }
    }
    
    function _default($model,$method=''){
        if($method==''){apex::show404();}

        $args = lib('uri')->args;
        array_shift($args);
        array_shift($args);

        if(file_exists(APP.'/models/'.$model.'.php')){
            if(@model($model)->inner->api==true){
                try{
                    $rt = call_user_func_array(array(model($model), $method), $args);
                }catch(Exception $e){
                    $rt = $e->getMessage();
                }
                print json_encode( $rt );
            }else{
                apex::show404();
            }
        }else{
            apex::show404();
        }
    }

}
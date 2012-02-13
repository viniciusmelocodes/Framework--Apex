<?
// ===========================================================
// DO NOT UPLOAD THIS FILE!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// This file will probably be different on the dev machine and
// on the server, don't forget!
// ===========================================================
date_default_timezone_set ("Asia/Jerusalem");

class config_lib{
    public $home_controller = 'home';
    public $views_directory = 'controllers';
    public $views_directory_suffix = '.views';
    public $user_lib_cookie = 'apex';

    public $encryption_key = 'Gr50yEm.uE6uOnhD[&pJ8!%KiR|NCe>4';
    
    // to hard code module loading (and order), use the following line:
    // apex::$modules = array(APP, APP.'/modules/<module_name>', SYS);
    // otherwise, autoloader is in effect
    public $modules = null;

    // defines the trigger constant name and a default value (null means required) ex: 'USER'=>null, 'LANG'=>'hebrew', ...etc
    public $url_triggers = array();
    
    // defines exceptions to the trigger rule, controllers and paths that do not require a trigger. ex: 'admin','login', ...etc
    public $url_triggers_exceptions = array(); 
    
    public $db = array(
        'driver' => 'sqlite',
        'path' => 'db',
        'prefix' => '',
        'frozen' => false
    );
/*
    public $db = array(
        'driver' => 'mysql',
        'host' => 'localhost',
        'dbName' => '<db name>',
        'username' => 'root',
        'password' => '',
        'prefix' => '',
        'frozen' => false
    );
*/
}

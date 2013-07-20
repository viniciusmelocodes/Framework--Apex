<?
require_once SYS."/libs/db/3.4.7/rb.php";
initialize_database();

function initialize_database(){
	//http://forums.hostgator.com/mysql-password-hashing-php-5-2-t117816.html
	$cfg = lib('config')->db;
	
	switch($cfg['driver']){
		case 'mysql':
			R::setup('mysql:host='.$cfg['host'].';dbname='.$cfg['dbName'],$cfg['username'],$cfg['password']);
			break;
		case 'sqlite':
			$path = explode('/',str_replace('\\','/',__DIR__.'\\..\\..\\'.$cfg['path']));
			array_shift($path);
			$path = implode('/',$path);
			R::setup('sqlite:/'.$path);
			break;
	}
	//$this->prefix = $cfg['prefix'];
	
	if($cfg['frozen']==true){
		R::freeze(true);
	}
}

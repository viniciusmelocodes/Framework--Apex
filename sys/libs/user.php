<?
class user_lib{
    private $current_growl = '';
	private $cookie=null;

    function __construct(){
		session_start();
        $this->current_growl = @$_SESSION['growl'];
        $_SESSION['growl'] = '';
    }
    
    function __get($name){
		if($this->cookie==null){
            if(isset($_COOKIE[lib('config')->user_lib_cookie])){
				try{
					$this->cookie = @unserialize(lib('encryption')->decrypt($_COOKIE[lib('config')->user_lib_cookie]));
					if($this->cookie == '') $this->cookie = array();
				}catch(Exception $e){
					$this->cookie = array();
				}
            }else{
                $this->cookie = array();
            } 
		}
        $rt = @$this->cookie[$name];
		//$this->perpetuate();
        return $rt;
    }
    
	function array_set($obj){
		foreach($obj as $key=>$item){
			$this->$key = $item;
		}
		//$this->perpetuate();
	}
	
    function __set($name, $value){
		$this->cookie[$name] = $value;
		//$this->perpetuate();
    }
    
    function growl($message=''){
        if($message!=''){
            $_SESSION['growl'] .= $message;
        }else{
            return $this->current_growl;
        }
    }
    
    function destroy(){
        foreach($_COOKIE as $cookie=>$value){
            setcookie($cookie, null, time()-31536000, '/');
        }
        foreach($_SESSION as $key=>$session){
            unset($_SESSION[$key]);
        }
        //$_COOKIE = null;
        //$_SESSION = null;
		@session_destroy();
    }

    function hook_before_display(){
	$this->perpetuate();
    }

    function perpetuate(){
	@setcookie(lib('config')->user_lib_cookie, lib('encryption')->encrypt(serialize($this->cookie)), time()+31536000, '/');
    }
}
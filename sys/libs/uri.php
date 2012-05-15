<?
class uri_lib{
	public $segments = array();
	public $uri = null;
	public $basePath;
	public $controller;
	public $method;
	public $args;
	public $querystring;
	public $base_url;
	public $domain;
	
	public $show404 = false;
	
	function here(){
		return $this->basePath.$this->controller.'/'.$this->method.'/';
	}
	
	function __construct(){
		$this->base_url = $this->_baseUrl();
		$this->_get_uri();
		$this->_get_elements();
		$this->domain = $_SERVER['HTTP_HOST'];
	}
	
	function _get_elements(){
		$segments = $this->segments;
		$args = array();

		$finished = false;
		while(sizeof($segments)>0 && $finished==false){
			$path = implode('/',$segments);
			if(!file_exists(flexiPath('/controllers/'.$path.'.php'))){
				array_unshift($args, array_pop($segments));
			}else{
				$finished = true;
			}
		}

		if($finished!=true){$this->show404=true;return;}//apex::show404();
		
		$this->controllerPath = $path;
		$this->basePath = $segments; array_pop($this->basePath); $this->basePath = implode('/',$this->basePath);if($this->basePath!=''){$this->basePath.='/';}
		$this->controller = $segments[sizeof($segments)-1];
		
		$this->method = array_shift($args);
		if($this->method==''){$this->method='index';}

		$this->args = $args;
	}
	
	function segment($segID){
		return $this->segments[$segID];
	}
	
	function _get_uri(){
		$home = lib('config')->home_controller;
		
		$this->uri = str_replace($this->base_url,'',$this->_selfURL());
		$exploded = explode('?',$this->uri);
		$this->uri = $exploded[0];
		@$this->querystring = $exploded[1];

		$this->uri = explode('/',$this->uri);

		if (!is_array(@lib('config')->url_triggers)){lib('config')->url_triggers=array();}
		if (!is_array(@lib('config')->url_triggers_exceptions)){lib('config')->url_triggers_exceptions=array();}
		
		if(!in_array($this->uri[0], lib('config')->url_triggers_exceptions)){

			foreach( lib('config')->url_triggers as $key=>$value ){
				$trigger_value = array_shift($this->uri);
				if( $trigger_value == '' ){
					if($value!=null){
						$trigger_value = $value;
					}else{
						$this->show404 = true;
						//apex::show404();
					}
				}
				define ( $key, $trigger_value );
			}
		}
		
		
		$this->uri = implode('/',$this->uri);
		
		$this->uri = trim($this->uri,'/');
		if($this->uri==''){$this->uri = $home.'/index';}
		if(file_exists(flexiPath('/controllers/'.$this->uri.'/'.$home.'.php'))){$this->uri .= '/'.$home;}
		$this->segments = explode('/',$this->uri);
	}

	function _selfURL() {
		$s = empty($_SERVER["HTTPS"]) ? ''
			: ($_SERVER["HTTPS"] == "on") ? "s"
			: "";
		$protocol = $this->_strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
			: (":".$_SERVER["SERVER_PORT"]);
		$rt = $protocol."://".$_SERVER['HTTP_HOST'].$port.$_SERVER['REQUEST_URI'];
		return $rt;
	}

	function _strleft($s1, $s2) {
		return substr($s1, 0, strpos($s1, $s2));
	}

	function _baseUrl(){
		$burl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
		$burl .= "://".$_SERVER['HTTP_HOST'];
		$burl .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
		return $burl;
	}

}
<?
class resources_lib{
    public $css;
    public $js;
    
    public $crunch_css_date = 0;
    public $crunch_js_date = 0;
    
    public $crunch_css = true;
    public $crunch_js = true;

    function tags(){
        ?>
        <link rel="stylesheet/less" href="resources/crunched.less" type="text/css" media="screen, print"/>
        <script type="text/javascript" src="resources/crunched.js"></script>
        <?
    }

    function __construct(){
        if(file_exists('resources/crunched.less')){
            $this->crunch_css_date = filemtime ( 'resources/crunched.less' );
        }else{
            $this->crunch_css = true;
        }

        if(file_exists('resources/crunched.js')){
            $this->crunch_js_date = filemtime ( 'resources/crunched.js' );
        }else{
            $this->crunch_js = true;
        }
    }

    function hook_before_display(){

        if($this->crunch_css==true){
            ob_start();
            foreach($this->css as $css){
                include $css;
                ?>


<?
            }
            file_put_contents ( 'resources/crunched.less', ob_get_clean() );
        }

        if($this->crunch_js==true){
            ob_start();
            foreach($this->js as $js){
                include $js;
                ?>


<?
            }
            file_put_contents ( 'resources/crunched.js', ob_get_clean() );
        }

    }

    function add_file($full_path){
        $file = basename($full_path);
        $explode = explode('.',$file);
        $extention = array_pop($explode);
        $filename = implode('.',$explode);
        
        switch($extention){
            case 'js':
                if ( filemtime ( $full_path ) > $this->crunch_js_date ){
                    $this->crunch_js = true;
                }
                $this->js[] = $full_path;
                break;
            case 'css':
            case 'less':
                if ( filemtime ( $full_path ) > $this->crunch_css_date ){
                    $this->crunch_css = true;
                }
                $this->css[] = $full_path;
                break;
        }
    }

    function auto($resources_folder){
        foreach( lib('fs')->dir("resources/$resources_folder") as $file){
            $full_path = "resources/$resources_folder/$file";
            $this->add_file($full_path);
        } 
    }
    
    function generate_tag($full_path){
        $file = basename($full_path);
        //@list($filename, $extention) = explode('.',$file);
        $explode = explode('.',$file);
        $extention = array_pop($explode);
        $filename = implode('.',$explode);
        
        if($filename[0]=='_')return;
        
        switch($extention){
            case 'js':
                ?>
        <script src="<?=$full_path?>"></script>
<?
                break;
            case 'css':
                ?>
        <link href="<?=$full_path?>" media="screen, print" rel="stylesheet" type="text/css" />
<?
                break;
            case 'less':
                ?>
        <link rel="stylesheet/less" href="<?=$full_path?>" type="text/css" media="screen, print"/>
<?
                break;
        }
    }
}
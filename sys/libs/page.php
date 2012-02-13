<?php
class page_lib {
    public $skin = 'default';
    public $template = '';
    public $data = array();
    public $autorender = true;

    function __construct(){
        $this->templatePath = lib('config')->views_directory.'/'.lib('uri')->basePath.lib('uri')->controller.lib('config')->views_directory_suffix.'/';
        $this->template = lib('uri')->method;
    }
    
    function render(){

        ob_start();
        $template = flexiPath($this->templatePath.$this->template.'.php');
        $templatePath = $this->templatePath;
        extract($this->data);
        if( $this->skin != '' ){
            require flexiPath('skins/'.$this->skin.'.php');
        }else{
            require flexiPath($template);
        }
        return ob_get_clean();
    }

    function ui($str){
        $rendered = lib('htmldom')->str_get_html($str);


        foreach($rendered->find('ui') as $uiElement){
            $obj = ui($uiElement->type);
            foreach($uiElement->attr as $attr=>$val){
                $obj->$attr = $val;
            }
            if(@$obj->id==''){
                $uiElement->outertext = '<b>Apex UI element of type '.$obj->type.' is missing the ID property.</b>';
            }else{
                $obj->inner = $uiElement->innertext;
                ob_start();
                $obj->render();
                $uiElement->outertext = ob_get_clean();
            }
        }

        return $rendered;
    }

}
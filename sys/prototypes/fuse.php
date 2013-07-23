<?
class fuse extends RedBean_SimpleModel{
    private $new_bean = false;
 
    function open(){}

    function dispense(){
        $this->uid = rand_string(16);
        $this->created = microtime(true);
        $this->new_bean = true;
    }

    function update(){
        $this->modified = microtime(true);
        if($this->new_bean == true){
            $this->created_by = @lib('user')->id;
        }
        $this->modified = microtime(true);
        $this->modified_by = @lib('user')->id;
    }
    
    function sanitize_for_api_call(){
        foreach($this->bean as $property=>$value){
            if(is_object($this->bean->$property)){ $this->bean->$property->sanitize_for_api_call(); }
            if($property == 'password' || $property == 'id' || substr($property, -3)=='_id'){ unset ($this->bean->$property);}
        }
    }
}
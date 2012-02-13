<?
class base_model{
    function __construct(){
        
    }
    
    function by_id($id){
        return lib('db')->find($this->classname, 'id='.$id)->row();
    }

    function by_uid($uid){
        return lib('db')->find($this->classname, 'uid="'.$uid.'"')->row();
    }
    
    function where($where=''){
        return lib('db')->find($this->classname, $where)->result();
    }

    function row($where=''){
        return lib('db')->find($this->classname, $where)->row();
    }

}
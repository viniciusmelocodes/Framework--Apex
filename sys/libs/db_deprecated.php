<?
class db_lib{
    function __construct(){
        include('db/1.0/redbean.inc.php');

        $cfg = lib('config')->db;

        switch($cfg['driver']){
            case 'mysql':
                $toolbox = RedBean_Setup::kickstartDev('mysql:host='.$cfg['host'].';dbname='.$cfg['dbName'], $cfg['username'], $cfg['password']);
                break;
            case 'sqlite':
                $toolbox = RedBean_Setup::kickstartDevL( 'sqlite:/'.$this->_fullPath().'/'.$cfg['path'] );
                break;
        }
        $this->prefix = $cfg['prefix'];

        $this->rb = $toolbox->getRedBean();
        $this->adapter = $toolbox->getDatabaseAdapter();
        
        if($cfg['frozen']==true){
            $this->rb->freeze(true);
        }
    }
    
    function _fullPath(){
        $path = explode('/',$_SERVER['SCRIPT_FILENAME']);
        array_shift($path);
        array_pop($path);
        $path = implode('/',$path);
        return $path;
    }
    
    function dispense($type){
        event('lib_db dispense '.$type);
        $x = $this->rb->dispense($this->prefix.$type);
        $x->created = time();
        $x->created_by = lib('user')->id;
        $x->uid = rand_string(8);
        return $x;
    }

    function from_post($type, $id=''){
        $type = $this->prefix.$type;
        if($id=='') {$x = $this->dispense($type);}
        else        {$x = $this->rb->load($type,$id);}

        foreach($_POST as $key=>$value){
            if($key[0]!='_'){$x->$key = $value;}
        }
        return $x;
    }

    function store($obj){
        event('lib_db STORE');
        $obj->modified = time();
        $obj->modified_by = lib('user')->id;
        $x = $this->rb->store($obj);
        return $x;
    }

    function getInsertID(){
        return $this->adapter->getInsertID();
    }

    function exec($sql){return $this->adapter->exec($sql);}
    function get($sql){return $this->adapter->get($sql);}
    function trash($obj){return $this->rb->trash($obj);}
    function destroy($obj){/*this function is an alias of the 'trash' function, for convenience only*/return $this->trash($obj);}
    function load($type,$id){
        //return $this->rb->load($type,$id);
        return $this->find($type, 'id="'.$id.'"')->row();
    }

    function find($type, $sql='id>0'){
        event('lib_db Find '.$type.' :: '.$sql);
        if($sql==''){$sql='id>0';}
        try{
            $rt = Finder::where($this->prefix.$type,$sql,array());
        }catch(Exception $e){
            $rt = array();
        }

        return new apexDB_return($rt);
    }
}


class apexDB_return{
    private $record;
    
    function __construct($rt){
        $this->record = $rt;
    }
    
    function count(){
        return sizeof($this->record);
    }
    
    function row(){
        if($this->record){
            return array_shift($this->record);
        }else{
            return false;
        }
    }
    
    function result(){
        if($this->record){
            return $this->record;
        }else{
            return array();
        }
    }
}
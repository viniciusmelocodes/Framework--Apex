<?
class db_lib{
    function __construct(){
        include('db/2.0.1/rb.php');

        $cfg = lib('config')->db;

        switch($cfg['driver']){
            case 'mysql':
                R::setup('mysql:host='.$cfg['host'].';dbname='.$cfg['dbName'],$cfg['username'],$cfg['password']);
                break;
            case 'sqlite':
                R::setup('sqlite:/'.$this->_fullPath().'/'.$cfg['path'] );
                break;
        }
        $this->prefix = $cfg['prefix'];

        if($cfg['frozen']==true){
            R::freeze(true);
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
        $x = R::dispense($this->prefix.$type);
        $x->created = microtime(true);
        $x->uid = rand_string(16);
        $x->created_by = lib('user')->id;
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
        $obj->modified = microtime(true);
        $obj->modified_by = lib('user')->id;
        $x = R::store($obj);
        return $x;
    }

    function getInsertID(){
        return R::getInsertID();
    }

    function exec($sql){return R::exec($sql);}
    function get($sql){return R::get($sql);}
    function trash($obj){return R::trash($obj);}
    function destroy($obj){/*this function is an alias of the 'trash' function, for convenience only*/return $this->trash($obj);}
    function load($type,$id){
        //return $this->rb->load($type,$id);
        return $this->find($type, 'id="'.$id.'"')->row();
    }

    function find($type, $sql=''){
        event('lib_db Find '.$type.' :: '.$sql);
        if($sql==''){$sql='id>0';}
        return new apexDB_return($type, $sql);
    }

    function deprecated_find($type, $sql='id>0'){
        event('lib_db Find '.$type.' :: '.$sql);
        if($sql==''){$sql='id>0';}
        try{
            $rt = R::find($this->prefix.$type,$sql,array());
        }catch(Exception $e){
            $rt = array();
        }

        return new apexDB_return($rt);
    }
}


class apexDB_return{
    public $type;
    public $sql;
    private $recordset;

    function __construct($type, $sql){
        $this->type = $type;
        $this->sql = $sql;
        $this->prefix = lib('db')->prefix;
    }

    function count(){
        $fullSQL = 'select count(id) as count from '.$this->prefix.$this->type.' where '.$this->sql;
        $count = R::getCell($fullSQL);
        if(!$count)$count = 0;
        return $count;
    }

    function row(){
        $this->affirm_recordset();
        if($this->recordset){
            return array_shift($this->recordset);
        }else{
            return false;
        }
    }

    function result(){
        $this->affirm_recordset();
        return $this->recordset;
    }
    
    function affirm_recordset(){
        if(!$this->recordset){
            try{
                $this->recordset = R::find($this->prefix.$this->type,$this->sql,array());
            }catch(Exception $e){
                $this->recordset = array();
            }
        }
    }
}

class deprecated_apexDB_return{
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
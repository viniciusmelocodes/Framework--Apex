<?
/*
SAMPLE USAGE:
================================================================================
IN CONTROLLER:

        $lala = new repeater('lala');
        $lala->base = 'home/index';
        $lala->binding = 'posts';
        $lala->page_size = 3;
        //$lala->where = '';
        $lala->upload_path = 'resources/uploads';
        $lala->validator = function($fields){
            $rt = true;
            if( strlen($fields['name'])<=2 ){
                conditional('name_too_short_alert');
                $rt = false;
            }
            if( !is_numeric($fields['phone']) ){
                conditional('phone_not_numeric_alert');
                $rt = false;
            }
            return $rt;
        };
        $lala->callback =  function($obj){ return $obj; };
        $lala->post_store =  function($obj){ return $obj; };
        $lala->view_permissions =  function($obj){ return true; };
        $lala->add_permissions =  function(){ return true; };
        $lala->modify_permissions = function($obj){ return ($obj->gender==2); };
        $lala->delete_permissions = function($obj){ return !($obj->is_cool); };

================================================================================
IN VIEW:

<ui type="repeater" id="lala">
    <empty>
        Sorry, no records found. <a class="r_add">Make a new one</a>.
    </empty>
    
    <before>
        <table>
            <tr>
                <th>id</th>
                <th>name</th>
                <th>phone</th>
                <th>gender</th>
                <th>is_cool</th>
                <th></th>
            </tr>
    </before>

    <row>
        <tr>
            <td><!=$row->id!></td>
            <td><a class="r_modify" alt="<!=$row->name!>"><!=$row->name!></a></td>
            <td><!=$row->phone!></td>
            <td><!=$row->gender!></td>
            <td><!=$row->is_cool!></td>
            <td><a class="r_delete" alt="This guy is cool go away">Delete</a></td>
        </tr>
    </row>

    <after>
        </table>
        <div>{paging}</div>
        <a class="r_add" alt="No one wants you to add them.">Add New</a>
    </after>
    
    <paging>
        <a class="r_first">First</a>
        <a class="r_prev">Prev</a>
        <a class="r_link">{id}</a>
        <a class="r_current">{id}</a>
        <a class="r_next">Next</a>
        <a class="r_last">Last</a>
    </paging>

    <form style="border:1px solid #f00;" class="form_class">
        
        <div class="r_conditional" id="name_too_short_alert">Name too short.</div>
        <div class="r_conditional" id="phone_not_numeric_alert">Phone not numeric.</div>

        name <input name="name"/><br/>
        phone <input name="phone"/><br/>
        gender <select name="gender"><option selected="selected" value="1">Male</option><option value="2">Female</option></select><br/>
        <input type="checkbox" name="is_cool"/> is_cool<br/>
        <hr/>
        My Story<br/>
        <textarea name="story"></textarea><br/>
        <hr/>
        <input type="checkbox" name="mujumbu[]" value="halo"/>Halo<br/>
        <input type="checkbox" name="mujumbu[]" value="mister"/>Mister<br/>
        <input type="checkbox" name="mujumbu[]" value="how"/>How<br/>
        <input type="checkbox" name="mujumbu[]" value="do"/>Do<br/>
        <input type="checkbox" name="mujumbu[]" value="you"/>You<br/>
        <input type="checkbox" name="mujumbu[]" value="do?"/>Do?<br/>
        <hr/>
        <input type="radio" name="rad" value="big"/>big<br/>
        <input type="radio" name="rad" value="in"/>in<br/>
        <input type="radio" name="rad" value="japan"/>japan<br/>
        <hr/>
        <!=$item_obj->just_a_file!> <input type="file" name="just_a_file"/>
        <hr/>
        <a class="r_cancel">Cancel</a> <input type="submit"/>
    </form>
</ui>

*/

class repeater{
    public $id;
    public $conditional=array();
    public $upload_path = 'uploads';
    
    function __construct($id){
        $this->id = $id;
        lib('page')->data[$id] = $this;
    }
    
    function __get($key){
        if(!isset(lib('page')->data[$id]->$key)) die("Property $key is not set in $this->id");
    }
    
    function __set($key, $value){
        $this->$key = $value;
    }

    public function conditional($id){
        
    }
    
    public function __call($name, $arguments) {
        // calling dynamically created function:
        // from: http://dot-php.blogspot.com/2010/03/dynamically-adding-methods-in-php-base.html
        if(isset($this->$name)===true) {
            $q = $this->$name;

            $nArgCount = count($arguments);
            $aArgTokens = array();
            for ($i=0; $i<$nArgCount; $i++) {
                $aArgTokens[] = '$arguments['.$i.']';
            }
            $sArgTokens = implode(',', $aArgTokens);

            $sEval = 'return $q('.$sArgTokens.');';  
            return eval($sEval); 
        } else {
            //throw new Exception("No registered method called ".__CLASS__."::".$name);
            return (function(){return true;});
        }
    }
}

function conditional($condition){
    // this function uses backtrace
    $q = debug_backtrace ();
    $q = $q[3];
    $q = $q['object']->id;
    @lib('page')->data[$q]->conditional[$condition] = true;
}
<?
class repeater_ui{
    function __construct(){
    }
    
    function render(){
        if(!isset(lib('page')->data[$this->id])){
            die('Controller missing the repeater: '.$this->id);
        }
        $this->logic = lib('page')->data[$this->id];
        if( !isset($this->logic->binding) ) $this->logic->binding = $this->logic->id;
        
        $state = @lib('uri')->segment ( sizeof( explode('/',$this->logic->base) ) );
        $item_id = @lib('uri')->segment ( sizeof( explode('/',$this->logic->base) ) +1 );
        $item_obj = lib('db')->find($this->logic->binding, 'uid="'.$item_id.'"')->row();


        $dom = lib('htmldom')->str_get_dom($this->inner);
        
        $where = (isset($this->logic->where))?$this->logic->where:'id>0';
        
        switch($state){



            case '':
            case 'page':
                if ( !$this->logic->view_permissions($item_obj) ){return;}
                
                if( isset($this->logic->page_size) ){
                    $page_size = $this->logic->page_size;
                    $page_id = $item_id;
                    $offset = $page_size * $page_id;
                    if($page_id == '')$page_id = 0;
                    
                    $page_count = lib('db')->find($this->logic->binding, $where)->count() / $page_size;
                    if ( $page_count != floor( $page_count ) ){
                        $page_count = floor( $page_count ) + 1;
                    }
                    $page_count -= 1;
                    $where .= " limit $page_size offset $offset";
                }

                $records = lib('db')->find($this->logic->binding, $where);
                
                if($records->count()==0){
                    if($dom->find('empty',0)){
                        eval ($this->evalprep($dom->find('empty',0),'',true));
                    }
                }else{
                    ob_start();
                    if($dom->find('before',0)){
                        eval ($this->evalprep($dom->find('before',0),'',true));
                    }
                    $count = 0;
                    foreach($records->result() as $row){
                        $count++;
                        eval ($this->evalprep($dom->find('row',0),$row,true));
                    }
                    if($dom->find('after',0)){
                        eval ($this->evalprep($dom->find('after',0),'',true));
                    }
                    $grid = ob_get_clean();
                    
                    if( isset($this->logic->page_size) ){
                        $paging_dom = $dom->find('paging',0);
                        ob_start();
                        if ( $page_id > 0 ){
                            $first_link = $paging_dom->find('.r_first',0);
                            $first_link->href = $this->logic->base;
                            print $first_link;

                            $prev_link = $paging_dom->find('.r_prev',0);
                            $prev_link->href = $this->logic->base.'/page/'.($page_id-1);
                            print $prev_link;
                        }
                        
                        for ( $f = 0; $f<=$page_count; $f++ ){
                            if ( $page_id != $f ){
                                $lnk = $paging_dom->find('.r_link',0);
                                $lnk->href = $this->logic->base.'/page/'.$f;
                                print str_replace('{id}',$f+1,$lnk);
                            }else{
                                $lnk = $paging_dom->find('.r_current',0);
                                print str_replace('{id}',$f+1,$lnk);
                            }
                        }
                        
                        if( $page_id < $page_count ){
                            $next_link = $paging_dom->find('.r_next',0);
                            $next_link->href = $this->logic->base.'/page/'.($page_id+1);
                            print $next_link;
                            
                            $last_link = $paging_dom->find('.r_last',0);
                            $last_link->href = $this->logic->base.'/page/'.$page_count;
                            print $last_link;
                        }

                        $paging = ob_get_clean();
                    }
                    
                    print str_replace('{paging}', @$paging, $grid);
                }
                break;




            case 'add':
                if ( !$this->logic->add_permissions() ){return;}

                $form = $dom->find('form',0);

                foreach($form->find('add,modify') as $tag){
                    if($tag->tag=='add'){
                        $tag->outertext = $tag->innertext;
                    }else{
                        $tag->outertext = '';
                    }
                }

                if ( $form == '' ) $form = lib('htmldom')->str_get_dom( '<form></form>' );

                $form->enctype="multipart/form-data";
                $form->method = 'post';
                $form->action = $this->logic->base.'/add';

                $form = lib('htmldom')->str_get_html( ''.$form );

                if(!$_POST){
                    if(!$form){
                        print '<b>Form</b> element is missing in repeater <b>'.$this->id.'</b>';
                        break;
                    }else{
                        @eval ($this->evalprep($form));
                    }
                }else{
                    if($this->logic->validator($_POST)==true){
                        // insert $_POST to database
                        $insert = lib('db')->dispense($this->logic->binding);
                        $insert = $this->form_to_db($form, $insert, $_POST);
                        if(isset($this->logic->callback)){
                            $insert = $this->logic->callback($insert);
                        }
                        $insert->uid = strtolower(rand_string(16));
                        if($insert){
                            lib('db')->store($insert);
                        }
                        $insert = $this->logic->post_store($insert);
                        redirect($this->logic->base);
                    }else{
                        // populate form with $_POST and
                        //print_r($_POST);die();
                        $form = $this->populate_form_from_array($form, $_POST);
                        //  re-display
                        //print($form);die();
                        @eval ($this->evalprep($form));
                    }
                }
                break;



            case 'modify':
                if ( !$this->logic->modify_permissions($item_obj) ){return;}
                $form = $dom->find('form',0);

                foreach($form->find('add,modify') as $tag){
                    if($tag->tag=='modify'){
                        $tag->outertext = $tag->innertext;
                    }else{
                        $tag->outertext = '';
                    }
                }
                
                $form->enctype="multipart/form-data";
                $form->method = 'post';
                $form->action = $this->logic->base.'/modify/'.$item_id;
                
                $form = lib('htmldom')->str_get_html( ''.$form );

                if(!$item_obj){
                    apex::show404();
                }else{
                    if(!$_POST){
                        $form = $this->populate_form_from_array($form, object_to_array($item_obj) );
                        eval ($this->evalprep($form, $item_obj));
                    }else{
                        if($this->logic->validator($_POST)==true){
                            $item_obj = $this->form_to_db($form, $item_obj, $_POST);
                            $item_obj = $this->logic->callback($item_obj);
                            if($item_obj){
                                lib('db')->store($item_obj);
                            }
                            $item_obj = $this->logic->post_store($item_obj);
                            redirect($this->logic->base);
                        }else{
                            // populate form with $_POST and
                            $form = $this->populate_form_from_array($form, $_POST);
                            //  re-display
                            eval ($this->evalprep($form));
                            
                        }
                    }
                }
                break;
            
            case 'delete':
                if ( !$this->logic->delete_permissions($item_obj) ){return;}
                lib('db')->destroy($item_obj);
                redirect($this->logic->base);
                break;


            default:
                apex::show404();
                break;
        }
    }
    
    function form_to_db($form, $obj, $values){
        //print_r($values);
        //print_r($_POST);
        foreach( $form->find('input,textarea,select') as $field){
            $field_name = $field->name;
            if($field_name[0]!='_'){

                switch( $field->tag ){
                    case 'textarea':
                        $obj->$field_name = $values[$field_name];
                        break;
                    case 'select':
                        $obj->$field_name = $values[$field_name];
                        break;
                    case 'input':
                        switch( $field->type ){
                            case 'text':
                            case 'radio':
                            case 'hidden':
                            case '':
                                $obj->$field_name = $values[$field_name];
                                break;
                            case 'checkbox':
                                if( substr($field_name, -2)=='[]' || sizeof($form->find('[name='.$field_name.']')) > 1 ){
                                    // handle multi checkbox
                                    $field_name = str_replace('[]','',$field_name);
                                    // the following line does happen multiple times
                                    // but it beats the alternative of using complex logic
                                    $obj->$field_name = implode(',',$values[$field_name]);
                                }else{
                                    // handle single checkbox
                                    if( @$values[$field_name] ){
                                        $obj->$field_name = true;
                                    }else{
                                        $obj->$field_name = false;
                                    }
                                }
                                break;
                            case 'file':
                                if( $_FILES[$field_name]['name']!='' ){
                                    if(!is_dir($this->logic->upload_path)){
                                        mkdir($this->logic->upload_path , 0777, true);
                                    }
                                    
                                    $ctr = null;
                                    $target_name = basename( $_FILES[$field_name]['name']);
    
                                    while( file_exists('./'.$this->logic->upload_path .'/'. $this->numify($target_name,$ctr)) ){
                                        $ctr++;
                                    }
                                    $_FILES[$field_name]['name'] = $this->numify($target_name,$ctr);
                                    $obj->$field_name = $_FILES[$field_name]['name'];
                    
                                    $target_path = $this->logic->upload_path .'/'. basename( $_FILES[$field_name]['name'] );
                    
                                    if(move_uploaded_file($_FILES[$field_name]['tmp_name'], $target_path)) {
                                    } else {
                                    }
                                }
                                break;
                        }
                        break;
                }

                
            }

        }
        return $obj;
    }

    function populate_form_from_array($form, $array){
        foreach( $form->find('input,textarea,select') as $field){
            switch( $field->tag ){
                case 'input':
                    switch( $field->type ){
                        case 'text':
                        case 'hidden':
                        case '':
                            $field->value = @$array[$field->name];
                            break;
                        case 'checkbox':
                            if( substr($field->name, -2)=='[]' || sizeof($form->find('[name='.$field->name.']')) > 1 ){
                                // handle multiple checkboxes
                                $name_in_array = str_replace('[]','',$field->name);

                                if(is_string(@$array[$name_in_array])){
                                    $multiple_values = @explode(',',$array[$name_in_array]);
                                    if(is_string($multiple_values)) $multiple_values = array($multiple_values);
                                }else{
                                    $multiple_values = @$array[$name_in_array];
                                }

                                if(!$multiple_values)$multiple_values=array();
                                if (in_array($field->value,$multiple_values)){
                                    $field->checked = 'checked';
                                }
                            }else{
                                // handle single checkboxes
                                if( @$array[$field->name] ){
                                    $field->checked = 'checked';
                                }
                            }
                            break;
                        case 'radio':
                            if( $field->value == @$array[$field->name] ){
                                $field->checked = 'checked';
                            }
                            break;
                    }
                    break;
                case 'textarea':
                    $field->innertext = $array[$field->name];
                    break;
                case 'select':
                    foreach($field->find('option') as $option){
                        $option->selected = null;
                    }
                    $field->find('option[value='.@$array[$field->name].']',0)->selected = 'selected';
                    break;
            }
        }
        return $form;
    }
    
    function add_link(){
        return $this->logic->base.'/add';
    }
    
    function modify_link(){
        return $this->logic->base.'/modify/{id}';
    }
    
    function cancel_link(){
        return $this->logic->base;
    }
    
    function delete_link(){
        return $this->logic->base.'/delete/{id}';
    }
    
    function evalprep($original_codeblock, $obj='', $inner=false){
        if($inner==true) {
            $codeblock = lib('htmldom')->str_get_html(''.$original_codeblock->innertext());
        }else{
            $codeblock = lib('htmldom')->str_get_html(''.$original_codeblock);
        }

        if($obj==''){
            $obj = new black_hole();
        }

        foreach(array('add','modify','delete') as $action){
            $fn_name = $action.'_permissions';
            $ln_name = $action.'_link';
            foreach ( $codeblock->find('.r_'.$action) as $link ){
                if ( $this->logic->$fn_name($obj) ){
                    $link->href = $this->$ln_name();
                    $link->alt = null;
                }else{
                    $link->outertext = $link->alt;
                }
            }
            
        }
        
        foreach ( $codeblock->find('.r_cancel') as $link ){
            $link->href = $this->cancel_link();
        }
        
        foreach ( $codeblock->find('.r_conditional') as $conditional){
            if ( !key_exists($conditional->id,$this->logic->conditional ) ){
                $conditional->outertext = '';
            }
        }
        
        $codeblock = str_replace('<!', '<?', $codeblock);
        $codeblock = str_replace('!>', '?>', $codeblock);

        $codeblock = str_replace('{id}',@$obj->uid,$codeblock);
        return '?>'.$codeblock;
    }

    function numify($fname, $num){
        if($num==null) return $fname;
        $fsplit = explode('.',$fname);
        $rt = $fsplit[0].'.'.$num;
        if(sizeof($fsplit)==2) $rt .= '.'.$fsplit[1];
        return $rt;
    }
}

class black_hole{
    function __call($name,$args){
        return false;
    }
    
    function __get($name){
        return false;
    }
}
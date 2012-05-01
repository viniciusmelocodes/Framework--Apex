<?
class html_lib{
    function auto($resources_folder){
        ?>
<!-- Start Auto <?=$resources_folder?> -->
<?
        foreach( lib('fs')->dir("resources/$resources_folder") as $file){
            $full_path = "resources/$resources_folder/$file";
            $this->generate_tag($full_path);
        }
        
        foreach(array('js','css','less') as $filetype){
            $full_path = "resources/$resources_folder/controllers/".lib('uri')->segment(0).'.'.$filetype;
            if( file_exists( $full_path ) ){
                $this->generate_tag( $full_path );
            }

            $full_path = "resources/$resources_folder/controllers/".lib('uri')->segment(0).'/'.@lib('uri')->segment(1).'.'.$filetype;
            if( file_exists( $full_path ) ){
                $this->generate_tag( $full_path );
            }
        }
        ?>
    <!-- End Auto <?=$resources_folder?> -->
<?
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
        <link href="<?=$full_path?>" media="screen" rel="stylesheet" type="text/css" />
<?
                break;
            case 'less':
                ?>
        <link rel="stylesheet/less" href="<?=$full_path?>" type="text/css" media="screen"/>
<?
                break;
        }
    }
}
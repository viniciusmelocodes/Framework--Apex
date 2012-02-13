<?
class tabs_ui{
    public $selected = 1;
    
    function __construct(){
    }
    
    function render(){
        $dom = lib('htmldom')->str_get_dom($this->inner);
        
        $head = '';
        $tabs = '';
        $iterator = 1;
        foreach ( $dom->find('tab') as $tab){
            $display = ($iterator==$this->selected)?'block':'none';
            $class = ($iterator==$this->selected)?'selected':'';

            $head .= "<a class='$class' href='javascript:;' onclick='
                $(this).parent().find(\"a\").removeClass(\"selected\");
                $(this).addClass(\"selected\");
                $(this).parent().parent().find(\".tabs_body\ .tab\").hide();
                $(this).parent().parent().find(\".tabs_body\ .tab:nth-child($iterator)\").show();
            '>$tab->title</a>";
            $tabs .= "<div style='display:$display;' class='tab'>$tab</div>";
            $iterator++;
        }
        ?>
        <div class="tabs">
            <div class="tabs_head"><?=$head?></div>
            <div class="tabs_body"><?=$tabs?></div>
        </div>
        <?
    }
}
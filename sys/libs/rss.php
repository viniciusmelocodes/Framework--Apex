<?
class rss_lib{
    public $doc;

    function load($feedUrl){
        $this->doc = new DOMdocument();
        $this->doc->load($feedUrl);
        return $this;
    }
    
    function as_array(){
        $rss_tags = array(
            'title',
            'link',
            'guid',
            'comments',
            'description',
            'pubDate',
            'category',
        );
        $item_tag = 'item';

        $rss_array = array();
        $items = array();
        
        foreach($this->doc->getElementsByTagName($item_tag) as $node) {
            foreach($rss_tags as $value) {
                $items[$value] = @$node->getElementsByTagName($value)->item(0)->nodeValue;
            }
            array_push($rss_array, $items);
        }
        
        return $rss_array;
    }
}
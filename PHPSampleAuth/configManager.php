<?php

class Configuration{
    private $configFile = 'app.config';
    private $items = array();
    function __construct() { $this->parse(); }
    function __get($id) { return $this->items[$id]; }
    function parse()
    {
        $doc = new DOMDocument();
        $doc->load( $this->configFile );
        $cn = $doc->getElementsByTagName("appSettings");
        $nodes = $cn->item(0)->getElementsByTagName("*");
        foreach( $nodes as $node )
            $this->items[ $node->nodeName ] = $node->nodeValue;
    }
}

?>
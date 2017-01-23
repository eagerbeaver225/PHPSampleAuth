<?php
include ('configManager.php');

class AppCache{    
    public static $AccessToken = array();
    public static $AppKey;
    public static $AppSecret;
    public static $AuthenticationUrl;
    public static $OpenApiBaseUrl;

    static function init() { 
        $c = new Configuration();
        self::$AppKey = $c->AppKey;
        self::$AppSecret = $c->AppSecret;
        self::$AuthenticationUrl =  $c->AuthenticationUrl;
        self::$OpenApiBaseUrl = $c->OpenApiBaseUrl;         
    }
}

?>
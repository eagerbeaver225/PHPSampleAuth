<?php
include('appCache.php');
include('openApiAuthHelper.php');

session_start();

$token = array();
AppCache::init();
$token = $_SESSION['token'];

if(empty($token) || !isset($token)){    
    header("index.php");
    die();
    return;
}

global $status, $tokenVaule;
$refreshedToken = OpenApiAuthHelper::refreshToken(AppCache::$AuthenticationUrl, 
    AppCache::$AppKey, AppCache::$AppSecret, $token["refresh_token"]);
if(strlen($refreshedToken["refresh_token"])){
    $tokenValue = $refreshedToken["refresh_token"];
    $status = "Token refreshed";
    $_SESSION['token'] = $refreshedToken;
}else{
    $tokenValue = "null";
    $status = "Token refresh failed";
    unset($_SESSION['token']);
}

include('refreshToken_content.php');

?>

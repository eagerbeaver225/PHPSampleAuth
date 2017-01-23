<?php
include('appCache.php');
include('openApiAuthHelper.php');

$token = array();
AppCache::init();
session_start();
$token = $_SESSION['token'];
$token = array_filter($token);
if(empty($token)){
    header("index.php");
    die();
}

global $status, $tokenVaule;
$refreshedToken = OpenApiAuthHelper::refreshToken(AppCache::$AuthenticationUrl, 
    AppCache::$AppKey, AppCache::$AppSecret, $token["refresh_token"]);
if(strlen($refreshedToken["refresh_token"])){
    AppCache::$AccessToken = $refreshedToken["refresh_token"];
    $tokenValue = $refreshedToken["refresh_token"];
    $status = "Token refreshed";
}else{
    $tokenValue = "null";
    $status = "Token refresh failed";
}

include('refreshToken_content.php');

?>

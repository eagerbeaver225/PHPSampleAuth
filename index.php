﻿<?php 

include ('appCache.php'); 
include ('openApiAuthHelper.php');

session_start();

global $token;
global $Script, $samlRequest, $authenticationUrl, $openApiResponseData, $openApiBaseUrl;

$samlResponse = "";
$url = "";
$appKey = "";
$appSecret = "";
$samlToken = "";
$token = array();
$openApiResponseData = "";
$openApiBaseUrl = "";

if(array_key_exists('SAMLResponse', $_POST)){
    $samlResponse = $_POST["SAMLResponse"];    
}

global $tokenValue, $tokenType;
AppCache::init();

if(strlen($samlResponse) != 0){
    $url =  AppCache::$AuthenticationUrl;
    $appKey = AppCache::$AppKey;
    $appSecret = AppCache::$AppSecret;
    $openApiBaseUrl = AppCache::$OpenApiBaseUrl;
    $samlToken = utf8_decode(base64_decode($samlResponse));

    $token = OpenApiAuthHelper::getAccessToken($url, $appKey, $appSecret, $samlToken);
    $_SESSION['token'] = $token;

}else{
    if(!empty($_SESSION)){
        $token = $_SESSION['token'];   
        $token = array_filter($token);
    }

    $from = "";
    if(isset($_GET['from'])){
        $from = $_GET['from'];
    }
    if($from != 'refresh' || empty($token)){
        setupAuthenticationForm();
        return;
    }
}

include('apiClient.php');
$accessToken = "";

try{
    $accessToken = $token["token_type"]." ".$token["access_token"];
    $openApiResponseData = ApiClient::GetClientsMe($accessToken, AppCache::$OpenApiBaseUrl);
    $tokenValue = $token["access_token"];
    $tokenType = $token["token_type"];
    include ('default.php'); 
} catch (Exception $e){
    showWebException();
    $tokenValue = $token["access_token"];
    $tokenType = $token["token_type"];
}

function setupAuthenticationForm()
{
    $Script = "";
    $samlRequest = "";
    $authenticationUrl = "";
    $openApiResponseData = "";
    $openApiBaseUrl = "";
    $tokenValue = "";
    $tokenType = "";

    AppCache::init();
    $authenticationUrl = AppCache::$AuthenticationUrl."/AuthnRequest";

    // Make the SAML request that attempts to authenticate (and redirects to the login-form if necessary)
    $url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $samlRequest = OpenApiAuthHelper::buildSamlRequest($authenticationUrl, $url, $url);
    $base64Saml = base64_encode(utf8_encode($samlRequest));

    $samlRequest = $base64Saml; // send the request as a base 64 encoded post-parameter (as a hidden field in the form)
    $Script = "document.forms['samlForm'].submit();";
    // Setup the auto-submit by inserting this into a script-tag right after the form

    include ('default.php');
}

function showWebException(Exception $e)
{
    $openApiResponseData = "Error: " + $e->getMessage();
}
?>
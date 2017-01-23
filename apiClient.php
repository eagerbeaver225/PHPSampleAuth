<?php

class ApiClient
{
    static function GetClientsMe($accessToken, $baseUrl){
        $uri = $baseUrl."/port/v1/clients/me";
        $options = array(
            'http' => array(
                'header' => array("Authorization: ".$accessToken),
                'method' => 'GET'
            )
        );
        $context = stream_context_create($options);
        $response = file_get_contents($uri, false, $context);
        if($response === FALSE){
            echo "Token response error";
        }else{
            return $response;
        }
    }
}
?>
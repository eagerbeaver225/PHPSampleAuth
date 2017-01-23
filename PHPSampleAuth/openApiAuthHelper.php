<?php

final class OpenApiAuthHelper{
        /// <summary>
        /// Retrieve the authorization code from the SAML response, and use that to get the OAuth token
        /// </summary>
        /// <param name="authenticationUrl">The URL of the authentication server.</param>
        /// <param name="appKey">The application key.</param>
        /// <param name="appSecret">The application secret.</param>
        /// <param name="samlToken">The SAML token.</param>
        static function getAccessToken($authenticationUrl, $appKey, $appSecret, $samlToken)
        {
            $authorizationUrl = $authenticationUrl."/token";

            // In case you want to use the SAML token for anything other than retrieving the authorization code you also need to:
            // 1) Validate that the issuer is either https://sim.logonvalidation.net or https://live.logonvalidation.net depending on environment
            // 2) That the token's status code is "Success"
            // 3) That the token has not expired
            // 4) That the signature matches the content using the public key provided in the token
            // if (!IsValidSamlToken(saml))
            //   throw new Exception("Provided SAML token is invalid");

            // Extract the autorization code from the SAML response
            $authorizationCode = self::parseAndGetAuthorizationCode($samlToken);           

            // Setup the grant type & authorization code for the token service
            //$requestPayload = "grant_type=authorization_code&code=".$authorizationCode;
            $requestPayload = array(
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode 
            );

            // Request a token, using the appKey, secret & the payload
            return self::sendAuthorizationRequest($authorizationUrl, $appKey, $appSecret, $requestPayload);
        }

        static function refreshToken($authenticationUrl, $appKey, $appSecret, $refreshToken)
        {
            $authorizationUrl = $authenticationUrl."/token";

            // Setup the grant type & refresh token for the token service
            //$requestPayload = "grant_type=refresh_token&refresh_token=".$refreshToken;
            $requestPayload = array(
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken 
            );

            // Request the new token, using the appKey, secret & the payload
            return self::sendAuthorizationRequest($authorizationUrl, $appKey, $appSecret, $requestPayload);
        }

        static function sendAuthorizationRequest($authenticationUrl, $appKey, $appSecret, $requestPayload)
        {
            $credentials = $appKey.":".$appSecret;
            $auth = base64_encode(utf8_encode($credentials));           

            $options = array(
                'http' => array(
                    'header' => array("Authorization: Basic ".$auth,"Content-type: application/x-www-form-urlencoded\r\n"),
                    'method' => 'POST',
                    'content' => http_build_query($requestPayload)
                )
            );
            $context = stream_context_create($options);
            $tokenResponse = file_get_contents($authenticationUrl, false, $context);
            if($tokenResponse === FALSE){
                 echo "Token response error";
            }else{
                return json_decode($tokenResponse, true);                
            }        
        }

        static function parseAndGetAuthorizationCode($saml)
        {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->loadXML($saml);
            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace("samlp", "urn:oasis:names:tc:SAML:2.0:protocol");
            $xpath->registerNamespace("saml", "urn:oasis:names:tc:SAML:2.0:assertion");
            
            $elements = [];
            $attribute = "";
            $query = "/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name='AuthorizationCode']/saml:AttributeValue";
            $elements = $xpath->query($query);
            foreach($elements as $field){
               $attribute = $field->nodeValue;
            }

            return $attribute;
        }

        static function GUID()
        {
            if (function_exists('com_create_guid') === true)
            {
                return trim(com_create_guid(), '{}');
            }

            return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        }

        /// <summary>
        /// Builds the SAML authentication request.
        /// </summary>
        /// <param name="authenticationUrl">The URL for the authentication endpoint.</param>
        /// <param name="applicationUrl">The URL for the application.</param>
        /// <param name="issuerUrl">The URL issuing the request</param>
        static function buildSamlRequest($authenticationUrl, $applicationUrl, $issuerUrl)
        {
            $guid = self::GUID();
            $timestamp = str_replace('+00:00', 'Z', gmdate('c'));

            return chr(10).'                    <samlp:AuthnRequest ID="'.$guid.'" Version="2.0" ForceAuthn="false" IsPassive="false"'.chr(10).'                    ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"'.chr(10).'                    IssueInstant="'.$timestamp.'" Destination="'.$authenticationUrl.'" AssertionConsumerServiceURL="'.$applicationUrl.'">'.
           chr(10).'                    <samlp:NameIDPolicy AllowCreate="false" />'.chr(10).'                    <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">'.$issuerUrl.'</saml:Issuer>'.chr(10).'                    </samlp:AuthnRequest>';     
        }
}
?>
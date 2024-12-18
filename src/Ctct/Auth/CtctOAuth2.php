<?php
namespace Ctct\Auth;

use Ctct\Exceptions\CtctException;
use Ctct\Exceptions\OAuth2Exception;
use Ctct\Util\Config;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

/**
 * Class that implements necessary functionality to obtain an access token from a user
 *
 * @package     Auth
 * @author      Constant Contact
 */
class CtctOAuth2
{
    public $clientId;
    public $clientSecret;
    public $redirectUri;
    public $client;
    public $props;

    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->client = new Client();
    }

    /**
     * Get the URL at which the user can authenticate and authorize the requesting application
     * @param boolean $server - Whether or not to use OAuth2 server flow, alternative is client flow
     * @param string $state - An optional value used by the client to maintain state between the request and callback.
     * @return string $url - The url to send a user to, to grant access to their account
     */
    public function getAuthorizationUrl($server = true, $state = null)
    {
        $responseType = ($server) ? Config::get('auth.response_type_code') : Config::get("auth.response_type_token");
        $params = array(
            'response_type' => $responseType,
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri
        );

        // add the state param if it was provided
        if ($state != null) {
            $params['state'] = $state;
        }

        $baseUrl = Config::get('auth.base_url') . Config::get('auth.authorization_endpoint');
        return (string) Uri::withQueryValues(new Uri($baseUrl), $params);
    }

    /**
     * Obtain an access token
     * @param string $code - code returned from Constant Contact after a user has granted access to their account
     * @return array
     * @throws OAuth2Exception
     */
    public function getAccessToken($code)
    {
        $params = array(
            'grant_type' => Config::get('auth.authorization_code_grant_type'),
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri
        );

        $baseUrl = Config::get('auth.base_url') . Config::get('auth.token_endpoint');
        $request = new Request("POST", Uri::withQueryValues(new Uri($baseUrl), $params));

        try {
            $response = json_decode($this->client->send($request)->getBody(), true);
        } catch (BadResponseException $e) {
            throw $this->convertException($e);
        }

        return $response;
    }

    /**
     * Get an information about an access token
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @return array
     * @throws CtctException
     */
    public function getTokenInfo($accessToken)
    {
        $baseUrl = Config::get('auth.base_url') . Config::get('auth.token_info');
        $request = new Request("POST", Uri::withQueryValues(new Uri($baseUrl), ['access_token' => $accessToken]));

        try {
            $response = json_decode($this->client->send($request)->getBody(), true);
        } catch (BadResponseException $e) {
            throw $this->convertException($e);
        }
        return $response;
    }

    /**
     * @param BadResponseException $exception
     * @return OAuth2Exception
     */
    private function convertException($exception) {
        $ctctException = new OAuth2Exception($exception->getMessage(), $exception->getCode());
        $ctctException->setUrl((string) $exception->getRequest()->getUri());
        $ctctException->setErrors([json_decode($exception->getResponse()->getBody()->getContents())]);
        return $ctctException;
    }
}

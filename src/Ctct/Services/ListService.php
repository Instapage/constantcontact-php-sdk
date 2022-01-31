<?php
namespace Ctct\Services;

use Ctct\Exceptions\CtctException;
use Ctct\Util\Config;
use Ctct\Components\Contacts\ContactList;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Stream;

/**
 * Performs all actions pertaining to Constant Contact Lists
 *
 * @package     Services
 * @author         Constant Contact
 */
class ListService extends BaseService
{
    /**
     * Get lists within an account
     * @param $accessToken - Constant Contact OAuth2 access token
     * @param array $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      modified_since - ISO-8601 formatted timestamp.
     * @return Array - ContactLists
     * @throws CtctException
     */
    public function getLists($accessToken, Array $params = array())
    {
        $baseUrl = Config::get('endpoints.base_url') . Config::get('endpoints.lists');

        $request = parent::createBaseRequest($accessToken, 'GET', $baseUrl, $params);

        try {
            $response = parent::getClient()->send($request);
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        $lists = array();
        foreach (json_decode((string) $response->getBody(), true) as $contact) {
            $lists[] = ContactList::create($contact);
        }

        return $lists;
    }

    /**
     * Create a new Contact List
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param ContactList $list
     * @return ContactList
     * @throws CtctException
     */
    public function addList($accessToken, ContactList $list)
    {
        $baseUrl = Config::get('endpoints.base_url') . Config::get('endpoints.lists');

        $request = parent::createBaseRequest($accessToken, 'POST', $baseUrl);
        $stream = \GuzzleHttp\Psr7\Utils::streamFor(json_encode($list));

        try {
            $response = parent::getClient()->send($request->withBody($stream));
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        return ContactList::create(json_decode((string) $response->getBody(), true));
    }

    /**
     * Update a Contact List
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param ContactList $list - ContactList to be updated
     * @return ContactList
     * @throws CtctException
     */
    public function updateList($accessToken, ContactList $list)
    {
        $baseUrl = Config::get('endpoints.base_url') . sprintf(Config::get('endpoints.list'), $list->id);

        $request = parent::createBaseRequest($accessToken, 'PUT', $baseUrl);
        $stream = \GuzzleHttp\Psr7\Utils::streamFor(json_encode($list));

        try {
            $response = parent::getClient()->send($request->withBody($stream));
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        return ContactList::create(json_decode((string) $response->getBody(), true));
    }

    /**
     * Delete a Contact List
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param $listId - list id
     * @return ContactList
     * @throws CtctException
     */
    public function deleteList($accessToken, $listId)
    {
        $baseUrl = Config::get('endpoints.base_url') . sprintf(Config::get('endpoints.list'), $listId);

        $request = parent::createBaseRequest($accessToken, 'DELETE', $baseUrl);

        try {
            $response = parent::getClient()->send($request);
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        return ($response->getStatusCode() == 204) ? true : false;
    }

    /**
     * Get an individual contact list
     * @param $accessToken - Constant Contact OAuth2 access token
     * @param $listId - list id
     * @return ContactList
     * @throws CtctException
     */
    public function getList($accessToken, $listId)
    {
        $baseUrl = Config::get('endpoints.base_url') . sprintf(Config::get('endpoints.list'), $listId);

        $request = parent::createBaseRequest($accessToken, 'GET', $baseUrl);

        try {
            $response = parent::getClient()->send($request);
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        return ContactList::create(json_decode((string) $response->getBody(), true));
    }
}

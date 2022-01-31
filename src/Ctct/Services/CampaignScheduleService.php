<?php
namespace Ctct\Services;

use Ctct\Exceptions\CtctException;
use Ctct\Util\Config;
use Ctct\Components\EmailMarketing\Schedule;
use Ctct\Components\EmailMarketing\TestSend;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Stream;

/**
 * Performs all actions pertaining to scheduling Constant Contact Campaigns
 *
 * @package Services
 * @author Constant Contact
 */
class CampaignScheduleService extends BaseService
{
    /**
     * Create a new schedule for a campaign
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param int $campaignId - Campaign id to be scheduled
     * @param Schedule $schedule - Schedule to be created
     * @return Schedule
     * @throws CtctException
     */
    public function addSchedule($accessToken, $campaignId, Schedule $schedule)
    {
        $baseUrl = Config::get('endpoints.base_url') . sprintf(Config::get('endpoints.campaign_schedules'), $campaignId);

        $request = parent::createBaseRequest($accessToken, 'POST', $baseUrl);
        $stream = \GuzzleHttp\Psr7\Utils::streamFor(json_encode($schedule));

        try {
            $response = parent::getClient()->send($request->withBody($stream));
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        return Schedule::create(json_decode((string) $response->getBody(), true));
    }

    /**
     * Get a list of schedules for a campaign
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param int $campaignId - Campaign id to be scheduled
     * @return array
     * @throws CtctException
     */
    public function getSchedules($accessToken, $campaignId)
    {
        $baseUrl = Config::get('endpoints.base_url') . sprintf(Config::get('endpoints.campaign_schedules'), $campaignId);

        $request = parent::createBaseRequest($accessToken, 'GET', $baseUrl);

        try {
            $response = parent::getClient()->send($request);
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        $schedules = array();
        foreach (json_decode((string) $response->getBody(), true) as $schedule) {
            $schedules[] = Schedule::create($schedule);
        }
        return $schedules;
    }

    /**
     * Get a specific schedule for a campaign
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param int $campaignId - Campaign id to be get a schedule for
     * @param int $scheduleId - Schedule id to retrieve
     * @return Schedule
     * @throws CtctException
     */
    public function getSchedule($accessToken, $campaignId, $scheduleId)
    {
        $baseUrl = Config::get('endpoints.base_url') . sprintf(Config::get('endpoints.campaign_schedule'), $campaignId, $scheduleId);

        $request = parent::createBaseRequest($accessToken, 'GET', $baseUrl);

        try {
            $response = parent::getClient()->send($request);
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        return Schedule::create(json_decode((string) $response->getBody(), true));
    }

    /**
     * Update a specific schedule for a campaign
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param int $campaignId - Campaign id to be scheduled
     * @param Schedule $schedule - Schedule to retrieve
     * @return Schedule
     * @throws CtctException
     */
    public function updateSchedule($accessToken, $campaignId, Schedule $schedule)
    {
        $baseUrl = Config::get('endpoints.base_url') . sprintf(Config::get('endpoints.campaign_schedule'), $campaignId, $schedule->id);

        $request = parent::createBaseRequest($accessToken, 'PUT', $baseUrl);
        $stream = \GuzzleHttp\Psr7\Utils::streamFor(json_encode($schedule));

        try {
            $response = parent::getClient()->send($request->withBody($stream));
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        return Schedule::create(json_decode((string) $response->getBody(), true));
    }

    /**
     * Delete a specific schedule for a campaign
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param int $campaignId - Campaign id
     * @param int $scheduleId - Schedule id to delete
     * @return True if successful
     * @throws CtctException
     */
    public function deleteSchedule($accessToken, $campaignId, $scheduleId)
    {
        $baseUrl = Config::get('endpoints.base_url') . sprintf(Config::get('endpoints.campaign_schedule'), $campaignId, $scheduleId);

        $request = parent::createBaseRequest($accessToken, 'DELETE', $baseUrl);

        try {
            $response = parent::getClient()->send($request);
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        return ($response->getStatusCode() == 204) ? true : false;
    }

    /**
     * Send a test send of a campaign
     * @param string $accessToken - Constant Contact OAuth2 access token
     * @param int $campaignId - Id of campaign to send test of
     * @param TestSend $testSend - Test send details
     * @return TestSend
     * @throws CtctException
     */
    public function sendTest($accessToken, $campaignId, TestSend $testSend)
    {
        $baseUrl = Config::get('endpoints.base_url') . sprintf(Config::get('endpoints.campaign_test_sends'), $campaignId);

        $request = parent::createBaseRequest($accessToken, 'POST', $baseUrl);
        $stream = \GuzzleHttp\Psr7\Utils::streamFor(json_encode($testSend));

        try {
            $response = parent::getClient()->send($request->withBody($stream));
        } catch (BadResponseException $e) {
            throw parent::convertException($e);
        }

        return TestSend::create(json_decode((string) $response->getBody(), true));
    }
}

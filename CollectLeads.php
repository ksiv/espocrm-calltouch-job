<?php

namespace Espo\Custom\Jobs;
use Espo\Core\ExternalAccount\OAuth2;
use CollectLeadsSettings;
use EspoApiClient;
require_once ("EspoApiClient.php");
require_once ("CollectLeadsSettings.php");

class CollectLeads extends \Espo\Core\Jobs\Base
{

    public function run()
    {
        try{
        // all the logic needs to be defined in the method run
            $node = CollectLeadsSettings\CollectLeadsSettings::NODE;
            $token = CollectLeadsSettings\CollectLeadsSettings::TOKEN;
            $id = CollectLeadsSettings\CollectLeadsSettings::ID;

            // they really do have two diff date formats for calls and inquiries
            $dateCall = date('d/m/Y');
            $dateMail = date('m/d/Y');
             $baseURL = 'https://api-node'. $node .'.calltouch.ru/calls-service/RestAPI/';
            $mailURL = $baseURL . 'requests/?clientApiId=' . $token. '&dateFrom=' . $dateMail . '&dateTo=' . $dateMail . '&withMapVisits=true';
            $callURL = $baseURL . $id . '/calls-diary/calls?clientApiId=' . $token . '&dateFrom=' . $dateCall . '&dateTo=' . $dateCall;



            //
            // Load Calls
            //
            error_log(var_dump($callURL));
            $httpClient = new OAuth2\Client();
            $resArray = $httpClient->request($callURL);
            $myApiURL = CollectLeadsSettings\CollectLeadsSettings::MYAPIURL;
            $apiKey = CollectLeadsSettings\CollectLeadsSettings::APIKEY;
            $local_login = CollectLeadsSettings\CollectLeadsSettings::ESPO_API_LOGIN;
            $local_pass = CollectLeadsSettings\CollectLeadsSettings::ESPO_API_PASS;
            foreach($resArray['result'] as $key => $lead) {


                $formData = [
                    'emailAddress' => '',
                    'phoneNumber' => $lead['callerNumber'],
                    'addressCity' => $lead['city'],
                    'siteName' => $lead['siteName'],
                    'keyword' => $lead['keyword'],
                    'utmSource' => $lead['utmSource'],
                    'utmTerm' =>  $lead['utmTerm'],
                    'utmCampaign' =>  $lead['utmCampaign'],
                    'utmMedium' => $lead['utmMedium'],
                    'successful' => $lead['successful'],
                    'uniqTargetCall' => $lead['uniqTargetCall'],
                    'targetCall' => $lead['targetCall'],
                    'medium' => $lead['medium'],
                    'date' => $lead['date'],
                    'type' => 'Звонок',
                    'callId' => $lead['callId']

                ];
                $client = new EspoApiClient\EspoApiClient($myApiURL,$local_login,$local_pass);

                $response = $client->request('POST', 'LeadCapture/' .$apiKey, $formData);


            }

            //
            // Load Mails
            //
            error_log(var_dump($mailURL));
            $httpClient = new OAuth2\Client();
            $resArray = $httpClient->request($mailURL);
            $myApiURL = CollectLeadsSettings\CollectLeadsSettings::MYAPIURL;
            $apiKey = CollectLeadsSettings\CollectLeadsSettings::APIKEY;
            $local_login = CollectLeadsSettings\CollectLeadsSettings::ESPO_API_LOGIN;
            $local_pass = CollectLeadsSettings\CollectLeadsSettings::ESPO_API_PASS;

            foreach($resArray['result'] as $key => $lead) {


                $formData = [
                    'lastName' => $lead['client']['fio'],
                    'emailAddress' => '',
                    'phoneNumber' => $lead['client']['phones']['0']['phoneNumber'],
                    'addressCity' => $lead['session']['city'],
                    'siteName' => $lead['siteId'],
                    'keyword' => $lead['session']['keyword'],
                    'utmSource' => $lead['session']['utmSource'],
                    'utmTerm' =>  $lead['session']['utmTerm'],
                    'utmCampaign' =>  $lead['session']['utmCampaign'],
                    'utmMedium' => $lead['session']['utmMedium'],
                    'successful' => $lead['successful'],
                    'uniqTargetCall' => $lead['uniqTargetRequest'],
                    'targetCall' => $lead['targetRequest'],
                    'medium' => $lead['session']['medium'],
                    'date' => $lead['dateStr'],
                    'requestId' => $lead['requestId'],
                    'type' => 'Заявка'

                ];
                $client = new EspoApiClient\EspoApiClient($myApiURL,$local_login,$local_pass);

                $response = $client->request('POST', 'LeadCapture/' .$apiKey, $formData);


            }

            }catch (\Exception $e){
                echo 'Exception: ',  $e->getMessage(), "\n";
            }








    }



}
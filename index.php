<?php
/*
*   Author: Emiliano Vargas
*   Date: Jun 2020
*   Description: Automate your SES bounces and complaints to be sent automatically to you Slack Channel.
*/
//Set timezone to retrieve correct timestamps
date_default_timezone_set('America/Argentina/Buenos_Aires');
//Set your Slack Webhook URL
$webhook = 'https://hooks.slack.com/YOUR_URL_TO_THE_WEBHOOK';
require 'vendor/autoload.php';

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

// Make sure the request is POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(404);
    die;
}

//Magic from the AWS-SDK.
$message = Message::fromRawPostData();

//Uncomment this if you want to confirm a subscription from a topic.
// if ($message['Type'] === 'SubscriptionConfirmation') {
//     // Confirm the subscription by sending a GET request to the SubscribeURL
//     file_get_contents($message['SubscribeURL']);
// }

//Validate that the msg comes from AWS. More magic from the AWS-SDK.
$validator = new MessageValidator();
//If the message is valid
if ($validator->isValid($message)) {
    //We load the notification message into $json.
    $json = json_decode($message['Message']);

    var_dump($json);
    //Switch to evaluate if the notification is from a bounce or a complaint and then we prepare the json to be sent to Slack.
    switch (true) {
        case $json->bounce != null:
            $data = array(
                'blocks' =>
                array(
                    0 =>
                    array(
                        'type' => 'section',
                        'text' =>
                        array(
                            'type' => 'mrkdwn',
                            'text' => "🚫*SES " . $json->notificationType . " Received*🚫\n" . $json->bounce->bouncedRecipients[0]->emailAddress . " " . $json->bounce->bouncedRecipients[0]->action,
                        ),
                    ),
                    1 =>
                    array(
                        'type' => 'section',
                        'fields' =>
                        array(
                            0 =>
                            array(
                                'type' => 'mrkdwn',
                                'text' => "*BounceType:*\n" . $json->bounce->bounceType,
                            ),
                            1 =>
                            array(
                                'type' => 'mrkdwn',
                                'text' => "*bounceSubType:*\n" . $json->bounce->bounceSubType,
                            ),
                            2 =>
                            array(
                                'type' => 'mrkdwn',
                                'text' => "*Timestamp:*\n" . date('l jS \of F Y h:i:s A'),
                            ),
                            3 =>
                            array(
                                'type' => 'mrkdwn',
                                'text' => "*Source:*\n" . $json->mail->source . " (" . $json->mail->sourceIp . ")",
                            ),
                        ),
                    ),
                    2 =>
                    array(
                        'type' => 'divider'
                    ),
                ),
            );
            break;
        case $json->complaint != null:
            $data = array(
                'blocks' =>
                array(
                    0 =>
                    array(
                        'type' => 'section',
                        'text' =>
                        array(
                            'type' => 'mrkdwn',
                            'text' => "⚠*SES " . $json->notificationType . " Received*⚠\n" . $json->complaint->complainedRecipients[0]->emailAddress,
                        ),
                    ),
                    1 =>
                    array(
                        'type' => 'section',
                        'fields' =>
                        array(
                            0 =>
                            array(
                                'type' => 'mrkdwn',
                                'text' => "*Complaint Type:*\n" . $json->complaint->complaintFeedbackType,
                            ),
                            1 =>
                            array(
                                'type' => 'mrkdwn',
                                'text' => "*User Agent:*\n" . $json->complaint->userAgent,
                            ),
                            2 =>
                            array(
                                'type' => 'mrkdwn',
                                'text' => "*Timestamp:*\n" . date('l jS \of F Y h:i:s A'),
                            ),
                            3 =>
                            array(
                                'type' => 'mrkdwn',
                                'text' => "*Source:*\n" . $json->mail->source . " (" . $json->mail->sourceIp . ")",
                            ),
                        ),
                    ),
                    2 =>
                    array(
                        'type' => 'divider'
                    ),
                ),
            );
            break;
    }
    //We send the JSON to the Slack Webhook
    $curl = curl_init($webhook);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_exec($curl);
}
//If message it's not valid.
else {
    //Send the error message + the IP that originated the notification.
    $curl = curl_init($webhook);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
        'text' => '🚫Ha fallado la verificación del mensaje. WHO: ' . $_SERVER['REMOTE_ADDR'],
    )));
    curl_exec($curl);
}

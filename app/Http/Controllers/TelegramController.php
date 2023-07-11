<?php

namespace App\Http\Controllers;

use danog\MadelineProto\Settings;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings\AppInfo;
use danog\MadelineProto\Tools;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    //

    public function __construct()
    {

    }

    public function test()
    {

        $settings = new Settings();

        // set api_id & api_hash
        $settings->setAppInfo((new AppInfo())->setApiId(24056222)->setApiHash("ad3a6029e1100010b1d94cac023a8260"));
        $madeline_proto = new API('telegram.madeline', $settings);

        try {
            $account_Authorizations = $madeline_proto->account->getAuthorizations();
        } catch (\danog\MadelineProto\Exception $e) {

            $madeline_proto->phoneLogin(Tools::readLine('Enter your phone number: '));
            $authorization = $madeline_proto->completePhoneLogin(Tools::readLine('Enter the phone code: '));
            if ($authorization['_'] === 'account.password') {
                $authorization = $madeline_proto->complete2falogin(Tools::readLine('Please enter your password (hint ' . $authorization['hint'] . '): '));
            }
            if ($authorization['_'] === 'account.needSignup') {
                $authorization = $madeline_proto->completeSignup(Tools::readLine('Please enter your first name: '), readline('Please enter your last name (can be empty): '));
            }
        }

        // $full_chat = $madeline_proto->getFullInfo(-1001908676080);
        // var_dump($full_chat);
        $chat = 'https://t.me/Amirhosseyn_Zareian'; // Replace with the channel username or ID
        $limit = 100; // Number of messages to retrieve
        $offset_id = 0; // ID of the last message you received
        $offset_date = 0; // Date of the last message you received
        $max_id = 0; // ID of the first message you want to retrieve
        $min_id = 0; // ID of the last message you want to retrieve

        $messages = $madeline_proto->messages->getHistory(['peer' => $chat, 'limit' => $limit, 'offset_id' => $offset_id, 'offset_date' => $offset_date, 'max_id' => $max_id, 'min_id' => $min_id]);

        foreach ($messages['messages'] as $message) {
            // Do something with the message
            echo $message['message'] . "\n";
        }

    }
}
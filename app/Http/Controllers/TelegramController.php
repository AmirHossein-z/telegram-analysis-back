<?php

namespace App\Http\Controllers;

use danog\MadelineProto\Settings;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings\AppInfo;
use danog\MadelineProto\Tools;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TelegramController extends Controller
{
    //

    public $madelineProto = "";
    public function __construct()
    {

    }

    public function loginTelegram()
    {
        $userInfo = auth()->user()->id;
        if (is_null($userInfo->api_id) || is_null($userInfo->api_hash)) {
            return response()->json(['status' => false, 'value' => 'شاید باید اطلاعات خود را تکمیل کنید!']);
        }

        $settings = new Settings();
        $madelinePath = base_path('public/telegram.madeline');
        $settings->setAppInfo((new AppInfo())->setApiId((int) $userInfo->api_id)->setApiHash($userInfo->api_hash));
        $this->madelineProto = new API($madelinePath, $settings);
        $this->madelineProto->phoneLogin("+989307573597");
        return response()->json([
            'status' => true,
            'value' => ''
        ]);

    }

    public function otpValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $otpError = $errors->first('otp');

            $message = [
                strlen($otpError) > 0 ? ['apiId' => 'otp معتبر نیست'] : null,
            ];

            $message = array_values(array_filter($message));

            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 400);
        }

        $madelinePath = base_path('public/telegram.madeline');
        $this->madelineProto = new API($madelinePath);

        $authorization = $this->madelineProto->completePhoneLogin($request->input('otp'));

        // Save the session information
        // file_put_contents('madeline.madeline', serialize($this->madelineProto->API));
        // if ($authorization['_'] === 'account.password') {
        //     $authorization = $this->madelineProto->complete2falogin(Tools::readLine('Please enter your password (hint ' . $authorization['hint'] . '): '));
        // }
        // if ($authorization['_'] === 'account.needSignup') {
        //     $authorization = $this->madelineProto->completeSignup(Tools::readLine('Please enter your first name: '), readline('Please enter your last name (can be empty): '));
        // }

        return response()->json(['status' => true, 'value' => '']);
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

    public function getAllUserChannelsHas()
    {
        // Load the session information from the file
        $madelinePath = base_path('public/telegram.madeline');

        // Create a new MadelineProto instance using the session information
        $madeline_proto = new API($madelinePath);
        $dialogs = $madeline_proto->getDialogs();
        $channelIds = [];
        foreach ($dialogs as $dialog) {
            if ($dialog['_'] === 'peerChannel') {
                array_push($channelIds, $dialog['channel_id']);
            }
        }

        $channelsInfo = [];
        foreach ($channelIds as $id) {
            $info = $madeline_proto->getInfo(['_' => 'inputPeerChannel', 'channel_id' => $id]);
            if (!is_null($info)) {
                $channel_name = $info['Chat']['title'];
                array_push($channelsInfo, ['id' => $id, 'channel_name' => $channel_name]);
            }
            usleep(1000);
        }

        return response()->json(['status' => true, 'value' => $channelsInfo]);
    }

    public function setChannelInfo(Request $request)
    {

        /* get channel info and insert to database */
        $channelId = $request->input('channelId');

        $madelinePath = base_path('public/telegram.madeline');
        $madeline_proto = new API($madelinePath);
        // $info = $madeline_proto->getFullInfo('-100' . $channelId);

        // $name = $info['Chat']['title'];
        // $channel_telegram_id = $info['Chat']['username'];
        // $description = $info['full']['about'];
        // $membersCount = $info['full']['participants_count'];

        // $dateCreated = date('Y-m-d H:i:s');
        // $dateUpdated = date('Y-m-d H:i:s');
        // DB::insert('INSERT INTO channels(name,description,channel_telegram_id,members_count,user_id,created_at,updated_at) VALUES (?,?,?,?,?,?,?)', [
        //     $name,
        //     $description,
        //     $channel_telegram_id,
        //     $membersCount,
        //     auth()->user()->id,
        //     $dateCreated,
        //     $dateUpdated
        // ]);

        /* get channel info and insert to database */


        /* get posts and insert to database */
        $messages = $madeline_proto->messages->getHistory([
            'peer' => '-100' . $channelId,
            'offset_id' => 0,
            'offset_date' => 0,
            'add_offset' => 0,
            'limit' => 0,
            'max_id' => 0,
            'min_id' => 0
        ]);

        $posts = [];
        foreach ($messages['messages'] as $message) {
            $post = [
                'id' => $message['id'],
                'views' => $message['views'],
                'shares' => $message['forwards'],
                'tags' => $message['entities'] ?? [],
                'date_created' => $message['date'] ?? null,
                'date_edited' => $message['edit_date'] ?? null
            ];

            // Handle different message types (text, photo, video, etc.)
            if (isset($message['message'])) {
                $post['content'] = $message['message'];
                preg_match_all('/#\w+/', $message['message'], $matches);
                // this regex searchs for tags and excludes #name in link url
                if (isset($matches[0])) {
                    $post['tags'] = $matches[0];
                }
            } elseif (isset($message['media'])) {
                if ($message['media'] instanceof \danog\MadelineProto\TL\Types\MessageMediaDocument) {
                    $post['file_url'] = $madeline_proto->downloadToBrowser($message['media']['document']);
                } elseif ($message['media'] instanceof \danog\MadelineProto\TL\Types\MessageMediaPhoto) {
                    $post['file_url'] = $madeline_proto->downloadToBrowser($message['media']['photo']);
                }
            }

            $posts[] = $post;
        }


        /* get posts and insert to database */
        return response()->json(['status' => true, 'value' => $posts]);
    }

    private function getPosts()
    {

    }
}
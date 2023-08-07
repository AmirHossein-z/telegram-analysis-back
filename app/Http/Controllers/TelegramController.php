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

    public function __construct()
    {

    }

    public function loginTelegram()
    {
        try {
            //code...
            $userInfo = auth()->user();
            if (is_null($userInfo->api_id) || is_null($userInfo->api_hash)) {
                return response()->json(['status' => false, 'value' => 'شاید باید اطلاعات خود را تکمیل کنید!']);
            }

            $settings = new Settings();
            $madelinePath = base_path('public/telegram_' . $userInfo->id . '.madeline');
            $settings->setAppInfo((new AppInfo())->setApiId((int) $userInfo->api_id)->setApiHash($userInfo->api_hash));
            $this->madelineProto = new API($madelinePath, $settings);

            // convert to international format
            $number = $userInfo->phone;
            $globalPhone = "+98" . substr($number, 1);

            $this->madelineProto->phoneLogin($globalPhone);
            return response()->json([
                'status' => true,
                'value' => ''
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'value' => $th]);
        }

    }

    public function otpValidation(Request $request)
    {
        $userInfo = auth()->user();

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

        $madelinePath = base_path('public/telegram_' . $userInfo->id . '.madeline');
        $this->madelineProto = new API($madelinePath);

        $authorization = $this->madelineProto->completePhoneLogin($request->input('otp'));

        // if ($authorization['_'] === 'account.password') {
        //     $authorization = $this->madelineProto->complete2falogin(Tools::readLine('Please enter your password (hint ' . $authorization['hint'] . '): '));
        // }
        // if ($authorization['_'] === 'account.needSignup') {
        //     $authorization = $this->madelineProto->completeSignup(Tools::readLine('Please enter your first name: '), readline('Please enter your last name (can be empty): '));
        // }

        return response()->json(['status' => true, 'value' => '']);
    }

    public function getAllUserChannelsHas()
    {
        $userInfo = auth()->user();
        // Load the session information from the file
        $madelinePath = base_path('public/telegram_' . $userInfo->id . '.madeline');

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

        $userInfo = auth()->user();
        try {
            /* get channel info and insert to database */
            $channelId = $request->input('channelId');

            $madelinePath = base_path('public/telegram_' . $userInfo->id . '.madeline');
            $madeline_proto = new API($madelinePath);
            $info = $madeline_proto->getFullInfo('-100' . $channelId);


            $name = $info['Chat']['title'];
            $channelDateCreated = $info['Chat']['date'];
            $channelDateUpdated = $info['last_update'];

            if (isset($channelDateCreated)) {
                $channelDateCreated = date('Y-m-d H:i:s', $channelDateCreated);
            }

            if (isset($channelDateUpdated)) {
                $channelDateUpdated = date('Y-m-d H:i:s', $channelDateUpdated);
            }

            $userId = (int) auth()->user()->id;
            $channel_telegram_id = isset($info['Chat']['username']) ? $info['Chat']['username'] : null;
            $description = $info['full']['about'];
            $membersCount = $info['full']['participants_count'];
            $channelType = $info['type'];

            $dateCreated = date('Y-m-d H:i:s');
            $dateUpdated = date('Y-m-d H:i:s');
            DB::insert('INSERT INTO channels(name,type,description,channel_telegram_id,members_count,user_id,created_at,updated_at,channel_date_created,channel_date_updated) VALUES (?,?,?,?,?,?,?,?,?,?)', [
                $name,
                $channelType,
                $description,
                $channel_telegram_id,
                $membersCount,
                $userId,
                $dateCreated,
                $dateUpdated,
                $channelDateCreated,
                $channelDateUpdated
            ]);

            $channelIdInserted = DB::getPdo()->lastInsertId();
            /* get channel info and insert to database */

            /* get posts and insert to database */
            $offsetId = 0;
            $limit = 100;
            $posts = [];
            // $i = 0;
            $allTags = [];
            $allViews = 0;
            $AllShares = 0;

            while (true) {
                $messages = $madeline_proto->messages->getHistory([
                    'peer' => '-100' . $channelId,
                    'offset_id' => $offsetId,
                    'offset_date' => 0,
                    'add_offset' => 0,
                    'limit' => $limit,
                    'max_id' => 0,
                    'min_id' => 0
                ]);

                if (empty($messages['messages'])) {
                    break;
                }

                $posts = [];
                foreach ($messages['messages'] as $message) {
                    $post = [
                        'id' => $message['id'],
                        'views' => isset($message['views']) ? $message['views'] : 0,
                        'shares' => isset($message['forwards']) ? $message['forwards'] : 0,
                        'date_created' => $message['date'] ?? null,
                        'date_edited' => $message['edit_date'] ?? null
                    ];

                    // calculate views and shares for channel
                    $allViews += $post['views'];
                    $AllShares += $post['shares'];

                    if (isset($message['message'])) {
                        $post['content'] = $message['message'];
                        // preg_match_all('/#\w+/', $message['message'], $matches);
                        preg_match_all('/(\#[^\d][A-Za-z0-9\-\_+]*[\s]*){1,}$/', $message['message'], $matches);

                        if (isset($matches[0]) && isset($matches[0][0])) {
                            // get all tags in separate array
                            $tagArray = preg_split("/\s+/", $matches[0][0]);
                            foreach ($tagArray as $tag) {
                                $allTags[] = $tag;
                            }

                            $post['tags'] = implode(',', $tagArray);
                        }
                    } elseif (isset($message['media'])) {
                        if ($message['media'] instanceof \danog\MadelineProto\TL\Types\MessageMediaDocument) {
                            // $post['file_url'] = $madeline_proto->downloadToBrowser($message['media']['document']);
                            $post['content'] = '';
                        } elseif ($message['media'] instanceof \danog\MadelineProto\TL\Types\MessageMediaPhoto) {
                            // $post['file_url'] = $madeline_proto->downloadToBrowser($message['media']['photo']);
                            $post['content'] = '';
                        }
                    } else {
                        $post['content'] = '';
                    }

                    // convert to date that mysql understands
                    if (isset($post['date_created'])) {
                        $post['date_created'] = date('Y-m-d H:i:s', $post['date_created']);
                    }

                    if (isset($post['date_edited'])) {
                        $post['date_edited'] = date('Y-m-d H:i:s', $post['date_edited']);
                    }

                    $posts[] = [
                        'details' => $post['content'],
                        'view' => $post['views'],
                        'share' => $post['shares'],
                        'type' => 0,
                        'tags' => $post['tags'] ?? '',
                        'channel_id' => (int) $channelIdInserted,
                        'created_at' => $post['date_created'],
                        'updated_at' => $post['date_edited'],
                    ];
                    $offsetId = $message['id'];
                }

                // Count the occurrence of each tag
                $tagCounts = array_count_values($allTags);

                // Sort the tag counts in descending order
                arsort($tagCounts);

                // Get the most used tags
                $mostUsedTags = array_slice(array_keys($tagCounts), 0, 5);

                // Save the most used tags in another variable
                $mostUsedTagsString = implode(',', $mostUsedTags);

                try {
                    DB::table('posts')->insert($posts);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([$e, DB::getQueryLog()]);
                }
                // $i++;
                // if ($i === 5) {
                //     break;
                // }
            }

            // update channel table with new views and shares value
            DB::update("UPDATE channels SET view = ?, share = ?,tags=? WHERE id = ?", [$allViews, $AllShares, $mostUsedTagsString, $channelIdInserted]);
        } catch (\Throwable $e) {
            return response()->json([$e->getMessage()]);
        }

        return response()->json(['status' => true, 'value' => '']);

        /* get posts and insert to database */
    }

    // private function getPosts()
    // {

    // }
}
<?php

namespace app\controllers;

use app\models\MongoLog;
use app\models\Status;
use DateTime;
use DateTimeZone;
use SplQueue;
use Yii;
use yii\rest\Controller;

class SiteController extends Controller
{    
    public $mapping = [
        'change_post_status' => [
            'events' => [
                'send_email', 'send_sms'
            ],
            'msg' => ''
        ],
        'order_completed' => [
            'events' => [
                'send_email', 'send_sms'
            ],
            'msg' => ''
        ],
    ];

    protected function verbs()
    {
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;

        $event = $request->post('event');
        $micro = $request->post('micro');
        $key = $request->post('key');
        $value = $request->post('value');
        $logsCollection = Yii::$app->mongodb->getCollection('core_api_logs');
        $eventCollection = Yii::$app->mongodb->getCollection($event . '_' . $micro);
        $dateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('UTC'));
        $timstamp = $dateTime->getTimestamp();

        if(!isset($this->mapping[$event])) {
            $logsCollection->insert([
                'event' => $event,
                'key' => $key, 
                'value' => $value, 
                'description' => 'trying to handle event is not exist', 
                'created_at' => $timstamp
            ]);
            
            return [
                'status' => Status::STATUS_BAD_REQUEST,
                'message' => 'this event isnt exist'
            ];
        }

        $logsCollection->insert([
            'event' => $event, 
            'key' => $key, 
            'value' => $value, 
            'description' => 'event handling success', 
            'created_at' => $timstamp
        ]);

        $eventCollection->insert([
            'event' => $event, 
            'micro' => $micro,
            'key' => $key, 
            'value' => $value, 
            'status' => 'pending',
            'created_at' => $timstamp
        ]);

        return [
            'status' => Status::STATUS_OK,
            'message' => $this->mapping[$event]['msg'],
        ];
    }
}
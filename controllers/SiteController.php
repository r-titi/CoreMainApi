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
        'send_email' => [
            'micro' => 'http://localhost:8888/site/send-email',
            'msg' => ''
        ],
        'send_sms' => [
            'micro' => 'http://localhost:8888/site/send-sms',
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
        $key = $request->post('key');
        $value = $request->post('value');
        $logsCollection = Yii::$app->mongodb->getCollection('core_api_logs');
        $eventsCollection = Yii::$app->mongodb->getCollection('core_api_events');
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
        
        $output=null;
        $retval=null;

        $logsCollection->insert([
            'event' => $event, 
            'key' => $key, 
            'value' => $value, 
            'description' => 'event handling success', 
            'created_at' => $timstamp
        ]);

        $eventsCollection->insert([
            'event' => $event, 
            'micro' => $this->mapping[$event]['micro'],
            'key' => $key, 
            'value' => $value, 
            'status' => 'pending',
            'created_at' => $timstamp
        ]);

        return [
            'status' => Status::STATUS_OK,
            'message' => $this->mapping[$event]['msg'],
            // 'curl' => exec("curl -d '{\"key\":\"$key\", \"value\":\"$value\"}' -H \"Content-Type: application/json\" -X POST " . $this->mapping[$event]['micro']),
            // 'data' => "Returned with status $retval",
            // 'output' => $output
        ];
    }
}
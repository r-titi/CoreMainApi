<?php

namespace app\controllers;

use app\models\Constants;
use app\models\Status;
use DateTime;
use DateTimeZone;
use Yii;
use yii\rest\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        $request = Yii::$app->request;

        $event = $request->post('event');
        $key = $request->post('key');
        $value = $request->post('value');

        $validationMsg = [];
        if(empty($event)) {
            $validationMsg[] = 'Please send event';
        }

        if(empty($key)) {
            $validationMsg[] = 'Please send key';
        }

        if(empty($value)) {
            $validationMsg[] = 'Please send value';
        }

        if(empty($event) || empty($key) || empty($value)) {
            return [
                'status' => Status::STATUS_BAD_REQUEST,
                'message' => implode(' and ', $validationMsg)
            ];
        }

        $eventMapping = (new \yii\db\Query())
        ->select(['id', 'event', 'micro'])
        ->from('mapping')
        ->where(['event' => $event])
        ->all();

        $logsCollection = Yii::$app->mongodb->getCollection('core_api_logs');
        $dateTime = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('UTC'));
        $timstamp = $dateTime->getTimestamp();
        if(count($eventMapping) == 0) {
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

        foreach($eventMapping as $mapping) {
            $eventCollection = Yii::$app->mongodb->getCollection($mapping['event'] . '_' . $mapping['micro']);

            $eventCollection->insert([
                'event' => $event, 
                'micro' => $mapping['micro'],
                'key' => $key, 
                'value' => $value, 
                'status' => Constants::STATUS_PENDING,
                'created_at' => $timstamp
            ]);
        }

        $logsCollection->insert([
            'event' => $event, 
            'key' => $key, 
            'value' => $value, 
            'description' => 'event handling success', 
            'created_at' => $timstamp
        ]);

        return [
            'status' => Status::STATUS_OK,
        ];
    }
}
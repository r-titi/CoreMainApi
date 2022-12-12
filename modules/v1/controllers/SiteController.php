<?php

namespace app\modules\v1\controllers;

use app\models\Status;
use SplQueue;
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

        $event_mapping = [
            'send_email' => [
                'micro' => 'http://localhost:8888/site/send-email'
            ],
            'send_sms' => [
                'micro' => 'http://localhost:8888/site/send-sms'
            ],
        ];

        $newqueue = new SplQueue();
        $newqueue->enqueue('Welcome');
        $newqueue->enqueue('to');
        $newqueue->enqueue('PHP');

        print_r ($newqueue);exit;

        $orderID = $request->post('order_id');
        $action = $request->post('action');
        
        if($action == 'send_email') {
            $to = $request->post('to');
            $from = $request->post('from');
            $subject = $request->post('subject');
            $body = $request->post('body');
            if(empty($to)) {
                return [
                    'status' => Status::STATUS_BAD_REQUEST,
                    'message' => 'Please send to email',
                ];
            }

            if(empty($from)) {
                return [
                    'status' => Status::STATUS_BAD_REQUEST,
                    'message' => 'Please send from email',
                ];
            }

            if(empty($subject)) {
                return [
                    'status' => Status::STATUS_BAD_REQUEST,
                    'message' => 'Please send email subject',
                ];
            }

            if(empty($body)) {
                return [
                    'status' => Status::STATUS_BAD_REQUEST,
                    'message' => 'Please send email body',
                ];
            }
        } else if($action == 'send_sms') {
            $mobileNumber = $request->post('mobile');

            if(empty($mobileNumber)) {
                return [
                    'status' => Status::STATUS_BAD_REQUEST,
                    'message' => 'Please send mobile number',
                ];
            }
        }

        $mapping = [
            'send_email' => [
                'url' => 'http://localhost:8888/site/send-email',
                'msg' => 'send email order is submitted id ' . $orderID
            ],
            'send_sms' => [
                'url' => 'http://localhost:8888/site/send-sms',
                'msg' => 'send sms order is submitted id ' . $orderID
            ],
        ];
        
        $output=null;
        $retval=null;

        return [
            'status' => Status::STATUS_OK,
            'message' => $mapping[$action]['msg'],
            // 'exec' => exec('wget -bq  -O/dev/null '.$mapping[$action]['url'] . '?order_id='.$orderID, $output, $retval),
            // 'curl' => exec("curl " . $mapping[$action]['url']),
            'curl' => exec("curl -d '{\"to\":\"$to\", \"from\":\"$from\", \"subject\":\"$subject\", \"body\":\"$body\"}' -H \"Content-Type: application/json\" -X POST " . $mapping[$action]['url']),
            'data' => "Returned with status $retval",
            'output' => $output
        ];
    }
}
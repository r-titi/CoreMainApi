<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\mongodb\Query;

class WatcherController extends Controller
{
    public function actionIndex()
    {
        $eventsCollection = Yii::$app->mongodb->getCollection('core_api_events');
        $query = new Query();
        $query->select(['micro', 'event'])
            ->from('core_api_events')
            ->where(['status' => 'pending'])
            ->limit(10);
            
        $rows = $query->all();
        foreach($rows as $row) {
            $key = $row['key'] ?? null;
            $value = $row['value'] ?? null;
            exec("curl -d '{\"key\":\"$key\", \"value\":\"$value\"}' -H \"Content-Type: application/json\" -X POST " . $row['micro']);
            $eventsCollection->remove(['_id' => $row['_id']]);
        }
    }
}
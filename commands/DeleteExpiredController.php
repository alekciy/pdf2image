<?php

namespace app\commands;

use Yii;
use Ramsey\Uuid\Uuid;
use yii\console\Controller;

/**
 * Зачищает результаты конвертации PDF файлов время жизни которых истекло.
 * Команду следует поместить в ежеминутный крон.
 */
class DeleteExpiredController extends Controller
{
    public function actionIndex()
    {
        $webRoot = Yii::getAlias('@app/web');
        $currentTime = time();
        foreach (glob($webRoot . '/*', GLOB_ONLYDIR) as $dir) {
            $uuid = pathinfo($dir, PATHINFO_BASENAME);
            if (Uuid::isValid($uuid)) {
                $cTime = filectime($dir);
                if ($cTime + Yii::$app->params['pdfFile']['ttl'] < $currentTime) {
                    $cmd = 'rm -fr ' . escapeshellarg($dir);
                    exec($cmd);
                }
            }
        }

    }
}
<?php

namespace app\controllers\api;

use yii\rest\Controller;
use app\models\Pdf;
use yii\web\NotFoundHttpException;

class PdfController extends Controller
{
    public function actionIndex($id)
    {
        $pdf = Pdf::findById($id);
        if (!$pdf instanceof Pdf) {
            throw new NotFoundHttpException();
        }
        $pdf->setRenderFields(['imagePageList']);
        return $pdf;
    }
}
<?php

namespace app\controllers;

use app\models\Pdf;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PdfController extends Controller
{
    /**
     * Просмотр презентации по заданному идентификатору.
     * @param string $id Идентификатор презентации.
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $pdf = Pdf::findById($id);
        if (!$pdf instanceof Pdf) {
            throw new NotFoundHttpException();
        }
        return $this->render('view', ['pdf' => $pdf]);
    }

    /**
     * Скачать архив с презентацией.
     * @param string $id Идентификатор презентации.
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDownload($id)
    {
        $pdf = Pdf::findById($id);
        if (!$pdf instanceof Pdf) {
            throw new NotFoundHttpException();
        }
        $zipFile = $pdf->zipPath;
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . pathinfo($zipFile, PATHINFO_BASENAME) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($zipFile));
        ob_end_flush();
        readfile($zipFile);
    }
}
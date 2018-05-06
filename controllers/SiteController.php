<?php

namespace app\controllers;

use Yii;
use app\models\Pdf;
use app\models\PdfUploadForm;
use yii\web\Controller;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Страница с формой загрузки PDF файла.
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', ['model' => new PdfUploadForm()]);
    }

    /**
     * Загрузить файл на сервер и конвертировать страницы в презентацию.
     * @return string|\yii\web\Response
     */
    public function actionUpload()
    {
        $pdf = new Pdf();
        if (Yii::$app->request->isPost) {
            $uploadForm = new PdfUploadForm();
            $uploadForm->pdfFile = UploadedFile::getInstance($uploadForm, 'pdfFile');
            $uploadResult = $uploadForm->upload();
            if ($uploadResult instanceof Pdf) {
                $pdf = $uploadResult;
            } else {
                Yii::$app->session->addFlash('pdfError', $pdf->errors);
                return $this->redirect('/');
            }
        }
        if ($pdf->validate()) {
            if ($pdf->convertToImage()) {
                return $this->redirect('/pdf/' . $pdf->id);
            } else {
                Yii::$app->session->addFlash('pdfError', 'Не удалось конвертировать PDF файл в презентацию по неизвестной причине');
            }
        } else {
            Yii::$app->session->addFlash('pdfError', $pdf->errors);
        }

        return $this->render('upload', ['pdf' => $pdf]);
    }
}

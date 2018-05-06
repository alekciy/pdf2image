<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Отвечает за загрузку файла на сервер и проверку типа и размера.
 * @property UploadedFile $pdfFile
 */
class PdfUploadForm extends Model
{
    /** @var  UploadedFile */
    public $pdfFile;

    public function rules()
    {
        return [
            [
                ['pdfFile'],
                'file',
                'skipOnEmpty' => false,
                'mimeTypes' => 'application/pdf',
                'maxSize' => Yii::$app->params['pdfFile']['maxSize'],
                'checkExtensionByMimeType' => true,
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'pdfFile' => 'PDF файл',
        ];
    }

    /**
     * Загрузит файл на сервер и вернет его в случае успеха.
     * @return Pdf|bool
     */
    public function upload()
    {
        if ($this->validate()) {
            $pdf = new Pdf();
            if ($this->pdfFile->saveAs($pdf->createFilePath())) {
                return $pdf;
            }
        }
        return false;
    }
}

<?php

namespace app\models;

use Yii;
use Ramsey\Uuid\Uuid;
use yii\base\Exception;
use yii\base\Model;

/**
 * @property string $id Идентификатор файла.
 * @property string $filePath Абсолютный путь до файла.
 * @property integer $totalPage Количество страниц.
 * @property string $orientation Ориентация страниц.
 * @property array $info Метаданные о файле.
 * @property array $imagePageList Список файлов изображений страниц документа.
 * @property string $zipPath Абсолютный путь до ZIP файла с презентацией.
 */
class Pdf extends Model
{
    const ORIENTATION_LANDSCAPE = 'landscape';
    const ORIENTATION_PORTRAIT = 'portrait';
    const ORIENTATION_MIXED = 'mixed'; // в файле есть как альбомные так и портретные страницы

    const INFO_TOTAL_PAGE = 'totalPage';
    const INFO_ORIENTATION = 'orientation';

    public $id;
    /** @var Converter $_conv */
    protected $_conv = null;
    protected $_info = null;
    protected $_renderFieldList = ['id'];

    public function init()
    {
        if (empty($this->id)) {
            $this->id = Uuid::uuid4()->toString();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return $this->_renderFieldList;
    }

    /**
     * Задаем поля отдаваемые на фронт. Для конфигурирования в конкретном действии контроллера.
     * @param array $fieldList Массив имен полей для рендеринга на клиент.
     */
    public function setRenderFields($fieldList)
    {
        $this->_renderFieldList = ['id'];
        if (is_array($fieldList)) {
            foreach ($fieldList as $field) {
                if ($this->hasProperty($field)) {
                    $this->_renderFieldList[] = $field;
                }
            }
        }
    }

    /**
     * Найти PDF по его идентификатору.
     * @param string $id Идентификатор PDF файла.
     * @return Pdf|null
     */
    public static function findById($id)
    {
        if (Uuid::isValid($id)
            && file_exists(Yii::getAlias("@app/web/{$id}/{$id}.pdf"))
        ) {
            $model = new self();
            $model->id = $id;
            return $model;
        }
        return null;
    }

    /**
     * @return Converter
     * @throws \Spatie\PdfToImage\Exceptions\PageDoesNotExist
     */
    protected function getConverter()
    {
        if ($this->_conv === null) {
            $this->_conv = new Converter($this->filePath);
        }
        return $this->_conv;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            ['filePath', function ($attribute, $params) {
                if (!file_exists($this->$attribute)) {
                    $this->addError($attribute, 'Такого файла не существует');
                }
            }],
            ['totalPage',
                'integer',
                'min' => 1,
                'max' => Yii::$app->params['pdfFile']['maxPage'],
                'message' => 'PDF должен содержать не более {value} страниц',
            ],
            ['orientation', function ($attribute, $params) {
                if ($this->orientation != self::ORIENTATION_PORTRAIT) {
                    $this->addError($attribute, 'Ориентация все страниц файла должна быть портретной');
                }
            }],
        ];
    }

    public function attributeLabels()
    {
        return [
            'totalPage' => 'Максимальное количество страниц',
            'pdfFile' => 'PDF файл',
        ];
    }

    /**
     * Абсолютный путь до PDF файла.
     * @return string
     * @throws Exception
     */
    public function getFilePath()
    {
        return Yii::getAlias("@app/web/{$this->id}/{$this->id}.pdf");
    }

    /**
     * Вернет абсолютный путь до PDF файла. Метод подготавливает ФС сервера гарантируя наличие конечной директории.
     */
    public function createFilePath()
    {
        $dir = dirname($this->filePath);
        if (!file_exists($dir)) {
            if (false === mkdir($dir, 0775, true)) {
                throw new Exception("Не удалось создать директорию $dir");
            };
        }
        return $this->filePath;
    }

    /**
     * Вернет информацию о файле в виде массива либо значение конкретного поля $fieldName.
     * @param string $fieldName Имя поля
     * @return mixed
     */
    public function getInfo($fieldName = null)
    {
        if ($this->_info === null) {
            $this->_info = [];
            if (file_exists($this->filePath)) {
                $cmd = 'pdfinfo -l ' . Yii::$app->params['pdfFile']['maxPage'] . ' ' . escapeshellarg($this->filePath);
                exec($cmd, $outputLineList, $exitCode);
                if ($exitCode == 0) {
                    $pageOrientationList = [];
                    foreach ($outputLineList as $outputLine) {
                        if (preg_match('~^(?<key>[^:]+):{1}\s+(?<value>.*)~', $outputLine, $matches)) {
                            switch (true) {
                                case $matches['key'] == 'Pages':
                                    $this->_info[self::INFO_TOTAL_PAGE] = (integer)$matches['value'];
                                    break;
                                case preg_match("~^Page\s+(\d+)\ssize~", $matches['key']) == 1:
                                    if (preg_match('~^(?<width>[0-9\.]+)\s?x\s?(?<height>[0-9\.]+).*~', $matches['value'], $matchSize)) {
                                        $pageOrientationList[] = $matchSize['height'] / $matchSize['width'] > 1
                                            ? self::ORIENTATION_PORTRAIT
                                            : self::ORIENTATION_LANDSCAPE;
                                    }
                                    break;
                            }
                        }
                    }
                    if (!empty($pageOrientationList)) {
                        $pageOrientationList = array_unique($pageOrientationList);
                        $this->_info[self::INFO_ORIENTATION] = count($pageOrientationList) == 1
                            ? $pageOrientationList[0]
                            : self::ORIENTATION_MIXED;
                    }
                }
            }
        }
        if ($fieldName === null) {
            return $this->_info;
        }
        return isset($this->_info[$fieldName])
            ? $this->_info[$fieldName]
            : null;
    }

    /**
     * Количество страниц в PDF файле.
     * @return integer|null
     */
    public function getTotalPage()
    {
        return $this->getInfo(self::INFO_TOTAL_PAGE);
    }

    /**
     * Ориентация страниц.
     * @return string|null
     */
    public function getOrientation()
    {
        return $this->getInfo(self::INFO_ORIENTATION);
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate()
    {
        if ($this->hasErrors()
            && file_exists($this->filePath)
        ) {
            $dir = dirname($this->filePath);
            exec('rm -fR ' . escapeshellarg($dir));
        }
        parent::afterValidate();
    }

    /**
     * Конвертирует страницы документа в JPG файлы сохраняя их в директорию нахождения $this->filePath.
     * Вернет true в случае успеха.
     * @return boolean
     */
    public function convertToImage()
    {
        // Количество процессорных ядер
        $cmd = 'echo $(( $(lscpu | awk \'/^Socket/{ print $2 }\') * $(lscpu | awk \'/^Core/{ print $4 }\') ))';
        $processorCores = (int) system($cmd, $exitCode);

        $cmd = 'gs -dNOPAUSE -sDEVICE=jpeg -dFirstPage=1 -dJPEGQ=100 -r300'
            . ' -dNumRenderingThreads=' . $processorCores
            . ' -dLastPage=' . $this->totalPage
            . ' -sOutputFile=' . escapeshellarg(dirname($this->filePath) . '/page_%d.jpg')
            . ' -q ' . escapeshellarg($this->filePath)
            . ' -c quit';
        exec($cmd, $outputLineList, $exitCode);
        return $exitCode == 0;
    }

    /**
     * Вернет массив путей (относительно web папки) до файлов с изображениями страниц документа.
     * @return array
     */
    public function getImagePageList()
    {
        $result = [];
        $webRoot = Yii::getAlias('@app/web');
        $dir = dirname($this->filePath);
        foreach (glob("{$dir}/page_*") as $fileName) {
            $result[] = str_replace($webRoot, '', $fileName);
        }
        return $result;
    }

    /**
     * Вернет абсолютный путь до файла с архивом презентации.
     * @return string
     */
    public function getZipPath()
    {
        $webRoot = Yii::getAlias('@app/web');
        $zipFile = Yii::getAlias("@app/web/{$this->id}/{$this->id}.zip");
        $imagePageList = $this->imagePageList;
        if (!file_exists($zipFile)
            && !empty($imagePageList)
        ) {
            copy(Yii::getAlias('@app/web/slider.zip'), $zipFile);
            $templateZip = new \ZipArchive();
            $templateZip->open($zipFile);
            foreach ($imagePageList as $image) {
                $templateZip->addFile($webRoot . $image, '/slider/images/' . pathinfo($image, PATHINFO_BASENAME));
            }
            $indexFileContent = Yii::$app->view->renderFile(Yii::getAlias('@app/views/pdf/_index.php'), ['pdf' => $this]);
            $templateZip->addFromString('/slider/index.html', $indexFileContent);
            $templateZip->close();

        }
        return $zipFile;
    }
}

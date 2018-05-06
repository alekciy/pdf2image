<?php

namespace app\models;

use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;

/**
 * Конвертер PDF файла в картинки.
 */
class Converter extends \Spatie\PdfToImage\Pdf
{
    /**
     * @param string $pdfFile URL или путь в файловой системе до PDF файла.
     * @throws PdfDoesNotExist
     */
    public function __construct($pdfFile)
    {
//        parent::__construct($pdfFile);
        if ( filter_var($pdfFile, FILTER_VALIDATE_URL)
            && !file_exists($pdfFile)
        ) {
            throw new PdfDoesNotExist();
        }
        $t = microtime(true);
//        $this->imagick = new \Imagick($pdfFile);
        $this->imagick = new \Imagick();
        $this->imagick->setFilename($pdfFile);
        var_dump($this->imagick->getNumberImages());
        $ct = microtime(true); var_dump(intval(($ct-$t)*1000)); $t = microtime(true);
        $this->pdfFile = $pdfFile;

        // Вызов \Spatie\PdfToImage::getNumberOfPages() не подходит ввиду долгой работы при большом количестве страниц
        $cmd = '/bin/bash -c "set -o pipefail ; pdfinfo ' . escapeshellarg($pdfFile) . ' | grep Pages | awk \'{print $2}\'"';
        $totalPage = (int) system($cmd, $exitCode);
        $this->numberOfPages = $exitCode == 0
            ? $totalPage
            : 0;
        $ct = microtime(true); var_dump(intval(($ct-$t)*1000)); $t = microtime(true);

    }

}
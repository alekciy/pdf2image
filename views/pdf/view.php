<?php
/* @var $this yii\web\View */
$this->title = 'Веб презентация';
?>

<div>
    <a href="/pdf/download?id=<?=$pdf->id?>" class="btn btn-info" role="button">Скачать презентацию</a>
</div>
<!-- Fotorama -->
<div class="fotorama"
    data-navposition="top"
    data-width="90%"
    data-allowfullscreen="native"
    data-nav="thumbs"
    data-loop="true"
    data-fit="contain"
    data-click="false"
    data-swipe="true"
    data-keyboard="true"
>
    <?php
    /** @var \app\models\Pdf $pdf */
    foreach ($pdf->imagePageList as $img):
    ?>
        <img src="<?=$img?>">
    <?php endforeach; ?>
</div>

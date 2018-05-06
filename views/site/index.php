<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
$this->title = 'Конвертер PDF в веб презентацию';
?>

<h2>PDF в JPG</h2>
<?php $form = ActiveForm::begin(['action' => 'site/upload', 'options' => ['enctype' => 'multipart/form-data']]) ?>
    <div class="form-group">
        <?= $form->field($model, 'pdfFile')->fileInput() ?>
    </div>
    <button type="submit" class="btn btn-default">конвертировать</button>
<?php ActiveForm::end() ?>
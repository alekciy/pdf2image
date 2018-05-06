<?php
/** @var \app\models\Pdf $pdf */
?>

<?php if (!empty($pdf->errors)): ?>
    <div class="alert alert-danger">
        <strong>Ошибка!</strong> В процессе загрузки возникли проблемы. Попробуйте загрузить еще раз
        перейдя по <a href="/" title="На главную">ссылке</a>.
    </div>
<?php endif; ?>

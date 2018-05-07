<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="utf-8">
    <meta charset="<?= Yii::$app->language ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= \yii\helpers\Html::csrfMetaTags() ?>
    <title><?= \yii\helpers\Html::encode($this->title) ?></title>
    <link rel="stylesheet" href="/assets/fotorama-4.6.4/fotorama.min.css">
    <link rel="stylesheet" href="/assets/bootstrap-3.3.7/css/bootstrap.min.css">
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
        <div class="container">
            <?php foreach(Yii::$app->session->getFlash('pdfError', []) as $messageList): ?>
				<?php if (is_array($messageList)): ?>
					<?php foreach ($messageList as $fieldName => $messages): ?>
                	    <?php foreach ($messages as $mess): ?>
                	        <div class="alert alert-danger" role="alert"><?=$mess?></div>
                	    <?php endforeach ?>
                	<?php endforeach ?>
				<?php else: ?>
					<div class="alert alert-danger" role="alert"><?=$messageList?></div>
				<?php endif ?>
            <?php endforeach ?>

            <?= $content ?>
        </div>
    </div>

    <script src="/assets/jquery_1.11.1.min.js"></script>
    <script src="/assets/fotorama-4.6.4/fotorama.min.js"></script>
    <script src="/assets/bootstrap-3.3.7/js/bootstrap.min.js"></script>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

<!DOCTYPE html>
<html lang="ru">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Презентация <?=$pdf->id?></title>
		<link rel="stylesheet" href="./assets/fotorama-4.6.4/fotorama.min.css">
		<link rel="stylesheet" href="./assets/bootstrap-3.3.7/css/bootstrap.min.css">
	</head>
	<body>
		<div class="container">
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
                    $img = pathinfo($img, PATHINFO_BASENAME);
                ?>
                    <img src="./images/<?=$img?>">
                <?php endforeach; ?>
			</div>
		</div>

	<script src="./assets/jquery_1.11.1.min.js"></script>
	<script src="./assets/fotorama-4.6.4/fotorama.min.js"></script>
	<script src="./assets/bootstrap-3.3.7/js/bootstrap.min.js"></script>
	</body>
</html>

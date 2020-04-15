<?php

/* @var $this yii\web\View */
/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
Hello <?= $requestuser->username ?>,

Grats, your request has been accepted. Find your key below:

<?= $offer->description ?>: <?= $offer->key ?>

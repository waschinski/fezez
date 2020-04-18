<?php

/* @var $this yii\web\View */
/* @var $user models\User */
/* @var $description string */
/* @var $requestuser models\User */

$offersLink = Yii::$app->urlManager->createAbsoluteUrl(['offer/myoffers']);
?>
Hello <?= $user->username ?>,

<?= $requestuser->username ?> has requested one of your offers:

<?= $description ?>

Follow the link below to accept or reject the request:

<?= $offersLink ?>

<?php

/* @var $this yii\web\View */
/* @var $user models\User */
/* @var $description string */
/* @var $requestuser models\User */

$offersLink = Yii::$app->urlManager->createAbsoluteUrl(['offer/myoffers']);
?>
<?= Yii::t('mail', 'Hello') ?> <?= $user->username ?>,

<?= Yii::t('mail', '{username} has requested one of your offers:'), [
    'username' => $requestuser->username
] ?>

<?= $description ?>

<?= Yii::t('mail', 'Follow the link below to accept or reject the request:') ?>

<?= $offersLink ?>

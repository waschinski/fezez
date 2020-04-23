<?php

/* @var $this yii\web\View */
/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<?= Yii::t('mail', 'Hello') ?> <?= $requestuser->username ?>,

<?= Yii::t('mail', 'Your payment for {description} has been confirmed. Find your key below:', [
    'description' => $offer->description
]) ?>

<?= $offer->key ?>

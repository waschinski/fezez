<?php

/* @var $this yii\web\View */
/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<?= Yii::t('mail', 'Hello') ?> <?= $user->username ?>,

<?= Yii::t('mail', 'The request for {description} has been canceled.', [
    'description' => $offer->description
]) ?>

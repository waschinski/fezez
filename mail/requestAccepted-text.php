<?php

/* @var $this yii\web\View */
/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<?= Yii::t('mail', 'Hello') ?> <?= $requestuser->username ?>,

<?= Yii::t('mail', 'Grats, your request for {description} has been accepted. Find your key below:'), [
    'description' => $offer->description
] ?>

<?= $offer->key ?>

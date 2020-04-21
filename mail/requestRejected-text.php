<?php

/* @var $this yii\web\View */
/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<?= Yii::t('mail', 'Hello') ?> <?= $requestuser->username ?>,

<?= Yii::t('mail', 'Sorry, but your request for {description} has been rejected.'), [
    'description' => $offer->description
] ?>

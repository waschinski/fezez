<?php

/* @var $this yii\web\View */
/* @var $requestuser models\User */
/* @var $offer models\Offer */

$paymentLink = $user->paypalme_link . '/' . $offer->price . $currencycode;
?>
<?= Yii::t('mail', 'Hello') ?> <?= $requestuser->username ?>,

<?= Yii::t('mail', 'You have requested to buy {description} from {seller}.', [
    'description' => $offer->description,
    'seller' => $user->username
]) ?>

<?= Yii::t('mail', 'Please pay now using the following link:') ?>

<?= $paymentLink ?>

<?= Yii::t('mail', 'Once {seller} confirms receiving your payment you will get your key.', [
    'seller' => $user->username
]) ?>

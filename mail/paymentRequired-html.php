<?php
use yii\helpers\Html;

/* @var $requestuser models\User */
/* @var $offer models\Offer */

$paymentLink = $user->paypalme_link . '/' . $offer->price . $currencycode;
?>
<div class="request-buy">
    <p><?= Yii::t('mail', 'Hello') ?> <?= Html::encode($requestuser->username) ?>,</p>

    <p><?= Yii::t('mail', 'You have requested to buy {description} from {seller}.', [
        'description' => Html::encode($offer->description),
        'seller' => Html::encode($user->username)
    ]) ?></p>

    <p><?= Yii::t('mail', 'Please pay now using the following link:') ?></p>

    <p><?= $paymentLink ?></p>
    
    <p><?= Yii::t('mail', 'Once {seller} confirms receiving your payment you will get your key.', [
        'seller' => Html::encode($user->username)
    ]) ?></p>
</div>

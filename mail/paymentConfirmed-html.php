<?php
use yii\helpers\Html;

/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<div class="request-paymentconfirmed">
    <p><?= Yii::t('mail', 'Hello') ?> <?= Html::encode($requestuser->username) ?>,</p>

    <p><?= Yii::t('mail', 'Your payment for {description} has been confirmed. Find your key below:', [
        'description' => Html::encode($offer->description)
    ]) ?></p>

    <p><?= Html::encode($offer->key) ?></p>
</div>

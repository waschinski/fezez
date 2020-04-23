<?php
use yii\helpers\Html;

/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<div class="request-buy">
    <p><?= Yii::t('mail', 'Hello') ?> <?= Html::encode($user->username) ?>,</p>

    <p><?= Yii::t('mail', '{username} has requested to buy {description} from you.', [
        'username' => Html::encode($requestuser->username),
        'description' => Html::encode($offer->description)
    ]) ?></p>

    <p><?= Yii::t('mail', 'An email containing your payment link has been sent to {username}.', [
        'username' => Html::encode($requestuser->username)
    ]) ?></p>

    <p><?= Yii::t('mail', 'Please confirm the payment once received.') ?></p>
</div>

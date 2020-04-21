<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user models\User */
/* @var $description string */
/* @var $requestuser models\User */

$offersLink = Yii::$app->urlManager->createAbsoluteUrl(['offer/myoffers']);
?>
<div class="request-received">
    <p><?= Yii::t('mail', 'Hello') ?> <?= Html::encode($user->username) ?>,</p>

    <p><?= Yii::t('mail', '{username} has requested one of your offers:', [
        'username' => Html::encode($requestuser->username)
    ]) ?></p>

    <p><?= Html::encode($description) ?></p>
    
    <p><?= Yii::t('mail', 'Follow the link below to accept or reject the request:') ?></p>

    <p><?= Html::a(Html::encode($offersLink), $offersLink) ?></p>
</div>

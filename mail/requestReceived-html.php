<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user models\User */
/* @var $requestuser models\User */

$offersLink = Yii::$app->urlManager->createAbsoluteUrl(['offer/myoffers']);
?>
<div class="request-received">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p><?= Html::encode($requestuser->username) ?> has requested one of your offers.</p>
    
    <p>Follow the link below to accept or reject the request:</p>

    <p><?= Html::a(Html::encode($offersLink), $offersLink) ?></p>
</div>

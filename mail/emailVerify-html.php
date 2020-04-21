<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user models\User */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
?>
<div class="verify-email">
    <p><?= Yii::t('mail', 'Hello') ?> <?= Html::encode($user->username) ?>,</p>

    <p><?= Yii::t('mail', 'Follow the link below to verify your email:') ?></p>

    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>
</div>

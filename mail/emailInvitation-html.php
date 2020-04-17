<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user models\User */

$signupLink = Yii::$app->urlManager->createAbsoluteUrl(['site/signup', 'email' => $invitation->email, 'code' => $invitation->code]);
?>
<div class="invitation-email">
    <p>Hello,</p>

    <p>Your friend <?= Html::encode($user->username) ?> has invited you to join Fezez.</p>
    
    <p>Follow the link below to signup:</p>

    <p><?= Html::a(Html::encode($signupLink), $signupLink) ?></p>

    <p>This link is only valid for 24 hours and can only be used when signing up with your email address.</p>
</div>

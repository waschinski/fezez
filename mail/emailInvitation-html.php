<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user models\User */

$signupLink = Yii::$app->urlManager->createAbsoluteUrl(['site/signup', 'email' => $invitation->email, 'code' => $invitation->code]);
?>
<div class="invitation-email">
    <p><?= Yii::t('mail', 'Hello') ?>,</p>

    <p><?= Yii::t('mail', 'Your friend {username} has invited you to join Fezez.'), [
        'username' => Html::encode($user->username)
    ] ?></p>
    
    <p><?= Yii::t('mail', 'Follow the link below to signup:') ?></p>

    <p><?= Html::a(Html::encode($signupLink), $signupLink) ?></p>

    <p><?= Yii::t('mail', 'This link is only valid for 24 hours and can only be used when signing up with your email address.') ?></p>
</div>

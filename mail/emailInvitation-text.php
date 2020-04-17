<?php

/* @var $this yii\web\View */
/* @var $user models\User */

$signupLink = Yii::$app->urlManager->createAbsoluteUrl(['site/signup', 'email' => $invitation->email, 'code' => $invitation->code]);
?>
Hello,

Your friend <?= $user->username ?> has invited you to join Fezez.
    
Follow the link below to signup:

<?= $signupLink ?>

This link is only valid for 24 hours and can only be used when signing up with your email address.

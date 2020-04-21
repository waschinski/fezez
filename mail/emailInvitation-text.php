<?php

/* @var $this yii\web\View */
/* @var $user models\User */

$signupLink = Yii::$app->urlManager->createAbsoluteUrl(['site/signup', 'email' => $invitation->email, 'code' => $invitation->code]);
?>
<?= Yii::t('mail', 'Hello') ?>,

<?= Yii::t('mail', 'Your friend {username} has invited you to join Fezez.', [
    'username' => $user->username
]) ?>
    
<?= Yii::t('mail', 'Follow the link below to signup:') ?>

<?= $signupLink ?>

<?= Yii::t('mail', 'This link is only valid for 24 hours and can only be used when signing up with your email address.') ?>

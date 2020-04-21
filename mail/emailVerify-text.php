<?php

/* @var $this yii\web\View */
/* @var $user models\User */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
?>
<?= Yii::t('mail', 'Hello') ?> <?= $user->username ?>,

<?= Yii::t('mail', 'Follow the link below to verify your email:') ?>

<?= $verifyLink ?>

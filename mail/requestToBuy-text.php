<?php

/* @var $this yii\web\View */
/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<?= Yii::t('mail', 'Hello') ?> <?= $user->username ?>,

<?= Yii::t('mail', '{username} has requested to buy {description} from you.', [
    'username' => $requestuser->username,
    'description' => $offer->description
]) ?>

<?= Yii::t('mail', 'An email containing your payment link has been sent to {username}.', [
    'username' => $requestuser->username
]) ?>

<?= Yii::t('mail', 'Please confirm the payment once received.') ?>

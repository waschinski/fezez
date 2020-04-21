<?php
use yii\helpers\Html;

/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<div class="request-rejected">
    <p><?= Yii::t('mail', 'Hello') ?> <?= Html::encode($user->username) ?>,</p>

    <p><?= Yii::t('mail', 'The request for {description} has been canceled.', [
        'description' => Html::encode($offer->description)
    ]) ?></p>
</div>

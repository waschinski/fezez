<?php
use yii\helpers\Html;

/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<div class="request-rejected">
    <p><?= Yii::t('mail', 'Hello') ?> <?= Html::encode($requestuser->username) ?>,</p>

    <p><?= Yii::t('mail', 'Sorry, but your request for {description} has been rejected.'), [
        'description' => Html::encode($offer->description)
    ] ?></p>
</div>

<?php
use yii\helpers\Html;

/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<div class="request-accepted">
    <p>Hello <?= Html::encode($requestuser->username) ?>,</p>

    <p>Grats, your request has been accepted. Find your key below:</p>

    <p><?= Html::encode($offer->description) ?>: <?= Html::encode($offer->key) ?></p>
</div>

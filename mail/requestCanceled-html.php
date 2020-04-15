<?php
use yii\helpers\Html;

/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<div class="request-rejected">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>The request for <?= Html::encode($offer->description) ?> has been canceled.</p>
</div>

<?php
use yii\helpers\Html;

/* @var $requestuser models\User */
/* @var $offer models\Offer */

?>
<div class="request-rejected">
    <p>Hello <?= Html::encode($requestuser->username) ?>,</p>

    <p>Sorry, but your request for <?= Html::encode($offer->description) ?> has been rejected.</p>
</div>

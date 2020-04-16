<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\OfferForm */

namespace app\models;

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Edit Offer';
$this->params['breadcrumbs'][] = ['label' => 'My Offers', 'url' => ['offer/myoffers']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="offer-myoffers">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Please give a precise description of the key so other users know what they are requesting.</p>
    <p>The key will only be visible to you until you accept a request from another user, then it will be shared with that user.</p>
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-newoffer']); ?>

                <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>

                <?= $form->field($model, 'description')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'key') ?>

                <div class="form-group">
                    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'editoffer-button']) ?>
                    <?= Html::a('Cancel', ['/offer/myoffers'], ['class' => 'btn btn-light']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

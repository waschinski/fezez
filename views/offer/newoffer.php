<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\OfferForm */

namespace app\models;

use Yii;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = \Yii::t('app', 'New Offer');
$this->params['breadcrumbs'][] = ['label' => 'My Offers', 'url' => ['offer/myoffers']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="offer-myoffers">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= \Yii::t('app', 'Please give a precise description of the key so other users know what they are requesting.') ?></p>
    <p><?= \Yii::t('app', 'The key will only be visible to you until you accept a request from another user, then it will be shared with that user.') ?></p>
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-newoffer']); ?>

                <?= $form->field($model, 'description')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'key') ?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary', 'name' => 'newoffer-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\InviteForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Invite a friend';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-invite">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please provide your friends email address in order to send an invitation code required for signup:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-invite']); ?>

                <?= $form->field($model, 'email') ?>

                <div class="form-group">
                    <?= Html::submitButton('Invite', ['class' => 'btn btn-primary', 'name' => 'invite-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

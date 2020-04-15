<?php

namespace app\models;

use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$this->title = 'Marketplace';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-marketplace">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Have a look at all the keys currently offered by other users. <span style="font-style:italic"><?= Html::a('Got a spare key too?', ['offer/newoffer']) ?></span></p>
<?php
$dataProvider = new ActiveDataProvider([
    'query' => Offer::find()
        ->where(['status' => Offer::STATUS_ACTIVE])
        ->andWhere(['not', ['user_id' => \Yii::$app->user->identity->ID]]),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'description',
        'created_at:datetime',
        // 'statusdescription',
[  
            'class' => 'yii\grid\ActionColumn',
            // 'contentOptions' => ['style' => 'width:260px;'],
            'header'=> 'Actions',
            'template' => '{request}',
            'buttons' => [
                'request' => function ($url, $model) {
                    return $model->status == Offer::STATUS_ACTIVE ? Html::a('Request', ['offer/requestoffer'], [
                                'title' => 'Request',
                                'class'=> 'btn btn-primary btn-xs',
                                'data'=>[
                                    'method'=> 'post',
                                    'params'=> [
                                        'id' => $model->id
                                    ],
                                ]
                    ]) : '';
                },
            ],
        ],
    ]
]);
?>
</div>

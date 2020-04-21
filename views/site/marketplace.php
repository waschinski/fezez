<?php

namespace app\models;

use Yii;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$this->title = \Yii::t('app', 'Marketplace');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-marketplace">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= \Yii::t('app', 'Have a look at all the keys currently offered by other users.') ?> <span style="font-style:italic"><?= Html::a(Yii::t('app', 'Got a spare key too?'), ['offer/new']) ?></span></p>
<?php
$dataProvider = new ActiveDataProvider([
    'query' => Offer::find()
        ->where(['status' => Offer::STATUS_ACTIVE])
        ->andWhere(['not', ['user_id' => \Yii::$app->user->identity->ID]])
        ->orderBy(['created_at' => SORT_DESC]),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'description',
        [
            'attribute' => 'created_at',
            'label' => Yii::t('app', 'Offered at'),
            'format' => 'datetime'
        ],
        // 'statusdescription',
[  
            'class' => 'yii\grid\ActionColumn',
            // 'contentOptions' => ['style' => 'width:260px;'],
            'header'=> Yii::t('app', 'Actions'),
            'template' => '{request}',
            'buttons' => [
                'request' => function ($url, $model) {
                    return $model->status == Offer::STATUS_ACTIVE ? Html::a(Yii::t('app', 'Request'), ['request/request'], [
                                'title' => Yii::t('app', 'Request'),
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

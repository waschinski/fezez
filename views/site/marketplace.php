<?php

namespace app\models;

use Yii;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\OfferSearch */

$this->title = \Yii::t('app', 'Marketplace');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-marketplace">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= \Yii::t('app', 'Have a look at all the keys currently offered by other users.') ?> <span style="font-style:italic"><?= Html::a(Yii::t('app', 'Got a spare key too?'), ['offer/new']) ?></span></p>
<?php
Pjax::begin();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'description',
        [
            'attribute' => 'displayprice',
            'label' => Yii::t('app', 'Price'),
        ],
        [
            'attribute' => 'created_at',
            'label' => Yii::t('app', 'Offered at'),
            'format' => 'datetime'
        ],
        [  
            'class' => 'yii\grid\ActionColumn',
            // 'contentOptions' => ['style' => 'width:260px;'],
            'header'=> Yii::t('app', 'Actions'),
            'template' => '{request} {buy}',
            'buttons' => [
                'request' => function ($url, $model) {
                    return $model->status == Offer::STATUS_ACTIVE && $model->price == 0 ? Html::a(Yii::t('app', 'Request'), ['offer/request'], [
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
                'buy' => function ($url, $model) {
                    return $model->status == Offer::STATUS_ACTIVE && $model->price > 0 ? Html::a(Yii::t('app', 'Buy'), ['offer/buy'], [
                                'title' => Yii::t('app', 'Buy'),
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

Pjax::end();
?>
</div>

<?php

namespace app\models;

use Yii;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\RequestSearch */

$this->title = \Yii::t('app', 'My Requests');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="offer-myrequests">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= \Yii::t('app', 'Below you will find all keys you have been requesting from other users.') ?></p>
<?php
Pjax::begin();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'description',
        [  
            'class' => 'yii\grid\ActionColumn',
            'header'=> 'Key',
            'template' => '{view} {show}',
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::tag('span', $model->key, ['id' => 'key-' . $model->id, 'class' => 'hidden']);
                },
                'show' => function ($url, $model) {
                    return $model->key != '' ? Html::button(Yii::t('app', 'Show'), [
                            'onclick' => 'this.classList.add("hidden"); document.getElementById("key-' . $model->id . '").classList.remove("hidden");',
                            'class' =>'btn btn-primary btn-xs',
                    ]) : '';
                },
            ]
        ],
        [
            'attribute' => 'offer.displayprice',
            'label' => Yii::t('app', 'Price'),
        ],
        [
            'attribute' => 'created_at',
            'label' => Yii::t('app', 'Requested at'),
            'format' => 'datetime',
        ],
        [
            'attribute' => 'state',
            'label' => Yii::t('app', 'State'),
        ],
        [  
            'class' => 'yii\grid\ActionColumn',
            // 'contentOptions' => ['style' => 'width:260px;'],
            'header'=> Yii::t('app', 'Actions'),
            'template' => '{cancel}',
            'buttons' => [
                'cancel' => function ($url, $model) {
                    return $model->status == Request::STATUS_WAITING ? Html::a(Yii::t('app', 'Cancel'), ['request/cancel'], [
                                'title' => Yii::t('app', 'Cancel'),
                                'class'=> 'btn btn-primary btn-xs',
                                'data'=>[
                                    'method'=> 'post',
                                    'params'=> [
                                        'id' => $model->id
                                    ],
                                ]
                    ]) : '';
                }
            ],
        ],
    ]
]);

Pjax::end();
?>
</div>

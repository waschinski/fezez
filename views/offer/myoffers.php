<?php

namespace app\models;

use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$this->title = 'My Offers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="offer-myoffers">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Below you will find all keys you are currently offering. <span style="font-style:italic"><?= Html::a('Got a new spare key?', ['offer/newoffer']) ?></span></p>
<?php
$dataProvider = new ActiveDataProvider([
    'query' => Offer::find()
        ->where(['user_id' => \Yii::$app->user->identity->ID])
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
            'class' => 'yii\grid\ActionColumn',
            'header'=> 'Key',
            'template' => '{view} {show}',
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::tag('span', $model->key, ['id' => 'key-' . $model->id, 'class' => 'hidden']);
                },
                'show' => function ($url, $model) {
                    return Html::button('Show', [
                            'onclick' => 'this.classList.add("hidden"); document.getElementById("key-' . $model->id . '").classList.remove("hidden");',
                            'class' =>'btn btn-primary btn-xs',
                    ]);
                },
            ]
        ],
        [
            'attribute' => 'created_at',
            'label' => 'Offered at',
            'format' => 'datetime'
        ],
        'state',
        [  
            'class' => 'yii\grid\ActionColumn',
            'header'=> 'Actions',
            'template' => '{activate} {deactivate} {reject} {accept}',
            'buttons' => [
                'activate' => function ($url, $model) {
                    return $model->status == Offer::STATUS_INACTIVE ? Html::a('Activate', ['offer/setofferstatus'], [
                                'title' => 'Activate',
                                'class' => 'btn btn-primary btn-xs',
                                'data' =>[
                                    'method' => 'post',
                                    'params' => [
                                        'id' => $model->id,
                                    'status' => Offer::STATUS_ACTIVE
                                    ],
                                ]
                    ]) : '';
                },
                'deactivate' => function ($url, $model) {
                    return $model->status == Offer::STATUS_ACTIVE ? Html::a('Deactivate', ['offer/setofferstatus'], [
                                'title' => 'Deactivate',
                                'class' => 'btn btn-primary btn-xs',
                                'data' =>[
                                    'method' => 'post',
                                    'params' => [
                                        'id' => $model->id,
                                    'status' => Offer::STATUS_INACTIVE
                                    ],
                                ]
                    ]) : '';
                },
                'reject' => function ($url, $model) {
                    return $model->status == Offer::STATUS_REQUESTED ? Html::a('Reject', ['offer/rejectrequest'], [
                                'title' => 'Reject',
                                'class' => 'btn btn-primary btn-xs',
                                'data' =>[
                                    'method' => 'post',
                                    'params' => [
                                        'id' => $model->id
                                    ],
                                ]
                    ]) : '';
                },
                'accept' => function ($url, $model) {
                    return $model->status == Offer::STATUS_REQUESTED ? Html::a('Accept', ['offer/acceptrequest'], [
                                'title' => 'Accept',
                                'class' => 'btn btn-primary btn-xs',
                                'data' =>[
                                    'method' => 'post',
                                    'params' => [
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

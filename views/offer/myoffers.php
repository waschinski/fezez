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
    <p>Below you will find all keys you are currently offering. <span style="font-style:italic"><?= Html::a('Got a new spare key?', ['offer/new']) ?></span></p>
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
            'template' => '{activate} {deactivate} {reject} {accept} {edit}',
            'buttons' => [
                'activate' => function ($url, $model) {
                    return $model->status == Offer::STATUS_INACTIVE ? Html::a('Activate', ['offer/setstatus'], [
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
                    return $model->status == Offer::STATUS_ACTIVE ? Html::a('Deactivate', ['offer/setstatus'], [
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
                    return $model->status == Offer::STATUS_REQUESTED ? Html::a('Reject', ['request/reject'], [
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
                    return $model->status == Offer::STATUS_REQUESTED ? Html::a('Accept', ['request/accept'], [
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
                'edit' => function ($url, $model) {
                    return $model->status == Offer::STATUS_ACTIVE || $model->status == Offer::STATUS_INACTIVE ? Html::a('Edit', ['offer/edit/' . $model->id], [
                                'title' => 'Edit',
                                'class' => 'btn btn-primary btn-xs',
                    ]) : '';
                },
            ],
        ],
    ]
]);
?>
</div>

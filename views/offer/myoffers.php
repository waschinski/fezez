<?php

namespace app\models;

use Yii;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\OfferSearch */

$this->title = \Yii::t('app', 'My Offers');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="offer-myoffers">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= \Yii::t('app', 'Below you will find all keys you are currently offering.') ?> <span style="font-style:italic"><?= Html::a(Yii::t('app', 'Got a new spare key?'), ['offer/new']) ?></span></p>
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
                    return Html::button(Yii::t('app', 'Show'), [
                            'onclick' => 'this.classList.add("hidden"); document.getElementById("key-' . $model->id . '").classList.remove("hidden");',
                            'class' =>'btn btn-primary btn-xs',
                    ]);
                },
            ]
        ],
        [
            'attribute' => 'displayprice',
            'label' => Yii::t('app', 'Price'),
        ],
        [
            'attribute' => 'created_at',
            'label' => Yii::t('app', 'Offered at'),
            'format' => 'datetime',
        ],
        [
            'attribute' => 'state',
            'label' => Yii::t('app', 'State'),
        ],
        [  
            'class' => 'yii\grid\ActionColumn',
            'header'=> Yii::t('app', 'Actions'),
            'template' => '{activate} {deactivate} {reject} {accept} {edit} {paid}',
            'buttons' => [
                'activate' => function ($url, $model) {
                    return $model->status == Offer::STATUS_INACTIVE ? Html::a(Yii::t('app', 'Activate'), ['offer/setstatus'], [
                                'title' => Yii::t('app', 'Activate'),
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
                    return $model->status == Offer::STATUS_ACTIVE ? Html::a(Yii::t('app', 'Deactivate'), ['offer/setstatus'], [
                                'title' => Yii::t('app', 'Deactivate'),
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
                    return $model->status == Offer::STATUS_REQUESTED ? Html::a(Yii::t('app', 'Reject'), ['request/reject'], [
                                'title' => Yii::t('app', 'Reject'),
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
                    return $model->status == Offer::STATUS_REQUESTED ? Html::a(Yii::t('app', 'Accept'), ['request/accept'], [
                                'title' => Yii::t('app', 'Accept'),
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
                    return $model->status == Offer::STATUS_ACTIVE || $model->status == Offer::STATUS_INACTIVE ? Html::a(Yii::t('app', 'Edit'), ['offer/edit/' . $model->id], [
                                'title' => Yii::t('app', 'Edit'),
                                'class' => 'btn btn-primary btn-xs',
                    ]) : '';
                },
                'paid' => function ($url, $model) {
                    return $model->status == Offer::STATUS_PAYABLE ? Html::a(Yii::t('app', 'Paid'), ['request/paid/' . $model->id], [
                                'title' => Yii::t('app', 'Paid'),
                                'class' => 'btn btn-primary btn-xs',
                    ]) : '';
                },
            ],
        ],
    ]
]);

Pjax::end();
?>
</div>

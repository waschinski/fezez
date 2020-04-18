<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Offer;
use app\models\Request;

class OfferController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['myoffers', 'new', 'edit', 'setstatus'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['myoffers', 'new', 'edit', 'setstatus'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Displays my offers.
     *
     * @return string
     */
    public function actionMyoffers()
    {
        return $this->render('myoffers');
    }

    /**
     * Displays new offer form.
     *
     * @return string
     */
    public function actionNew()
    {
        $offer = new Offer();
        $offer->user_id = \Yii::$app->user->identity->ID;
        if ($offer->load(\Yii::$app->request->post()) && $offer->validate() && $offer->save()) {
            // $offer->discordNewOffer(); // TODO: implement check once active/inactive is on new offer form
            Yii::$app->session->setFlash('success', 'Fezez confirms your offer.');
            return $this->redirect(['offer/myoffers']);
        }

        return $this->render('newoffer', [
            'model' => $offer,
        ]);
    }

    /**
     * Displays edit offer form.
     *
     * @return string
     */
    public function actionEdit()
    {
        $id = Yii::$app->request->get('id');
        $offer = Offer::findOne(['id' => $id, 'user_id' => \Yii::$app->user->identity->ID]);
        if ($offer->load(\Yii::$app->request->post()) && $offer->validate() && $offer->save()) {
            Yii::$app->session->setFlash('success', 'Fezez updated your offer.');
            return $this->redirect(['offer/myoffers']);
        }
        return $this->render('editoffer', [
            'model' => $offer
        ]);
    }

    /**
     * Sets the status of an offer.
     *
     * @return string
     */
    public function actionSetstatus()
    {
        $model = new Offer();
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        if ($status == Offer::STATUS_ACTIVE) {
            $statustext = 'activate';
        } else {
            $statustext = 'deactivate';
        }
        try {
            $offer = Offer::findOne(['id' => $id, 'user_id' => \Yii::$app->user->identity->id]);
            $offer->status = $status;
            if (!$offer->save()) {
                throw new \yii\db\Exception('Error while saving Offer model!');
            }
            if ($status == Offer::STATUS_ACTIVE) {
                $offer->discordNewOffer();
            }
            Yii::$app->session->setFlash('success', 'Fezez '.$statustext.'d your offer.');
            return $this->redirect(['offer/myoffers']);
        } catch(\Throwable $e) {
            Yii::$app->session->setFlash('error', 'Sorry, Fezez was unable to '.$statustext.' your offer status.');
            return $this->render('myoffers');
        }
    }
}

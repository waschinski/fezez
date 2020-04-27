<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Offer;
use app\models\OfferSearch;
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
        $searchModel = new OfferSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('myoffers', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
            Yii::$app->session->setFlash('success', Yii::t('app', 'Fezez confirms your offer.'));
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
            Yii::$app->session->setFlash('success', Yii::t('app', 'Fezez updated your offer.'));
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
            $statustext = Yii::t('app', 'activate');
        } else {
            $statustext = Yii::t('app', 'deactivate');
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
            
            Yii::$app->session->setFlash('success',
                $status == Offer::STATUS_ACTIVE ?
                \Yii::t('app', 'Fezez activated your offer.') : 
                \Yii::t('app', 'Fezez deactivated your offer.')
            );
            return $this->redirect(['offer/myoffers']);
        } catch(\Throwable $e) {
            Yii::$app->session->setFlash('error', 
                $status == Offer::STATUS_ACTIVE ?
                \Yii::t('app', 'Sorry, Fezez was unable to activate your offer.') : 
                \Yii::t('app', 'Sorry, Fezez was unable to deactivate your offer.')
            );
            return $this->render('myoffers');
        }
    }

    /**
     * Request an offer.
     *
     * @return string
     */
    public function actionRequest()
    {
        $id = Yii::$app->request->post('id');
        $transaction = Offer::getDb()->beginTransaction();
        $offer = Offer::findOne($id);
        try {
            if ($offer->status != Offer::STATUS_ACTIVE) {
                throw new \yii\base\Exception('Error while requesting Offer: Offer not active!');
            }
            $offer->status = Offer::STATUS_REQUESTED;
            if (!$offer->save()) {
                throw new \yii\db\Exception('Error while saving Offer model!');
            }
            $request = new Request();
            $request->offer_id = $offer->id;
            $request->user_id = Yii::$app->user->identity->id;
            $request->status = Request::STATUS_WAITING;
            if (!$request->save()) {
                throw new \yii\db\Exception('Error while saving Request model!');
            }
            if (!$offer->sendRequestReceivedEmail()) {
                throw new \yii\db\Exception('Error while sending Request Received email!');
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Fezez sent your request to the trader.'));
            return $this->redirect(['site/index']);
        } catch(\Throwable $e) {
            $transaction->rollBack();
            var_dump($e);
            Yii::$app->session->setFlash('error', Yii::t('app', 'Sorry, Fezez was unable to send your request to the trader.'));
            return $this->render('@app/views/site/marketplace');
        }
    }

    /**
     * Buy an offer.
     *
     * @return string
     */
    public function actionBuy()
    {
        $id = Yii::$app->request->post('id');
        $transaction = Offer::getDb()->beginTransaction();
        $offer = Offer::findOne($id);
        try {
            if ($offer->status != Offer::STATUS_ACTIVE) {
                throw new \yii\base\Exception('Error while requesting Offer: Offer not active!');
            }
            $offer->status = Offer::STATUS_PAYABLE;
            if (!$offer->save()) {
                throw new \yii\db\Exception('Error while saving Offer model!');
            }
            $request = new Request();
            $request->offer_id = $offer->id;
            $request->user_id = Yii::$app->user->identity->id;
            $request->status = Request::STATUS_WAITING;
            if (!$request->save()) {
                throw new \yii\db\Exception('Error while saving Request model!');
            }
            if (!$offer->sendRequestToBuyReceivedEmail()) {
                throw new \yii\db\Exception('Error while sending Request To Buy Received email!');
            }
            if (!$offer->sendPaymentRequiredEmail()) {
                throw new \yii\db\Exception('Error while sending Payment Required email!');
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Fezez sent your request to the trader.'));
            return $this->redirect(['site/index']);
        } catch(\Throwable $e) {
            $transaction->rollBack();
            var_dump($e);
            Yii::$app->session->setFlash('error', Yii::t('app', 'Sorry, Fezez was unable to send your request to the trader.'));
            return $this->render('@app/views/site/marketplace');
        }
    }
}

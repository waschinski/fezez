<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\OfferForm;
use app\models\Offer;

class OfferController extends Controller
{
    /**
     * Displays marketplace.
     *
     * @return string
     */
    public function actionMyrequests()
    {
        return $this->render('myrequests');
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
    public function actionNewoffer()
    {
        $model = new OfferForm();
        if ($model->load(Yii::$app->request->post()) && $model->createoffer()) {
            Yii::$app->session->setFlash('success', 'Fezez confirms your offer.');
            return $this->redirect(['offer/myoffers']);
        }

        return $this->render('newoffer', [
            'model' => $model,
        ]);
    }

    /**
     * Sets the status of an offer.
     *
     * @return string
     */
    public function actionSetofferstatus()
    {
        $model = new OfferForm();
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        if ($model->setofferstatus($id, $status)) {
            if ($status == Offer::STATUS_ACTIVE) {
                $statustext = 'activated';
            } else {
                $statustext = 'deactivated';
            }
            Yii::$app->session->setFlash('success', 'Fezez '.$statustext.' your offer.');
            return $this->redirect(['offer/myoffers']);
        }

        return $this->render('myoffers');
    }

    /**
     * Request an offer.
     *
     * @return string
     */
    public function actionRequestoffer()
    {
        $id = Yii::$app->request->post('id');
        $model = new OfferForm();
        if ($model->requestoffer($id)) {
            Yii::$app->session->setFlash('success', 'Fezez sent your request to the trader.');
            return $this->redirect(['site/index']);
        } else {
            Yii::$app->session->setFlash('error', 'Sorry, Fezez was unable to send your request to the trader.');
        }

        return $this->render('@app/views/site/marketplace');
    }
    
    /**
     * Cancel a request.
     *
     * @return string
     */
    public function actionCancelrequest()
    {
        $id = Yii::$app->request->post('id');
        $model = new OfferForm();
        if ($model->cancelrequest($id)) {
            Yii::$app->session->setFlash('success', 'Fezez confirms canceling the request.');
            return $this->redirect(['offer/myrequests']);
        }

        return $this->render('myrequests');
    }

    /**
     * Reject a request.
     *
     * @return string
     */
    public function actionRejectrequest()
    {
        $id = Yii::$app->request->post('id');
        $model = new OfferForm();
        if ($model->rejectrequest($id)) {
            Yii::$app->session->setFlash('success', 'Fezez confirms rejecting the request.');
            return $this->redirect(['offer/myoffers']);
        }

        return $this->render('myoffers');
    }

    /**
     * Reject a request.
     *
     * @return string
     */
    public function actionAcceptrequest()
    {
        $id = Yii::$app->request->post('id');
        $model = new OfferForm();
        if ($model->acceptrequest($id)) {
            Yii::$app->session->setFlash('success', 'Fezez confirms accepting the request.');
            return $this->redirect(['offer/myoffers']);
        }

        return $this->render('myoffers');
    }
}

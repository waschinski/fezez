<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Request;
use app\models\Offer;

class RequestController extends Controller
{
    /**
     * Displays my requests.
     *
     * @return string
     */
    public function actionMyrequests()
    {
        return $this->render('myrequests');
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
            Yii::$app->session->setFlash('success', 'Fezez sent your request to the trader.');
            return $this->redirect(['site/index']);
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Sorry, Fezez was unable to send your request to the trader.');
            return $this->render('@app/views/site/marketplace');
        }
    }

    /**
     * Cancel a request.
     *
     * @return string
     */
    public function actionCancel()
    {
        $id = Yii::$app->request->post('id');
        $transaction = Request::getDb()->beginTransaction();
        $request = Request::findOne($id);
        try {
            if ($request->status != Request::STATUS_WAITING) {
                throw new \yii\base\Exception('Error while canceling Request: Request not waiting!');
            }
            $request->status = Request::STATUS_DELETED;
            $offer_id = $request->offer_id;
            if (!$request->save()) {
                throw new \yii\db\Exception('Error while saving Request model!');
            }
            // Offer the request has been for
            $offer = Offer::findOne([
                'id' => $offer_id
            ]);
            $offer->status = Offer::STATUS_ACTIVE;
            if (!$offer->save()) {
                throw new \yii\db\Exception('Error while saving Offer model!');
            }
            if (!$offer->sendRequestCanceledEmail()) {
                throw new \yii\db\Exception('Error while sending Request Canceled email!');
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Fezez confirms canceling the request.');
            return $this->redirect(['request/myrequests']);
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Sorry, Fezez was unable to cancel your request.');
            return $this->render('myrequests');
        }
    }

    /**
     * Reject a request.
     *
     * @return string
     */
    public function actionReject()
    {
        $id = Yii::$app->request->post('id');
        $transaction = Offer::getDb()->beginTransaction();
        $offer = Offer::findOne($id);
        try {
            if ($offer->status != Offer::STATUS_REQUESTED) {
                throw new \yii\base\Exception('Error while rejecting Request: Offer not requested!');
            }
            $offer->status = Offer::STATUS_ACTIVE;
            if (!$offer->save()) {
                throw new \yii\db\Exception('Error while saving Offer model!');
            }
            // Request for the offer
            $request = Request::findOne([
                'offer_id' => $id,
                'status' => Request::STATUS_WAITING
            ]);
            $request->status = Request::STATUS_REJECTED;
            if (!$request->save()) {
                throw new \yii\db\Exception('Error while saving Request model!');
            }
            if (!$offer->sendRequestRejectedEmail()) {
                throw new \yii\db\Exception('Error while sending Request Received email!');
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Fezez confirms rejecting the request.');
            return $this->redirect(['offer/myoffers']);
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Sorry, Fezez was unable to reject the request.');
            return $this->render('myoffers');
        }
    }

    /**
     * Accept a request.
     *
     * @return string
     */
    public function actionAccept()
    {
        $id = Yii::$app->request->post('id');
        $transaction = Offer::getDb()->beginTransaction();
        $offer = Offer::findOne($id);
        try {
            if ($offer->status != Offer::STATUS_REQUESTED) {
                throw new \yii\base\Exception('Error while accepting Request: Offer not requested!');
            }
            $offer->status = Offer::STATUS_RECEIVED;
            if (!$offer->save()) {
                throw new \yii\db\Exception('Error while saving Offer model!');
            }
            // Request for the offer
            $request = Request::findOne([
                'offer_id' => $id,
                'status' => Request::STATUS_WAITING
            ]);
            $request->status = Request::STATUS_ACCEPTED;
            if (!$request->save()) {
                throw new \yii\db\Exception('Error while saving Request model!');
            }
            if (!$offer->sendRequestAcceptedEmail()) {
                throw new \yii\db\Exception('Error while sending Request Accepted email!');
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Fezez confirms accepting the request.');
            return $this->redirect(['offer/myoffers']);
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
            Yii::$app->session->setFlash('error', 'Sorry, Fezez was unable to accept the request.');
            return $this->render('myoffers');
        }
    }
}

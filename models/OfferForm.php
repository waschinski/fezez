<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * OfferForm is the model behind the offer form.
 *
 * @property Offer|null $offer This property is read-only.
 *
 */
class OfferForm extends Model
{
    public $description;
    public $key;
    /* public $autoacceptrequest;
    public $karma_threshold; */


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['key', 'trim'],
            ['key', 'required'],
            ['key', 'string', 'min' => 4, 'max' => 64],

            ['description', 'trim'],
            ['description', 'required'],
            ['description', 'string', 'max' => 255],
        ];
    }

    /**
     * Creates a new offer.
     *
     * @return bool whether the creating new offer was successful
     */
    public function createoffer()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $offer = new Offer();
        $offer->description = $this->description;
        $offer->setKeyHash($this->key);
        $offer->user_id = Yii::$app->user->identity->id;
        /* $offer->autoacceptrequest = $this->autoacceptrequest;
        $offer->karma_threshold = $this->karma_threshold; */
        return $offer->save();
    }

    /** (De)activates an offer.
     *
     * @return bool whether (de)activating an offer was successful
     */
    public function setofferstatus($id, $status)
    {
        $offer = Offer::findOne(['id' => $id, 'user_id' => Yii::$app->user->identity->id]);
        $offer->status = $status;
        return $offer->save();
    }

    /** Requests an offer.
     *
     * @return bool whether requesting an offer was successful
     */
    public function requestoffer($id)
    {
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
            if (!$this->sendRequestReceivedEmail($offer)) {
                throw new \yii\db\Exception('Error while sending Request Received email!');
            }
            $transaction->commit();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return true;
    }

    /** Cancel a request.
     *
     * @return bool whether canceling a request was successful
     */
    public function cancelrequest($id)
    {
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
            if (!$this->sendRequestCanceledEmail($offer)) {
                throw new \yii\db\Exception('Error while sending Request Canceled email!');
            }
            $transaction->commit();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return true;
    }

    /** Reject a request.
     *
     * @return bool whether rejecting a request was successful
     */
    public function rejectrequest($id)
    {
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
            if (!$this->sendRequestRejectedEmail($offer)) {
                throw new \yii\db\Exception('Error while sending Request Received email!');
            }
            $transaction->commit();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return true;
    }

    /** Accept a request.
     *
     * @return bool whether accepting a request was successful
     */
    public function acceptrequest($id)
    {
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
            if (!$this->sendRequestAcceptedEmail($offer)) {
                throw new \yii\db\Exception('Error while sending Request Accepted email!');
            }
            $transaction->commit();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return true;
    }

    /**
     * Sends an email informing the trader about the request.
     *
     * @return bool whether the email was send
     */
    public function sendRequestReceivedEmail($offer)
    {
        // User who offers
        $user = User::findOne($offer->user_id);
        // Request for the offer
        $request = Request::findOne([
            'offer_id' => $offer->id
        ]);
        // User of that request
        $requestuser = User::findOne([
            'id' => $request->user_id
        ]);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestReceived-html', 'text' => 'requestReceived-text'],
                ['user' => $user, 'requestuser' => $requestuser]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($user->email)
            ->setSubject('Request received at ' . Yii::$app->name)
            ->send();
    }

    /**
     * Sends an email informing the user about the request being rejected.
     *
     * @return bool whether the email was send
     */
    public function sendRequestRejectedEmail($offer)
    {
        // User who offers
        $user = User::findOne($offer->user_id);
        // Request for the offer
        $request = Request::findOne([
            'offer_id' => $offer->id
        ]);
        // User of that request
        $requestuser = User::findOne([
            'id' => $request->user_id
        ]);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestRejected-html', 'text' => 'requestRejected-text'],
                ['offer' => $offer, 'requestuser' => $requestuser]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($requestuser->email)
            ->setSubject('Request rejected at ' . Yii::$app->name)
            ->send();
    }

    /**
     * Sends an email informing the user about the request being accepted.
     *
     * @return bool whether the email was send
     */
    public function sendRequestAcceptedEmail($offer)
    {
        // User who offers
        $user = User::findOne($offer->user_id);
        // Request for the offer
        $request = Request::findOne([
            'offer_id' => $offer->id
        ]);
        // User of that request
        $requestuser = User::findOne([
            'id' => $request->user_id
        ]);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestAccepted-html', 'text' => 'requestAccepted-text'],
                ['offer' => $offer, 'requestuser' => $requestuser]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($requestuser->email)
            ->setSubject('Request accepted at ' . Yii::$app->name)
            ->send();
    }

        /**
     * Sends an email informing the user about the request being accepted.
     *
     * @return bool whether the email was send
     */
    public function sendRequestCanceledEmail($offer)
    {
        // User who offers
        $user = User::findOne($offer->user_id);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestCanceled-html', 'text' => 'requestCanceled-text'],
                ['offer' => $offer, 'user' => $user]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($user->email)
            ->setSubject('Request canceled at ' . Yii::$app->name)
            ->send();
    }
}

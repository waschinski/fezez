<?php
namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Offer model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $description
 * @property string $key_hash
 * @property integer $karma_threshold
 * @property bool $autoacceptrequest
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Offer extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;
    const STATUS_REQUESTED = 11;
    const STATUS_RECEIVED = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'offer';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'description' => 'Description',
            'key' => 'Key',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['description', 'trim'],
            ['description', 'required'],
            ['description', 'string', 'max' => 255],

            ['key', 'trim'],
            ['key', 'required'],
            ['key', 'string', 'min' => 4, 'max' => 64],

            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [
                    self::STATUS_RECEIVED,
                    self::STATUS_REQUESTED,
                    self::STATUS_ACTIVE,
                    self::STATUS_INACTIVE,
                    self::STATUS_DELETED
                ]
            ],
        ];
    }

    /**
     * Decrypts and returns key hash
     */
    public function getKey()
    {
        return Yii::$app->getSecurity()->decryptByKey(utf8_decode($this->key_hash), Yii::$app->params['secretKey']);
    }

    /**
     * Generates key hash from key and sets it to the model
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key_hash = utf8_encode(Yii::$app->getSecurity()->encryptByKey($key, Yii::$app->params['secretKey']));
    }

    /**
     * Returns state description
     */
    public function getstate()
    {
        switch($this->status) {
            case self::STATUS_INACTIVE:
                return 'Inactive';
            case self::STATUS_ACTIVE:
                return 'Active';
            case self::STATUS_REQUESTED:
                $request = Request::findOne(['offer_id' => $this->id]);
                $requestee = User::findOne(['id' => $request->user_id]);
                return 'Requested by ' . $requestee->username;
            case self::STATUS_DELETED:
                return 'Deleted';
            case self::STATUS_RECEIVED:
                $request = Request::findOne(['offer_id' => $this->id]);
                $receiver = User::findOne(['id' => $request->user_id]);
                return 'Received by ' . $receiver->username . ' on ' . Yii::$app->formatter->asDatetime($request->updated_at);
        }
            
        return 'Undefined';
    }

    /**
     * Requests related to the offer
     */
    public function getRequests()
    {
        return $this->hasMany(Request::className(), ['offer_id' => 'id']);
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

    /** Accept a request.
     *
     * @return bool whether accepting a request was successful
     */
    public function acceptrequest($id)
    {

        return true;
    }

    /**
     * Sends an email informing the trader about the request.
     *
     * @return bool whether the email was send
     */
    public function sendRequestReceivedEmail()
    {
        // User who offers
        $user = User::findOne($this->user_id);
        // Request for the offer
        $request = Request::findOne([
            'offer_id' => $this->id
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
    public function sendRequestRejectedEmail()
    {
        // User who offers
        $user = User::findOne($this->user_id);
        // Request for the offer
        $request = Request::findOne([
            'offer_id' => $this->id
        ]);
        // User of that request
        $requestuser = User::findOne([
            'id' => $request->user_id
        ]);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestRejected-html', 'text' => 'requestRejected-text'],
                ['offer' => $this, 'requestuser' => $requestuser]
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
    public function sendRequestAcceptedEmail()
    {
        // User who offers
        $user = User::findOne($this->user_id);
        // Request for the offer
        $request = Request::findOne([
            'offer_id' => $this->id
        ]);
        // User of that request
        $requestuser = User::findOne([
            'id' => $request->user_id
        ]);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestAccepted-html', 'text' => 'requestAccepted-text'],
                ['offer' => $this, 'requestuser' => $requestuser]
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
    public function sendRequestCanceledEmail()
    {
        // User who offers
        $user = User::findOne($this->user_id);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestCanceled-html', 'text' => 'requestCanceled-text'],
                ['offer' => $this, 'user' => $user]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($user->email)
            ->setSubject('Request canceled at ' . Yii::$app->name)
            ->send();
    }

}

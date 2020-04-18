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
                ['user' => $user, 'description' => $this->description, 'requestuser' => $requestuser]
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

    public function discordNewOffer() {
        $user = User::findOne($this->user_id);
        $signuptext = (Yii::$app->params['InvitationMandatory'] == '1' ? "ask for an invite" : "signup");
        $json_data = json_encode([
            "content" => $user->username . " has just submitted a new offer in the marketplace on Fezez:",
            "tts" => false,
            "embeds" => [
                [
                    "title" => $this->description,
                    "url" => \Yii::$app->params['homeURL'],
                    "description" => "Follow the link if you are interested in getting this key. Just login or " . $signuptext . " at Fezez.",
                ]
            ]
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        $this->sendDiscordMsg($json_data);
    }

    private function sendDiscordMsg($json_data) {
        $webhookURL = \Yii::$app->params['discordWebhookURL'];
        if ($webhookURL) {
            $ch = curl_init($webhookURL);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            // echo $response;
            curl_close($ch);
        }
    }

}

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
    const STATUS_PAYABLE = 12;
    const STATUS_RECEIVED = 20;
    const PRICEFORMAT = [
        'de-DE' => [
            'placeholder' => '0',
            'suffix' => ' €',
            'groupSeparator' => '.',
            'radixPoint' => ',',
            'rightAlign' => false,
            'digitsOptional' => false,
        ],
        'en-US' => [
            'placeholder' => '0',
            'prefix' => '$ ',
            'groupSeparator' => ',',
            'radixPoint' => '.',
            'rightAlign' => false,
            'digitsOptional' => false,
        ]
    ];
    const CURRENCYCODE = [
        'de-DE' => 'EUR',
        'en-US' => 'USD'
    ];

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
            'description' => \Yii::t('app', 'Description'),
            'key' => \Yii::t('app', 'Key'),
            'price' => \Yii::t('app', 'Price'),
            'paypalmelink' => \Yii::t('app', 'PayPal.Me Link'),
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

            ['price', 'double', 'min' => 0, 'max' => 999.99],

            ['paypalmelink', 'string', 'max' => 255],

            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [
                    self::STATUS_RECEIVED,
                    self::STATUS_PAYABLE,
                    self::STATUS_REQUESTED,
                    self::STATUS_ACTIVE,
                    self::STATUS_INACTIVE,
                    self::STATUS_DELETED
                ]
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->price == null) {
            $this->price = 0;
        }
        return true;
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
     * Returns the display price
     */
    public function getdisplayprice() {
        return $this->price > 0 ? Yii::$app->formatter->asCurrency($this->price) : Yii::t('app', 'free');
    }

    /**
     * Returns state description
     */
    public function getstate()
    {
        switch($this->status) {
            case self::STATUS_INACTIVE:
                return Yii::t('app', 'Inactive');
            case self::STATUS_ACTIVE:
                return Yii::t('app', 'Active');
            case self::STATUS_REQUESTED:
                $request = Request::findOne(['offer_id' => $this->id]);
                //$requestee = User::findOne(['id' => $request->user_id]);
                return Yii::t('app', 'Requested by {username}', [
                    'username' => $request->user->username
                ]);
            case self::STATUS_DELETED:
                return Yii::t('app', 'Deleted');
            case self::STATUS_RECEIVED:
                $request = Request::findOne(['offer_id' => $this->id]);
                //$receiver = User::findOne(['id' => $request->user_id]);
                return Yii::t('app', 'Received by {username} on {date}', [
                    'username' => $request->user->username,
                    'date' => Yii::$app->formatter->asDatetime($request->updated_at)
                ]);
            case self::STATUS_PAYABLE:
                $request = Request::findOne(['offer_id' => $this->id]);
                //$receiver = User::findOne(['id' => $request->user_id]);
                return Yii::t('app', 'Waiting on payment from {username}', [
                    'username' => $request->user->username
                ]);
        }
            
        return Yii::t('app', 'Undefined');
    }

    /**
     * Returns paypal.me link of the user who offers
     */
    public function getPaypalmelink()
    {
        $user = User::findOne($this->user_id);
        return $user->paypalme_link;
    }

    /**
     * Returns paypal.me link of the user who offers
     */
    public function setPaypalmelink($paypalmelink)
    {
        $user = User::findOne($this->user_id);
        $user->paypalme_link = $paypalmelink;
        $user->save();
    }

    /**
     * Requests related to the offer
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestReceived-html', 'text' => 'requestReceived-text'],
                ['user' => $user, 'description' => $this->description, 'requestuser' => $request->user]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($user->email)
            ->setSubject(Yii::t('mail', 'Request received at {sitename}', [
                'sitename' => Yii::$app->name
            ]))
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

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestRejected-html', 'text' => 'requestRejected-text'],
                ['offer' => $this, 'requestuser' => $request->user]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($request->user->email)
            ->setSubject(Yii::t('mail', 'Request rejected at {sitename}', [
                'sitename' => Yii::$app->name
            ]))
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

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestAccepted-html', 'text' => 'requestAccepted-text'],
                ['offer' => $this, 'requestuser' => $request->user]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($request->user->email)
            ->setSubject(Yii::t('mail', 'Request accepted at {sitename}', [
                'sitename' => Yii::$app->name
            ]))
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
            ->setSubject(Yii::t('mail', 'Request canceled at {sitename}', [
                'sitename' => Yii::$app->name
            ]))
            ->send();
    }

    /**
     * Sends an email informing a user about the request to buy their offer.
     *
     * @return bool whether the email was send
     */
    public function sendRequestToBuyReceivedEmail()
    {
        // User who offers
        $user = User::findOne($this->user_id);
        // Request for the offer
        $request = Request::findOne([
            'offer_id' => $this->id
        ]);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'requestToBuy-html', 'text' => 'requestToBuy-text'],
                ['offer' => $this, 'user' => $user, 'requestuser' => $request->user]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($user->email)
            ->setSubject(Yii::t('mail', 'Request To Buy at {sitename}', [
                'sitename' => Yii::$app->name
            ]))
            ->send();
    }

    /**
     * Sends an email informing a user about a payment being required.
     *
     * @return bool whether the email was send
     */
    public function sendPaymentRequiredEmail()
    {
        // User who offers
        $user = User::findOne($this->user_id);
        // Request for the offer
        $request = Request::findOne([
            'offer_id' => $this->id
        ]);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'paymentRequired-html', 'text' => 'paymentRequired-text'],
                ['offer' => $this, 'user' => $user, 'requestuser' => $request->user, 'currencycode' => self::CURRENCYCODE[getenv('SITE_LANG')]]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($request->user->email)
            ->setSubject(Yii::t('mail', 'Payment Required at {sitename}', [
                'sitename' => Yii::$app->name
            ]))
            ->send();
    }

    /**
     * Sends an email informing a user about confirmation of his payment.
     *
     * @return bool whether the email was send
     */
    public function sendPaymentConfirmedEmail()
    {
        // User who offers
        $user = User::findOne($this->user_id);
        // Request for the offer
        $request = Request::findOne([
            'offer_id' => $this->id
        ]);

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'paymentConfirmed-html', 'text' => 'paymentConfirmed-text'],
                ['offer' => $this, 'requestuser' => $request->user]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($request->user->email)
            ->setSubject(Yii::t('mail', 'Payment Confirmed at {sitename}', [
                'sitename' => Yii::$app->name
            ]))
            ->send();
    }

    public function discordNewOffer() {
        $user = User::findOne($this->user_id);
        $signuptext = (Yii::$app->params['InvitationMandatory'] == '1' ?
            \Yii::t('app', 'Just login or ask for an invite at Fezez.') :
            \Yii::t('app', 'Just login or signup at Fezez.')
        );
        $json_data = json_encode([
            "content" => \Yii::t('app', '{username} is now offering {description} on the marketplace on Fezez:', [
                'username' => $user->username,
                'description' => $this->description
            ]),
            "tts" => false,
            "embeds" => [
                [
                    "title" => $this->description,
                    "url" => \Yii::$app->params['homeURL'],
                    "description" => \Yii::t('app', 'Follow the link if you are interested in getting this key.') . " " . $signuptext,
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

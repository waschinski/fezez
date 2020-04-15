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
    public function getkey()
    {
        return Yii::$app->getSecurity()->decryptByKey(utf8_decode($this->key_hash), Yii::$app->params['secretKey']);
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
     * Generates key hash from key and sets it to the model
     *
     * @param string $key
     */
    public function setKeyHash($key)
    {
        $this->key_hash = utf8_encode(Yii::$app->getSecurity()->encryptByKey($key, Yii::$app->params['secretKey']));
    }

    /**
     * Requests related to the offer
     */
    public function getRequests()
    {
        return $this->hasMany(Request::className(), ['offer_id' => 'id']);
    }

}

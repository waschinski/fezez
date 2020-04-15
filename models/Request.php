<?php
namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Request model
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $offer_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Request extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_WAITING = 6;
    const STATUS_REJECTED = 9;
    const STATUS_ACCEPTED = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'request';
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
            ['status', 'default', 'value' => self::STATUS_WAITING],
            ['status', 'in', 'range' => [self::STATUS_ACCEPTED, self::STATUS_REJECTED, self::STATUS_WAITING, self::STATUS_DELETED]],
        ];
    }

    /**
     * Decrypts and returns key hash
     */
    public function getkey()
    {
        if ($this->status == self::STATUS_ACCEPTED) {
            $offer = Offer::findOne(['id' => $this->offer_id]);
            return Yii::$app->getSecurity()->decryptByKey(utf8_decode($offer->key_hash), Yii::$app->params['secretKey']);
        }
        return '';
    }

    /**
     * Returns state description
     */
    public function getstate()
    {
        switch($this->status) {
            case self::STATUS_DELETED:
                return 'Canceled';
            case self::STATUS_WAITING:
                return 'Waiting';
            case self::STATUS_REJECTED:
                return 'Rejected';
            case self::STATUS_ACCEPTED:
                return 'Accepted';
        }
            
        return 'Undefined';
    }

    /**
     * Offer related to the request
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['id' => 'offer_id']);
    }

}

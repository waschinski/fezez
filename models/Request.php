<?php
namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "request".
 *
 * @property int $id
 * @property int $user_id
 * @property int $offer_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Offer $offer
 * @property User $user
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
            [['user_id', 'offer_id', 'status'], 'required'],
            [['user_id', 'offer_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'id']],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * Returns offer key
     */
    public function getkey()
    {
        if ($this->status == self::STATUS_ACCEPTED) {
            return Offer::findOne(['id' => $this->offer_id])->getKey();
            //return $this->getOffer()->getKey();
        }
        return '';
    }

    /**
     * Returns offer description
     */
    public function getdescription()
    {
        return self::getOffer()->one()['description'];
    }

    /**
     * Sets offer description
     */
    public function setdescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns state description
     */
    public function getstate()
    {
        switch($this->status) {
            case self::STATUS_DELETED:
                return Yii::t('app', 'Canceled');
            case self::STATUS_WAITING:
                return Yii::t('app', 'Waiting');
            case self::STATUS_REJECTED:
                return Yii::t('app', 'Rejected');
            case self::STATUS_ACCEPTED:
                return Yii::t('app', 'Accepted');
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

    /**
     * User related to the request
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}

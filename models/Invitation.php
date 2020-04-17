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
class Invitation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invitation';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
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
     * Inviting user related to the Invite
     */
    public function getInviter()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Registered user related to the Invite
     */
    public function getNewuser()
    {
        return $this->hasOne(User::className(), ['id' => 'new_user_id']);
    }

    /**
     * Generates new token for email verification
     */
    public function generateInvitationCode()
    {
        $this->code = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates vaild until timestamp
     */
    public function generateValidUntil()
    {
        $this->valid_until = time() + 60*60*24;
    }

}

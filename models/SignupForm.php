<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * SignupForm is the model behind the signup form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $code;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => 'app\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => 'app\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            [['code'], 'validateCode', 'skipOnEmpty' => false, 'skipOnError' => false]
        ];
    }

    public function validateCode($attribute, $params)
    {
        if (Yii::$app->params['InvitationMandatory'] == '1' &&
                Invitation::find()
                    ->where([
                            'email' => $this->email,
                            'code' => $this->code,
                            'new_user_id' => null
                    ])
                    ->andWhere('valid_until >= UNIX_TIMESTAMP()')
                    ->count() == 0) {
            $this->addError('code', 'Code is invalid or not matching email.');
        }
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $transaction = User::getDb()->beginTransaction();
        try {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->generateEmailVerificationToken();
            if (Yii::$app->params['InvitationMandatory'] == '1') {
                $invitation = Invitation::findOne(['email' => $this->email, 'code' => $this->code]);
                $user->invited_by = $invitation->user_id;
            }
            if (!$user->save()) {
                throw new \yii\db\Exception('Error while saving User model!');
            }
            if (Yii::$app->params['InvitationMandatory'] == '1') {
                $invitation->new_user_id = $user->getId();
                if (!$invitation->save()) {
                    throw new \yii\db\Exception('Error while saving Invitation model!');
                }
            }
            if (!$this->sendEmail($user)) {
                throw new \yii\base\Exception('Error while sending Verify email!');
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Fezez confirms accepting the request.');
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Sorry, an error occured while signing up.');
            return false;
        }

    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($this->email)
            ->setSubject(Yii::t('mail', 'Account registration at {sitename}', [
                'sitename' => Yii::$app->name
            ]))
            ->send();
    }
}

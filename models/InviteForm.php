<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * InviteForm is the model behind the invite form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class InviteForm extends Invitation
{
    public $email;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => 'app\models\User', 'message' => 'This email address has already signed up.'],
            ['email', 'unique', 'targetClass' => 'app\models\Invitation', 'message' => 'This email address has already been invited.'],
        ];
    }

    /**
     * Invites an user.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function invite()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $invitation = new Invitation();
        $invitation->user_id = \Yii::$app->user->identity->id;
        $invitation->email = $this->email;
        $invitation->generateInvitationCode();
        $invitation->generateValidUntil();
        $user = User::findOne([
            'id' => $invitation->user_id
        ]);
        return $invitation->save() && $this->sendEmail($user, $invitation);

    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user, $invitation)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailInvitation-html', 'text' => 'emailInvitation-text'],
                ['user' => $user, 'invitation' => $invitation]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($this->email)
            ->setSubject('Invitation to ' . Yii::$app->name)
            ->send();
    }
}

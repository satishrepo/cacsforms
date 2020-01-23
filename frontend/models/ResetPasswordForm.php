<?php
namespace frontend\models;

use yii\base\InvalidArgumentException;
use yii\base\Model;
use common\models\User;


class ResetPasswordForm extends Model
{
    public $password;
    public $cpassword;
    public $otp;
    public $username;
    public $email;

    private $_user;

    public function rules()
    {
        return [

            [['password','cpassword'], 'required'],
            ['cpassword', 'compare', 'compareAttribute'=>'password'],
            [['cpassword', 'password'], 'string', 'length' => [6, 15]],

        ];
    }

    public function resetPassword()
    {
        if (!$this->validate()) {
            return null;
        }
     
        
        $user = User::find()->where([ 'email' => $this->email, 'otp' => $this->otp])->one();
        if(!empty($user)) {
            $user->setPassword($this->password);
            $user->removePasswordResetToken();
            $user->otp = null;
            return $user->save(false);
        } else {
            return false;
        }

    }

}
      
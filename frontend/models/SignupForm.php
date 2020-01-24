<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\mailer\smtpMailer;
/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $name;
    public $email;
    public $password;
    public $cpassword;

    public function rules()
    {
        return [
            [['username', 'name'], 'trim'],
            [['username', 'name'], 'required'],
            [['username', 'name'], 'string', 'min' => 2, 'max' => 255],
            ['username', 'uniqueUsername'],
            //['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'uniqueEmail'],
            //['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            [['password','cpassword'], 'required'],
            ['cpassword', 'compare', 'compareAttribute'=>'password'],
            [['cpassword', 'password'], 'string', 'length' => [6, 15]],
        ];
    }

    public function uniqueUsername($attribute, $params)
    {
        $user = User::find()->where(['username' => $this->username, 'status'=>STATUS_ACTIVE])->one();
        if(!empty($user)){
            $this->addError($attribute, 'The Student ID has already been taken');
        }
    }

    public function uniqueEmail($attribute, $params)
    {
        $user = User::find()->where(['email' => $this->email, 'status'=>STATUS_ACTIVE])->one();
        if(!empty($user)){
            $this->addError($attribute, 'The Email address has already been taken');
        }
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Student ID',
            'email' => 'Email',
            'password' => 'Password',
            'cpassword' => 'Confirm Password'
        ];
    }

    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $otp = rand(1111,9999);

        // ====== delete previous user if registered and not verified ====//
            $userWithUsername = User::find()->where(['username' => $this->username, 'role'=> ROLE_USER, 'status'=>STATUS_NOTVERIFIED])->one();
            $userWithEmail = User::find()->where(['email' => $this->email, 'role'=> ROLE_USER, 'status'=>STATUS_NOTVERIFIED])->one();
            if(!empty($userWithUsername)){
                $userWithUsername->delete();
            }
            if(!empty($userWithEmail)){
                $userWithEmail->delete();
            }
        //========================================//
        
        $user = new User();
        $user->name = $this->name;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->role = ROLE_USER;
        $user->status = STATUS_NOTVERIFIED;
        $user->otp = $otp;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->created_at = time();
        $user->updated_at = time();
        $user->is_admin = 0;
        
        if($user->save()){
            return $this->_sendmail($otp, $this->email);
            // return true;
        }else{
            return false;
        }
    }

    private function _sendmail($otp, $toEmail = false)
    {
        $host='mail.cacsforms.com';
        $user='admin@cacsforms.com'; 
        $password='admin@321';
        $secrity='ssl';
        $port='465';
        
        $ml = new smtpMailer();
        $ml->setDefaultValue(
            $host,
            $user,
            $password,
            $secrity,
            $port
        );
        $ml->Subject = 'New User Registration OTP';

        $ml->SetFrom('admin@cacsforms.com');

        
        $ml->MsgHTML('Your Otp is :'.$otp); //$error
        $ml->AddAddress($toEmail);
        // $ml->AddCC('jay.swd@gmail.com');
        // $ml->AddBCC('merajjmi@gmail.com');
        // $ml->AddBCC('ahadmurtaza@gmail.com');
        if($ml->Send()){
            return true;
        } else {
            return false;
        }
        // $processFlag = $ml->Send();
        // $ml->ClearAddresses();
        // $ml->ClearAttachments();
    }
}

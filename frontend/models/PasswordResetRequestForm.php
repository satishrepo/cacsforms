<?php
namespace frontend\models;

use Yii;

use yii\base\Model;
use common\models\User;
use common\mailer\smtpMailer;

class PasswordResetRequestForm extends Model

{

    public $email;


    public function rules()
    {

        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',

                'targetClass' => '\common\models\User',

                'filter' => ['status' => User::STATUS_ACTIVE],

                'message' => 'There is no user with this email address.'

            ],

        ];

    }



    /**

     * Sends an email with a link, for resetting the password.

     *

     * @return bool whether the email was send

     */

    public function sendEmail($toEmail)
    {
        if (!$this->validate()) {
            return null;
        }

        $otp = rand(1111,9999);
        $user = User::findOne([
            'status' => STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if (!$user) {
            return false;
        }else{
            $user->otp = $otp;
            if($user->save()){
                return $this->_sendmail($otp, $toEmail);
                // return true;
            }else{
                return false;
            }
        }

        

    }

    private function _sendmail($otp, $toEmail=false)
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
        $ml->Subject = 'Your Forget Password OTP';

        $ml->SetFrom('admin@cacsforms.com');

        
        $ml->MsgHTML('Your Otp is :'.$otp); //$error
        $ml->AddAddress($toEmail);
        // $ml->AddCC('jay.swd@gmail.com');
        // $ml->AddCC('merajjmi@gmail.com');
        // $ml->AddCC('ahadmurtaza@gmail.com');
        // $ml->Send();
        // $ml->ClearAddresses();
        // $ml->ClearAttachments();
        if($ml->Send()) {
            return true;
        } else {
            return false;
        }
    }

}


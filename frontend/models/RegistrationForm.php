<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
// use common\models\User;
use common\mailer\smtpMailer;
use Mpdf\Mpdf;


class RegistrationForm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'registration';
    }

    public function rules()
    {
        return [


            [['student_id', 'name'], 'safe'],
            [['student_id', 'name'], 'required'],
            [['student_id', 'name'], 'string', 'min' => 2, 'max' => 255],
            ['student_id', 'unique'],
            //['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique'],
            //['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            [['mobile','gpa'], 'required'],
            // ['cpassword', 'compare', 'compareAttribute'=>'password'],
            // [['cpassword', 'password'], 'string', 'length' => [6, 15]],
        ];
    }

    public function uniqueStudentid($attribute, $params)
    {
        $user = Yii::$app->find()->where(['student_id' => $this->student_id])->one();
        if(!empty($user)){
            $this->addError($attribute, 'The Student ID has already been taken');
        }
    }

    public function uniqueEmail($attribute, $params)
    {
        $user = User::find()->where(['email' => $this->email])->one();
        if(!empty($user)){
            $this->addError($attribute, 'The Email address has already been taken');
        }
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'student_id' => 'Student Id',
            'name' => 'Name',
            'emai' => 'Email',
            'mobile' => 'Mobile',
            'gpa' => 'GPA',
            'project_1' => 'Project',
            'project_2' => 'Project',
            'project_3' => 'Project',
            'project_4' => 'Project',
            'project_5' => 'Project',
            'project_6' => 'Project',
            'project_7' => 'Project',
            'project_8' => 'Project',
            'project_9' => 'Project',
            'project_10' => 'Project',
            'project_11' => 'Project',
            'created_at' => 'Created On',
            'created_by' => 'Created By',
            'updated_at' => 'Updated On',
            'updated_by' => 'Updated By',
            // 'status' => 'Status',
        ];
    }

    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $otp = rand(1111,9999);

        // ====== delete previous user if registered and not verified ====//
            $userWithUsername = User::find()->where(['student_id' => $this->student_id, 'role'=> ROLE_USER, 'status'=>STATUS_NOTVERIFIED])->one();
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
            // $this->_sendmail($otp);
            return true;
        }else{
            return false;
        }
    }

    public function _sendmail($userData, $emails)
    {
        $mpdf = new mPDF();

        $logo = Yii::getAlias('@webroot/cacs-logo.jpeg');
        $body = '<table align="center"><tr><th colspan="2"><img src="'.$logo.'" height="80px" ></th></tr>';
        $body .= '<tr><th colspan="2">COLLEGE OF APPLIED COMPUTER SCIENCE <br> KING SAUD UNIVERSITY</th></tr>';
        $body .= '<tr><th colspan="2">DATE : '.date("d-m-Y").'</th></tr>';
        $body .= '<tr><th colspan="2">GRADUATION FORM</th></tr>';
        $body .= '<tr><th colspan="2"> </th></tr>';
        $body .= '<tr><th colspan="2"> </th></tr>';
        $body .= '<tr><th colspan="2">'.$userData['headline'].'</th></tr>';
        $body .= '<tr><td>Student Id </td><td>'.$userData['student_id'].'</td></tr>';
        $body .= '<tr><td>Name </td><td>'.$userData['name'].'</td></tr>';
        $body .= '<tr><td>Email </td><td>'.$userData['email'].'</td></tr>';
        $body .= '<tr><td>Mobile </td><td>'.$userData['mobile'].'</td></tr>';
        $body .= '<tr><td>GPA </td><td>'.$userData['gpa'].'</td></tr>';
        if(is_array($userData['proposals'])) {
            forEach($userData['proposals'] as $k => $p) {
                $body .= '<tr><td>Proposal '.($k+1).' </td><td>'.$p.'</td></tr>';
            }
        }

        $body .= '<tr><td>Status </td><td>'.$userData['status'].'</td></tr>';
        $body .= '</table>';

        $mpdf->WriteHTML($body);
        $fileName = 'registrationfiles/Registration_'.time().'.pdf';
        $mpdf->Output($fileName, 'F');

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
        $ml->Subject = $userData['subject'];

        $ml->SetFrom('admin@cacsforms.com');
        
        $ml->MsgHTML($body); //$error
        $ml->AddAddress($emails['toEmail']);
        
        if(array_key_exists('ccEmail', $emails)){

            forEach($emails['ccEmail'] as $m) {
                $ml->AddCC($m);
            }

        }
        
        if(array_key_exists('bccEmail', $emails)){

            forEach($emails['bccEmail'] as $m) {
                $ml->AddBCC($m);
            }

        }

        $filePath = Yii::getAlias('@webroot/'.$fileName);
        $ml->addAttachment($filePath);
        $processFlag = $ml->Send();
        $ml->ClearAddresses();
        $ml->ClearAttachments();
    }
}

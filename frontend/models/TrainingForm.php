<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
// use common\models\User;
use common\mailer\smtpMailer;
use Mpdf\Mpdf;


class TrainingForm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'training';
    }

    public function rules()
    {
        return [


            [['student_id', 'name', 'mobile_no', 'email', 'passed_hours', 'remaining_hours'], 'trim'],

            [['student_id', 'name', 'mobile_no', 'email', 'passed_hours', 'remaining_hours'], 'required'],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'student_id' => 'Student ID',
            'name' => 'Name',
            'mobile_no' => 'Mobile No',
            'email' => 'Email',
            'passed_hours' => 'No of passed hours',
            'remaining_hours' => 'No of remaining hours',
            'created_on' => 'Created On',
            'created_by' => 'Created By',
            'updated_on' => 'Updated On',
            'updated_by' => 'Updated By'
        ];
    }


    public function _sendmail($userData, $emails)
    {

        $mpdf = new mPDF();

        $logo = Yii::getAlias('@webroot/cacs-logo.jpeg');
        $body = '<table align="center"><tr><th colspan="2"><img src="'.$logo.'" height="80px" ></th></tr>';
        $body .= '<tr><th colspan="2">COLLEGE OF APPLIED COMPUTER SCIENCE <br> KING SAUD UNIVERSITY</th></tr>';
        $body .= '<tr><th colspan="2">DATE : '.date("d-m-Y").'</th></tr>';
        $body .= '<tr><th colspan="2">CHEATING FORM</th></tr>';
        $body .= '<tr><th colspan="2"> </th></tr>';
        $body .= '<tr><th colspan="2"> </th></tr>';
        $body .= '<tr><th colspan="2">'.$userData['headline'].'</th></tr>';
        $body .= '<tr><td>Student Id</td><td>'.$userData['student_id'].'</td></tr>';
        $body .= '<tr><td>Name </td><td>'.$userData['name'].'</td></tr>';
        $body .= '<tr><td>Exam Type </td><td>'.$userData['mobile_no'].'</td></tr>';
        $body .= '<tr><td>Semeter Type </td><td>'.$userData['email'].'</td></tr>';
        $body .= '<tr><td>Course Name </td><td>'.$userData['passed_hours'].'</td></tr>';
        $body .= '<tr><td>Course Name </td><td>'.$userData['remaining_hours'].'</td></tr>';
        $body .= '</table>';

        $mpdf->WriteHTML($body);
        $fileName = 'trainingfiles/Training_'.time().'.pdf';
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

      
        // $ml->AddCC('satish.purohit.3@gmail.com');
        $filePath = Yii::getAlias('@webroot/'.$fileName);
        $ml->addAttachment($filePath);
        $processFlag = $ml->Send();
        $ml->ClearAddresses();
        $ml->ClearAttachments();

    }
}

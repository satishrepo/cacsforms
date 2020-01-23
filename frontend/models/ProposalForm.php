<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
// use common\models\User;
use common\mailer\smtpMailer;
use Mpdf\Mpdf;


class ProposalForm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'proposal';
    }

    public function rules()
    {
        return [


            [['instructor', 'title', 'description', 'goals', 'clients'], 'trim'],
            [['instructor', 'title', 'description', 'goals', 'clients', 'created_by'], 'required'],
            [['instructor', 'title', 'description', 'goals', 'clients'], 'string'],
            ['title', 'unique'],
            //['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'instructor' => 'Instructor',
            'title' => 'Title',
            'description' => 'description',
            'goals' => 'Goals',
            'clients' => 'Clients',
            'functions' => 'Functions',
            'skills' => 'Skills',
            'created_at' => 'Created On',
            'created_by' => 'user_id',
            'updated_at' => 'Updated On',
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
        $body .= '<tr><th colspan="2">GRADUATION FORM</th></tr>';
        $body .= '<tr><th colspan="2"> </th></tr>';
        $body .= '<tr><th colspan="2"> </th></tr>';
        $body .= '<tr><th colspan="2">'.$userData['headline'].'</th></tr>';
        $body .= '<tr><td>Instructor</td><td>'.$userData['instructor'].'</td></tr>';
        $body .= '<tr><td>Title </td><td>'.$userData['title'].'</td></tr>';
        $body .= '<tr><td>Description </td><td>'.$userData['description'].'</td></tr>';
        $body .= '<tr><td>Goals </td><td>'.$userData['goals'].'</td></tr>';
        $body .= '<tr><td>Clients </td><td>'.$userData['clients'].'</td></tr>';
        $body .= '<tr><td>Functions </td><td>'.(isSet($userData['functions']) ? $userData['functions'] : '-').'</td></tr>';
        $body .= '<tr><td>Skills </td><td>'.(isSet($userData['skills']) ? $userData['skills'] : '-').'</td></tr>';
        $body .= '<tr><td>Status </td><td>'.$userData['status'].'</td></tr>';
        $body .= '</table>';

        $mpdf->WriteHTML($body);
        $fileName = 'proposalfiles/Propsal_'.time().'.pdf';
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

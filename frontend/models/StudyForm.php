<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
// use common\models\User;
use common\mailer\smtpMailer;
/**
 * Signup form
 */
class StudyForm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'study';
    }

    public function rules()
    {
        return [


            [
                ['student_id', 'name', 'email', 'mobile', 'collage', 
                'first_sem', 'first_sem_year', 'last_sem', 'last_sem_year', 'gpa'], 'trim'],

            [['student_id', 'name', 'email', 'mobile', 'collage', 
                'first_sem', 'first_sem_year', 'last_sem', 'last_sem_year', 'gpa'], 'required'],
            [['student_id', 'name', 'email', 'collage'], 'string', 'min' => 2, 'max' => 255],
            [['mobile'], 'string', 'min' => 10, 'max' => 15],
            // ['student_id', 'uniqueStudentid'],
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
            'student_id' => 'Student ID',
            'name' => 'Name',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'collage' => 'Collage',
            'specialization' => 'Specialization',
            'first_sem' => 'First Semester',
            'first_sem_year' => 'First Semester Year',
            'last_sem' => 'Last Semester',
            'last_sem_year' => 'Last Semester Year',
            'gpa_low_reason' => 'Reason for low GPA',
            'gpa_rem_hrs' => 'Remaining Credit Hours',
            'gpa_warning' => 'No. of times GPA warning',
            'gpa' => 'GPA',
            'last_sem_gpa' => 'Last semester GPA',
            'two_sem_gpa' => 'Last two semester GPA',
            'created_on' => 'Created On',
            'created_by' => 'Created By',
            'updated_on' => 'Updated On',
            'updated_by' => 'Updated By'
        ];
    }


    public function _sendmail($userData)
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
        $ml->Subject = 'Proposal Submitted';

        $ml->SetFrom('admin@cacsforms.com');

        $body = '<table><tr><th colspan="2">New proposal submitted</th></tr>';
        $body .= '<tr><td>Instructor</td><td>'.$userData['instructor'].'</td></tr>';
        $body .= '<tr><td>Title </td><td>'.$userData['title'].'</td></tr>';
        $body .= '<tr><td>Description </td><td>'.$userData['description'].'</td></tr>';
        $body .= '<tr><td>Goals </td><td>'.$userData['goals'].'</td></tr>';
        $body .= '<tr><td>Clients </td><td>'.$userData['clients'].'</td></tr>';
        $body .= '<tr><td>Functions </td><td>'.(isSet($userData['functions']) ? $userData['functions'] : '-').'</td></tr>';
        $body .= '<tr><td>Skills </td><td>'.(isSet($userData['skills']) ? $userData['skills'] : '-').'</td></tr>';
        $body .= '</table>';
        
        $ml->MsgHTML($body); //$error
        $ml->AddAddress('satish.purohit.3@gmail.com');
        $ml->AddCC('jay.swd@gmail.com');
        // $ml->AddCC('merajjmi@gmail.com');
        // $ml->AddCC('ahadmurtaza@gmail.com');
        $processFlag = $ml->Send();
        $ml->ClearAddresses();
        $ml->ClearAttachments();
    }
}

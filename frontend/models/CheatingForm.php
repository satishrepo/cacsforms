<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
// use common\models\User;
use common\mailer\smtpMailer;
use Mpdf\Mpdf;


class CheatingForm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'cheating';
    }

    public function rules()
    {
        return [


            [['student_id', 'name', 'exam_type', 'semester_type', 'course_name', 'course_code', 'academic_year', 'date', 'section', 'exam_room_no', 'case_desc', 'supervisor_name'], 'trim'],

            [['student_id', 'name', 'exam_type', 'semester_type', 'course_name', 'course_code', 'academic_year', 'date', 'section', 'exam_room_no', 'case_desc', 'supervisor_name'], 'required'],
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
            'exam_type' => 'Examp Type',
            'semester_type' => 'Semester Type',
            'date' => 'Date',
            'course_code' => 'Course code',
            'course_name' => 'Course name',
            'section' => 'Section',
            'exam_room_no' => 'Exam Room No.',
            'case_desc' => 'Case Description',
            'supervisor_name' => 'Supervison Name',
            'invigilator_1' => 'Invigilator 1',
            'invigilator_2' => 'Invigilator 2',
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
        $body .= '<tr><td>Exam Type </td><td>'.$userData['exam_type'].'</td></tr>';
        $body .= '<tr><td>Semeter Type </td><td>'.$userData['semester_type'].'</td></tr>';
        $body .= '<tr><td>Course Name </td><td>'.$userData['course_name'].'</td></tr>';
        $body .= '<tr><td>Course Code</td><td>'.(isSet($userData['course_code']) ? $userData['course_code'] : '-').'</td></tr>';
        $body .= '<tr><td>Academic Year</td><td>'.(isSet($userData['academic_year']) ? $userData['academic_year'] : '-').'</td></tr>';
        $body .= '<tr><td>Section</td><td>'.(isSet($userData['section']) ? $userData['section'] : '-').'</td></tr>';
        $body .= '<tr><td>Room No.</td><td>'.(isSet($userData['exam_room_no']) ? $userData['exam_room_no'] : '-').'</td></tr>';
        $body .= '<tr><td>Description</td><td>'.(isSet($userData['case_desc']) ? $userData['case_desc'] : '-').'</td></tr>';
        $body .= '<tr><td>Supervisor Name</td><td>'.(isSet($userData['supervisor_name']) ? $userData['supervisor_name'] : '-').'</td></tr>';
        $body .= '<tr><td>Invigilator 1</td><td>'.(isSet($userData['invigilator_1']) ? $userData['invigilator_1'] : '-').'</td></tr>';
        $body .= '<tr><td>Invigilator 2</td><td>'.(isSet($userData['invigilator_2']) ? $userData['invigilator_2'] : '-').'</td></tr>';
        $body .= '</table>';

        $mpdf->WriteHTML($body);
        $fileName = 'chatingfiles/Cheating_'.time().'.pdf';
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

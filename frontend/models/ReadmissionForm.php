<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
// use common\models\User;
use common\mailer\smtpMailer;
/**
 * Signup form
 */
class ReadmissionForm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'readmission';
    }

    public function rules()
    {
        return [


            [
                ['reason', 'student_id', 'name', 'email', 'mobile', 'collage', 
                'first_sem', 'first_sem_year', 'last_sem', 'last_sem_year', 'gpa'], 'trim'],

            [['reason', 'student_id', 'name', 'email', 'mobile', 'collage', 
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
            'reason' => 'Reason for Re-Admission',
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
            'quit_reason' => 'Reason for leaving university',
            'gpa_rem_hrs' => 'Remaining Credit Hours',
            'number_warning' => 'No. of previous warning',
            'gpa' => 'GPA',
            'last_sem_gpa' => 'Last semester GPA',
            'two_sem_gpa' => 'Last two semester GPA',
            'created_on' => 'Created On',
            'created_by' => 'Created By',
            'updated_on' => 'Updated On',
            'updated_by' => 'Updated By'
        ];
    }


}

<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
// use common\models\User;
use common\mailer\smtpMailer;
/**
 * Signup form
 */
class SemesterdropForm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'semester_drop';
    }

    public function rules()
    {
        return [


            [['student_id', 'name', 'gpa', 'collage', 'academic_year', 'term_dropped', 'drop_reason', 
            'mobile', 'specialization',], 'trim'],

            [['student_id', 'name', 'gpa', 'collage', 'academic_year', 'term_dropped', 'drop_reason', 
            'mobile', 'specialization'], 'required'],

            // [['student_id', 'name', 'email', 'collage'], 'string', 'min' => 2, 'max' => 255],
            // [['mobile'], 'string', 'min' => 10, 'max' => 15],
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
            'mobile' => 'Mobile',
            'collage' => 'Collage',
            'gpa' => 'GPA',
            'academic_year' => 'Academic Year',
            'specialization' => 'Specialization',
            'term_dropped' => 'No of term dropped before',
            'drop_reason' => 'Reason to drop',
            'created_on' => 'Created On',
            'created_by' => 'Created By',
            'updated_on' => 'Updated On',
            'updated_by' => 'Updated By'
        ];
    }

}

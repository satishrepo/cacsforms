<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
// use common\models\User;
use common\mailer\smtpMailer;
use Mpdf\Mpdf;


class MasterForm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'master';
    }

    public function rules()
    {
        return [


            [['name', 'type'], 'trim'],
            [['name', 'type'], 'required'],
            [['name', 'type'], 'string'],
            ['name', 'unique'],
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
            'type' => 'Type',
            'name' => 'Name',
            'created_by' => 'user_id'
        ];
    }


}

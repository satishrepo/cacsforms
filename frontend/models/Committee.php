<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "committee".
 *
 * @property int $id
 * @property int $user_id
 * @property int $name
 * @property int $description
 * @property int $status
 * @property string $created_on
 * @property int $created_by
 * @property string $updated_on
 * @property int $updated_by
 */
class Committee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'committee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'status', 'created_on', 'created_by', 'updated_on', 'updated_by'], 'required'],
            [['user_id', 'status', 'created_by', 'updated_by'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User',
            'name' => 'Name',
            'description' => 'Description',
            'status' => 'Status',
            'created_on' => 'Created On',
            'created_by' => 'Created By',
            'updated_on' => 'Updated On',
            'updated_by' => 'Updated By',
        ];
    }

    public static function getTitle($id){
        $category = Committee::findOne(['id'=>$id]);
        if(!empty($category)){
            return $category->name;
        }else{
            return '';
        }
    }
}

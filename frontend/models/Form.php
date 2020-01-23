<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "form".
 *
 * @property int $id
 * @property int $committe_id
 * @property string $name
 * @property string $description
 * @property string $created_on
 * @property int $created_by
 * @property string $updated_on
 * @property int $updated_by
 * @property int $status
 */
class Form extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['committe_id', 'name',  'created_on', 'created_by', 'updated_on', 'updated_by', 'status'], 'required'],
            [['committe_id', 'created_by', 'updated_by', 'status'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['name', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'committe_id' => 'Committe',
            'name' => 'Name',
            'description' => 'Description',
            'created_on' => 'Created On',
            'created_by' => 'Created By',
            'updated_on' => 'Updated On',
            'updated_by' => 'Updated By',
            'status' => 'Status',
        ];
    }
}

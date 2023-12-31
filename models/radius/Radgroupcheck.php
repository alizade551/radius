<?php

namespace app\models\radius;


use Yii;

/**
 * This is the model class for table "radgroupcheck".
 *
 * @property int $id
 * @property string $groupname
 * @property string $attribute
 * @property string $op
 * @property string $value
 */
class Radgroupcheck extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radgroupcheck';
    }



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['groupname', 'attribute'], 'string', 'max' => 64],
            [['op'], 'string', 'max' => 2],
            [['value'], 'string', 'max' => 253],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'groupname' => Yii::t('app', 'Groupname'),
            'attribute' => Yii::t('app', 'Attribute'),
            'op' => Yii::t('app', 'Op'),
            'value' => Yii::t('app', 'Value'),
        ];
    }
}

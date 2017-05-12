<?php

namespace filsh\yii2\oauth2server\models;

use Yii;

/**
 * This is the model class for table "oauth_scopes".
 *
 * @property string $scope
 * @property integer $is_default
 */
class OauthScopesImsUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_scopes_ims_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scope_name', 'user_id', 'ims_id'], 'required'],
            [['user_id', 'ims_id'], 'integer'],
            [['scope_name'], 'string', 'max' => 2000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'scope_name' => 'Scope name',
            'ims_id' => 'Ims id',
            'user_id' => 'User id',
        ];
    }
}
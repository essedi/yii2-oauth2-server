<?php

namespace filsh\yii2\oauth2server\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use api\models\User;
class DefaultController extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
        ]);
    }
    
    public function actionToken()
    {
        //The first time a user logs in, he doesn't know his client_id, client_secret or ims_id
        //so we are going to default all those params to the user's account.
        if($_POST['grant_type'] === 'password' && (!isset($_POST['client_id']) || !$_POST['client_id']) && User::findByEmail($_POST['username']))
        {
            $usr = User::findByEmail($_POST['username']);
            //Get ims id
            $ims = \api\common\models\Ims::findByUserid($usr['id']);
            $oauthClient = \filsh\yii2\oauth2server\models\OauthClients::findOne(['user_id'=>$usr['id']]);
            if($ims && $oauthClient)
            {
                $_POST['client_id'] = $oauthClient->client_id;
                $_POST['client_secret'] = $oauthClient->client_secret;
                $_POST['ims_id'] = $ims['id'];
            }
        }
        
        
        $server = $this->module->getServer();
        $request = $this->module->getRequest();
        $response = $server->handleTokenRequest($request);
        
        return $response->getParameters();
    }
}
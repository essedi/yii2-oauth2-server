<?php

namespace filsh\yii2\oauth2server\controllers;

use api\components\ResponseFormatter;
use filsh\yii2\oauth2server\models\OauthAccessTokens;
use filsh\yii2\oauth2server\models\OauthClients;
use OAuth2\Response;
use Yii;
use yii\helpers\ArrayHelper;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use api\common\helpers\CommonHelper;
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

    /**
     * This will check if the grant and their token is still valid
     */
    public static function checkIfGrantStillValid()
    {
        //This is only aplicable for client_credentials grant type
        if(Yii::$app->request->post('grant_type') != 'client_credentials')
            return false;

        //Get the current auth Oauth client
        $oauthClient = OauthClients::find()->where(['client_id' => Yii::$app->request->post('client_id')])
                    ->andWhere(['client_secret' => Yii::$app->request->post('client_secret')])->one();
        //check if exists
        if(!$oauthClient)
            return false;

        //Get the current token and check if it's valid
        $oauthToken = OauthAccessTokens::find()->where(['client_id' => $oauthClient->client_id])
            ->andWhere(['scope' => 'shops'])->one();

        //check if the oauth token exists
        if(!$oauthToken)
            return false;

        //Check if is expired
        if(time() >= strtotime($oauthToken->expires))
            return false;

        //Return true if the token is still valid
        return $oauthToken;
    }
    
    public function actionToken()
    {
        //Check if the token is already setted
        if(($response = Self::checkIfGrantStillValid()))
        {
            return ResponseFormatter::Ok([
                'access_token' => $response->access_token,
                'expires_in' => (strtotime($response->expires) - time()),
                'token_type' => 'Bearer',
                'scope' => $response->scope
            ]);
        }
        else
        {
            //Expired or not exists, regenerate

            CommonHelper::beforeLogin();
            $server = $this->module->getServer();
            $request = $this->module->getRequest();
            $response = $server->handleTokenRequest($request);
            CommonHelper::afterLogin($response);
            return $response->getParameters();
        }
    }
}

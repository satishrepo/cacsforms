<?php
 
namespace common\models;
use Yii;



class Generic {
 
    public static function getHeader($response){
        if ($response['data'] !== null) {
            switch($response['statusCode']){
                case(200):
                    $response['data'] = [
                        'success' => $response['isSuccessful'],
                        'headerCode' => $response['statusCode'],
                        'data' => $response['data'], 
                    ];
                    break;
                case(417): // required data is missing
                    $formatedError = [];
                    $errors = $response['data']['errors'];
                    foreach($errors as $key => $value){
                        $formatedError[$key] = $value[0];
                    }
                    $response['data'] = [
                        'success' => $response['isSuccessful'],
                        'headerCode' => $response['statusCode'],
                        'data' => new \stdClass(),
                        'errors' => $formatedError
                    ];
                    break;
                case(404): // not found
                    $response['data'] = [
                        'success' => $response['isSuccessful'],
                        'headerCode' => $response['statusCode'],
                        'data' => [],
                    ];
                    break;
                case(405): // method not allowed
                    $response['data'] = [
                        'success' => $response['isSuccessful'],
                        'headerCode' => $response['statusCode'],
                        'data' => [],
                    ];
                    break;
                case(401): // not authorized
                    $response['data'] = [
                        'success' => $response['isSuccessful'],
                        'headerCode' => $response['statusCode'],
                        'data' => [],
                    ];
                    break;
                case(403):
                    $response['data'] = [
                        'success' => $response['isSuccessful'],
                        'headerCode' => $response['statusCode'],
                        'data' => [],
                    ];
                    break;
                default:
                    $response['data'] = [
                        'success' => $response['isSuccessful'],
                        'headerCode' => $response['statusCode'],
                        'data' => $response['data'],
                    ];
                    break;
            }
        }
        return $response['data'];
    }
    
    public static function errorHdrMsg($errors){
        $errorMessage = 'Please correct the error(s)';
        $errorDetail = '';
        if(is_array($errors)){
            if(isset($errors['errorMessage']) && !is_array($errors['errorMessage'])){
                $errorMessage = $errors['errorMessage'];
                unset($errors['errorMessage']);
            }
            $errorDetail = $errors;
        }else{
            $errorDetail = $errors;
        }
        if(!$errorDetail){
            $errorDetail = $errorMessage;
        }
        
        $data = [
            'errorMessage' => $errorMessage,
            'errorDetail' => $errorDetail
        ];
        \Yii::$app->response->headers->set('sd_data',[$data]);
        throw new \yii\web\HttpException(417);
    }
    
    
}



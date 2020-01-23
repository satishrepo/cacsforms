<?php
namespace frontend\controllers;

use Yii;
use frontend\models\UserMaster;
use frontend\models\search\UserMaster as UserMasterSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\Generic;
use common\models\LoginForm;
use frontend\models\SignupForm;
use frontend\models\ResetPasswordForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\RegistrationForm;
use common\mailer\smtpMailer;
use frontend\models\ProposalForm;
use frontend\models\StudyForm;
use frontend\models\ResultForm;
use frontend\models\CoursedropForm;
use frontend\models\ReadmissionForm;
use frontend\models\SemesterdropForm;

use Mpdf\Mpdf;


class ApiController extends Controller

{

    public $enableCsrfValidation = false;

    public function behaviors()
    {

        return [

            'verbs' => [

                'class' => VerbFilter::className(),

                'actions' => [

                    'delete' => ['POST'],

                ],

            ],

        ];

    }


    public function actionUser()
    {

        if(Yii::$app->request->post()){

            $postData = Yii::$app->request->post();

            $searchModel = new UserMasterSearch();

            $dataProvider = $searchModel->search([]);

            $data =  $dataProvider->getModels();

            $users = [];

            foreach($data as $dt){

                $attributes = $dt->attributes;



                unset($attributes['role'],$attributes['auth_key'],

                        $attributes['password_hash'],$attributes['password_reset_token'],

                        $attributes['is_admin'], $attributes['verification_token'],

                        $attributes['created_at'], $attributes['updated_at'],

                        $attributes['created_by'], $attributes['updated_by']

                    );

                $users[] = $attributes;

            }

            return json_encode(Generic::getHeader([

                'isSuccessful'=>true,

                'statusCode' => 200,

                'data'=> [

                    'users' => $users

                ]

            ]));



        }else{

            $searchModel = new UserMasterSearch();

            $dataProvider = $searchModel->search([]);



            $data =  $dataProvider->getModels();

            $users = [];

            foreach($data as $dt){

                $attributes = $dt->attributes;



                unset($attributes['role'],$attributes['auth_key'],

                        $attributes['password_hash'],$attributes['password_reset_token'],

                        $attributes['is_admin'], $attributes['verification_token'],

                        $attributes['created_at'], $attributes['updated_at'],

                        $attributes['created_by'], $attributes['updated_by']

                    );

                $users[] = $attributes;

            }

            return json_encode(Generic::getHeader([

                'isSuccessful'=>true,

                'statusCode' => 200,

                'data'=> [

                    'users' => $users

                ]

            ]));

        }

    }



    public function actionLogin()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new LoginForm();



            $model->username = isset($data['username']) ? $data['username'] : '';

            $model->password = isset($data['password']) ? $data['password'] : '';



            if($model->login()){

                $user = User::findByUsername($model->username)->attributes;

                unset($user['auth_key'],

                        $user['password_hash'], $user['password_reset_token'],

                        $user['is_admin'], $user['verification_token'],

                        $user['created_at'], $user['updated_at'],

                        $user['created_by'], $user['updated_by']

                    );

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 200,

                    'data'=> [

                        'users' => $user

                    ]

                ]));

            }else{

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 417,

                    'data'=> [

                        'errors' => $model->errors

                    ]
                ]));
            }

        }

    }



    public function actionSignup()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new SignupForm();


            $model->username = isset($data['username']) ? $data['username'] : '';

            $model->name = isset($data['name']) ? $data['name'] : '';

            $model->email = isset($data['email']) ? $data['email'] : '';

            $model->password = isset($data['password']) ? $data['password'] : '';

            $model->cpassword = isset($data['cpassword']) ? $data['cpassword'] : '';


            if($model->signup()){

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 200,

                    'data'=> [

                        'details'=>['username' => 'Details verified successfully', 'username'=>$model->username]

                    ]

                ]));

            }else{

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 417,

                    'data'=> [

                        'errors' => $model->errors

                    ]

                ]));

            }

        }

    }

    
    public function actionVerify()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $username = isset($data['username']) ? $data['username'] : '';
            $otp = isset($data['otp']) ? $data['otp'] : '';

            $user = User::find()->where([ 'username' => $username, 'otp' => $otp])->one();
            if(empty($user)){
                $user = User::find()->where([ 'email' => $username, 'otp' => $otp])->one();
            }

            if(!empty($user)){

                $user->otp = null;
                $user->status = STATUS_ACTIVE;

                if($user->save()){
                    return json_encode(Generic::getHeader([
                        'isSuccessful'=>true,
                        'statusCode' => 200,
                        'data'=> [
                            'details'=>['username' => 'Verification Code is verified successfully']
                        ]
                    ]));
                }
            }

            return json_encode(Generic::getHeader([
                'isSuccessful'=>true,
                'statusCode' => 417,
                'data'=> [
                    'errors' => ['username' => ['Verification Code is not verified']]
                ]
            ]));
        }
    }

    public function actionForgot()
    {
        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();
            $model = new PasswordResetRequestForm();

            $model->email = isset($data['email']) ? $data['email'] : '';
            if($model->sendEmail($model->email)){

                return json_encode(Generic::getHeader([
                    'isSuccessful'=>true,
                    'statusCode' => 200,
                    'data'=> [
                        'details'=>['username' => 'Mail sent successfully', 'username'=>$model->email]
                    ]
                ]));

            }else{
                return json_encode(Generic::getHeader([
                    'isSuccessful'=>true,
                    'statusCode' => 417,
                    'data'=> [
                        'errors' => $model->errors
                    ]
                ]));
            }
        }
    }


    public function actionResetpassword()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();
            $model = new ResetPasswordForm();
            $data = Yii::$app->request->post();

            $model->email = isset($data['username']) ? $data['username'] : '';
            $model->otp = isset($data['otp']) ? $data['otp'] : '';
            $model->password = isset($data['password']) ? $data['password'] : '';
            $model->cpassword = isset($data['cpassword']) ? $data['cpassword'] : '';

            if($model->resetPassword()){
                return json_encode(Generic::getHeader([
                    'isSuccessful'=>true,
                    'statusCode' => 200,
                    'data'=> [
                        'details'=>['username' => 'Password reset successfully']
                    ]
                ]));
            }

            return json_encode(Generic::getHeader([
                'isSuccessful'=>true,
                'statusCode' => 417,
                'data'=> [
                    'errors' => ['username' => ['Reset password verification Code is not verified']]
                ]
            ]));
        }
    }
    
    public function actionRegistration()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new RegistrationForm();

            $model->student_id = isset($data['student_id']) ? $data['student_id'] : '';

            $model->name = isset($data['name']) ? $data['name'] : '';

            $model->email = isset($data['email']) ? $data['email'] : '';

            $model->mobile = isset($data['mobile']) ? $data['mobile'] : '';

            $model->gpa = isset($data['gpa']) ? $data['gpa'] : '';

            $model->project_1 = isset($data['project_1']) ? $data['project_1'] : null;
            $model->project_2 = isset($data['project_2']) ? $data['project_2'] : null;
            $model->project_3 = isset($data['project_3']) ? $data['project_3'] : null;
            $model->project_4 = isset($data['project_4']) ? $data['project_4'] : null;
            $model->project_5 = isset($data['project_5']) ? $data['project_5'] : null;
            $model->project_6 = isset($data['project_6']) ? $data['project_6'] : null;
            $model->project_7 = isset($data['project_7']) ? $data['project_7'] : null;
            $model->project_8 = isset($data['project_8']) ? $data['project_8'] : null;
            $model->project_9 = isset($data['project_9']) ? $data['project_9'] : null;
            $model->project_10 = isset($data['project_10']) ? $data['project_10'] : null;
            $model->project_11 = isset($data['project_11']) ? $data['project_11'] : null;
            
            $model->created_by = isset($data['user_id']) ? $data['user_id'] : null;
            $model->status = 0;

            $proposalList = json_decode($this->actionProposal());

            // print_r($proposalList->data);exit;
            $projectData = [];
            foreach ($proposalList->data->projects as $key => $value) {
                
                if(
                    (isset($data['project_1']) && $value->id == $model->project_1) 
                    || (isset($data['project_2']) && $value->id == $model->project_2)
                    || (isset($data['project_3']) && $value->id == $model->project_3)
                    || (isset($data['project_4']) && $value->id == $model->project_4)
                    || (isset($data['project_5']) && $value->id == $model->project_5)
                    || (isset($data['project_6']) && $value->id == $model->project_6)
                    || (isset($data['project_7']) && $value->id == $model->project_7)
                    || (isset($data['project_8']) && $value->id == $model->project_8)
                    || (isset($data['project_9']) && $value->id == $model->project_9)
                    || (isset($data['project_10']) && $value->id == $model->project_10)
                    || (isset($data['project_11']) && $value->id == $model->project_11)
                    
                ) 
                {                    
                    $projectData[] = $value->title .' ('.$value->instructor.')';
                }

            }
            // print_r($projectData);exit;

            if($model->save()){

                // send mail to commette head

                $data['subject'] = 'Registration Successful';
                $data['headline'] = 'Student has submitted registration form.';
                $data['status'] = 'Pending';
                $data['proposals'] = $projectData;

                $emails = [];

                // $emails['toEmail'] = 'salvi@ksu.edu.sa';
                // $emails['ccEmail'] = 'mmeraj@ksu.edu.sa';

                $emails['toEmail'] = $model->email;
                // $emails['ccEmail'] = 'mmeraj@ksu.edu.sa';
                $emails['bccEmail'] = ['salvi@ksu.edu.sa', 'mmeraj@ksu.edu.sa', 
                    'satish.purohit.3@gmail.com'];


                $model->_sendmail($data, $emails);

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 200,

                    'data'=> [

                        'details'=>['username' => 'Details verified successfully', 'name'=>$model->name]

                    ]

                ]));

            }else{

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 417,

                    'data'=> [

                        'errors' => $model->errors

                    ]

                ]));

            }

        }

    }

    public function actionProposal() {
        
        $proposals = ProposalForm::find()->all();

        $records = [];

        foreach ($proposals as $value) {

            $row = $value->attributes;
            
            unset(
                $row['description'], 
                $row['goals'], 
                $row['clients'], 
                $row['functions'], 
                $row['skills'], 
                $row['created_on'], 
                $row['created_by'], 
                $row['updated_on'], 
                $row['updated_by']
            );   

            $records[] = $row;
        }

        return json_encode(Generic::getHeader([

                'isSuccessful'=>true,

                'statusCode' => 200,

                'data'=> [

                    'projects' => $records

                ]

            ]));
    }
    
    public function actionSaveproposal()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new ProposalForm();

            $model->instructor = isset($data['instructor']) ? $data['instructor'] : '';

            $model->title = isset($data['title']) ? $data['title'] : '';

            $model->description = isset($data['description']) ? $data['description'] : '';

            $model->goals = isset($data['goals']) ? $data['goals'] : '';

            $model->clients = isset($data['clients']) ? $data['clients'] : '';

            $model->functions = isset($data['functions']) ? $data['functions'] : '';

            $model->skills = isset($data['skills']) ? $data['skills'] : '';

            $model->created_by = isset($data['user_id']) ? $data['user_id'] : null;

            if($model->save()){

                $data['subject'] = 'Proposal submitted';
                $data['headline'] = 'New proposal submitted';
                $data['status'] = 'Pending';

                $emails = [];

                $emails['toEmail'] = 'mfaisal@ksu.edu.sa';
                // $emails['ccEmail'] = ['mmeraj@ksu.edu.sa'];
                $emails['bccEmail'] = ['salvi@ksu.edu.sa', 'mmeraj@ksu.edu.sa', 
                    'satish.purohit.3@gmail.com'];

                $model->_sendmail($data, $emails);

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 200,

                    'data'=> [

                        'details'=>['message' => 'Details saved successfully', 'title'=>$model->title]

                    ]

                ]));

            }else{

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 417,

                    'data'=> [

                        'errors' => $model->errors

                    ]

                ]));

            }

        }

    }

    public function actionProposallist() {

        $params = Yii::$app->request->post();

        /*
        if(!array_key_exists('user_id', $params)) {

            return json_encode(Generic::getHeader([

                        'isSuccessful'=>true,

                        'statusCode' => 417,

                        'data'=> [

                            'errors' => [ 'message' => [['please provide user_id']]] 

                        ]

                    ]));
        }
        */

        if(isset($params['user_id'])) {
            $proposals = ProposalForm::find()->where(['created_by' => $params['user_id']])->all();
        } else {
            $proposals = ProposalForm::find()->all();
        }

        $records = [];

        foreach ($proposals as $value) {

            $row = $value->attributes;
            
            unset(
                $row['created_on'], 
                $row['created_by'], 
                $row['updated_on'], 
                $row['updated_by']
            );   

            $records[] = $row;
        }

        return json_encode(Generic::getHeader([

                'isSuccessful'=>true,

                'statusCode' => 200,

                'data'=> [

                    'projects' => $records

                ]

            ]));
    }

    public function actionRegistrationlist() {

        $params = Yii::$app->request->post();

        /*
        if(!array_key_exists('user_id', $params)) {

            return json_encode(Generic::getHeader([

                        'isSuccessful'=>true,

                        'statusCode' => 417,

                        'data'=> [

                            'errors' => [ 'message' => [['please provide user_id']]] 

                        ]

                    ]));
        }
        */

        if(isset($params['user_id'])) {
            
            $proposals = RegistrationForm::find()->where(['created_by' => $params['user_id']])->all();

        } else {

            $proposals = RegistrationForm::find()->all();

        }

        $records = [];

        foreach ($proposals as $value) {

            $row = $value->attributes;
            
            unset(
                $row['created_on'], 
                $row['created_by'], 
                $row['updated_on'], 
                $row['updated_by']
            );   

            $records[] = $row;
        }

        return json_encode(Generic::getHeader([

                'isSuccessful'=>true,

                'statusCode' => 200,

                'data'=> [

                    'projects' => $records

                ]

            ]));
    }

    public function actionStatusupdate()
    {

        $params = Yii::$app->request->post();

        $errorMessage = '';

        if(
            array_key_exists('action', $params) 
        
            && ($params['action'] !== 'registration' && $params['action'] !== 'proposal')

        ) {

            $errorMessage = 'Invalid action value';

        } else {

            $params['status'] = $params['status'] ? $params['status'] : '0';

            if(array_key_exists('id', $params) && array_key_exists('action', $params)) {
                
              
                if($params['action'] === 'registration') {

                    $model = RegistrationForm::find()->where(['id' => $params['id']])->one();

                } else if($params['action'] === 'proposal') {

                    $model = ProposalForm::find()->where(['id' => $params['id']])->one();
                
                }

                if(!empty($model)){

                    $rs = $model->updateAttributes(['status' => (int)$params['status']]);

                    // send email to community head / faculty

                    $userData = $model->attributes;

                    $emails = [];
                    $statusArray[0] = 'Pending';
                    $statusArray[1] = 'Accepted';
                    $statusArray[2] = 'Rejected';
                    $statusArray[3] = 'Contact Us';

                    $emails['toEmail'] = 'ssalvi@ksu.edu.sa';
                    // $emails['toEmail'] = $userData['email'];
                    $emails['bccEmail'] = ['ssalvi@ksu.edu.sa'];

                    if($params['action'] === 'registration') {

                        $emails['toEmail'] = $userData['email'];
                        $userData['subject'] = 'Registration Status Update';
                        $userData['headline'] = 'Your registration application status has updated';
                        $userData['status'] = $statusArray[$userData['status']];

                        $model->_sendmail($userData, $emails);

                    } else if($params['action'] === 'proposal') {

                        $userData['subject'] = 'Registration Status Update';
                        $userData['headline'] = 'Your registration application status has updated';
                        $userData['status'] = $statusArray[$userData['status']];
                        $model->_sendmail($userData, $emails);
                    
                    }

                } else {

                    $rs = 1;

                }


                if($rs){

                    return json_encode(Generic::getHeader([

                        'isSuccessful'=>true,

                        'statusCode' => 200,

                        'data'=> [

                            'details'=>['message' => 'Status updated successfully', 'id'=> $params['id']]

                        ]

                    ]));

                }else{

                    $errorMessage = 'Not Updated';

                }

            } 
            else {

                $errorMessage = 'Required field missing';

            }
        }


        if($errorMessage) {

            return json_encode(Generic::getHeader([

                        'isSuccessful'=>true,

                        'statusCode' => 417,

                        'data'=> [

                            'errors' => [ 'message' => [$errorMessage]] 

                        ]

                    ]));
        }


    }

    public function actionSavestudy()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new StudyForm();

            $model->student_id = isset($data['student_id']) ? $data['student_id'] : '';

            $model->name = isset($data['name']) ? $data['name'] : '';

            $model->email = isset($data['email']) ? $data['email'] : '';

            $model->mobile = isset($data['mobile']) ? $data['mobile'] : '';

            $model->collage = isset($data['collage']) ? $data['collage'] : '';

            $model->specialization = isset($data['specialization']) ? $data['specialization'] : '';

            $model->first_sem = isset($data['first_sem']) ? $data['first_sem'] : '';

            $model->first_sem_year = isset($data['first_sem_year']) ? $data['first_sem_year'] : '';

            $model->last_sem = isset($data['last_sem']) ? $data['last_sem'] : '';

            $model->last_sem_year = isset($data['last_sem_year']) ? $data['last_sem_year'] : '';
            
            $model->gpa_low_reason = isset($data['gpa_low_reason']) ? $data['gpa_low_reason'] : '';

            $model->gpa_rem_hrs = isset($data['gpa_rem_hrs']) ? $data['gpa_rem_hrs'] : '';

            $model->gpa_warning = isset($data['gpa_warning']) ? $data['gpa_warning'] : '';

            $model->gpa = isset($data['gpa']) ? $data['gpa'] : '';

            $model->last_sem_gpa = isset($data['last_sem_gpa']) ? $data['last_sem_gpa'] : '';

            $model->two_sem_gpa = isset($data['two_sem_gpa']) ? $data['two_sem_gpa'] : '';

            $model->created_by = isset($data['user_id']) ? $data['user_id'] : null;


            if($model->save()){

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 200,

                    'data'=> [

                        'details'=>['message' => 'Details saved successfully', 'student_id'=>$model->student_id]

                    ]

                ]));

            }else{

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 417,

                    'data'=> [

                        'errors' => $model->errors

                    ]

                ]));

            }

        }

    }

    public function actionSaveresult()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new ResultForm();

            $model->student_id = isset($data['student_id']) ? $data['student_id'] : '';

            $model->name = isset($data['name']) ? $data['name'] : '';

            $model->gpa = isset($data['gpa']) ? $data['gpa'] : '';

            $model->gpa_warning = isset($data['gpa_warning']) ? $data['gpa_warning'] : '';

            $model->mobile = isset($data['mobile']) ? $data['mobile'] : '';

            $model->application_date = isset($data['application_date']) ? $data['application_date'] : '';

            $model->academic_year = isset($data['academic_year']) ? $data['academic_year'] : '';

            $model->term = isset($data['term']) ? $data['term'] : '';

            $model->course_code = isset($data['course_code']) ? $data['course_code'] : '';

            $model->instructor_name = isset($data['instructor_name']) ? $data['instructor_name'] : '';
            
            $model->section = isset($data['section']) ? $data['section'] : '';

            $model->final_exam_date = isset($data['final_exam_date']) ? $data['final_exam_date'] : '';

            $model->apply = isset($data['apply']) ? $data['apply'] : '';

            $model->created_by = isset($data['user_id']) ? $data['user_id'] : null;


            if($model->save()){

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 200,

                    'data'=> [

                        'details'=>['message' => 'Details saved successfully', 'student_id'=>$model->student_id]

                    ]

                ]));

            }else{

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 417,

                    'data'=> [

                        'errors' => $model->errors

                    ]

                ]));

            }

        }

    }


    public function actionDropcourse()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new CoursedropForm();

            $model->student_id = isset($data['student_id']) ? $data['student_id'] : '';

            $model->name = isset($data['name']) ? $data['name'] : '';

            $model->gpa = isset($data['gpa']) ? $data['gpa'] : '';

            $model->mobile = isset($data['mobile']) ? $data['mobile'] : '';

            $model->collage = isset($data['collage']) ? $data['collage'] : '';

            $model->section = isset($data['section']) ? $data['section'] : '';

            $model->credit_hours = isset($data['credit_hours']) ? $data['credit_hours'] : '';

            $model->regd_credit_hours = isset($data['regd_credit_hours']) ? $data['regd_credit_hours'] : '';

            $model->course_dropped = isset($data['course_dropped']) ? $data['course_dropped'] : '';

            $model->course_code = isset($data['course_code']) ? $data['course_code'] : '';

            $model->course_name = isset($data['course_name']) ? $data['course_name'] : '';

            $model->created_by = isset($data['user_id']) ? $data['user_id'] : null;


            if($model->save()){

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 200,

                    'data'=> [

                        'details'=>['message' => 'Details saved successfully', 'student_id'=>$model->student_id]

                    ]

                ]));

            }else{

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 417,

                    'data'=> [

                        'errors' => $model->errors

                    ]

                ]));

            }

        }

    }

    public function actionReadmission()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new ReadmissionForm();

            $model->reason = isset($data['reason']) ? $data['reason'] : '';

            $model->student_id = isset($data['student_id']) ? $data['student_id'] : '';

            $model->name = isset($data['name']) ? $data['name'] : '';

            $model->email = isset($data['email']) ? $data['email'] : '';

            $model->mobile = isset($data['mobile']) ? $data['mobile'] : '';

            $model->collage = isset($data['collage']) ? $data['collage'] : '';

            $model->specialization = isset($data['specialization']) ? $data['specialization'] : '';

            $model->first_sem = isset($data['first_sem']) ? $data['first_sem'] : '';

            $model->first_sem_year = isset($data['first_sem_year']) ? $data['first_sem_year'] : '';

            $model->last_sem = isset($data['last_sem']) ? $data['last_sem'] : '';

            $model->last_sem_year = isset($data['last_sem_year']) ? $data['last_sem_year'] : '';
            
            $model->quit_reason = isset($data['quit_reason']) ? $data['quit_reason'] : '';

            $model->gpa_rem_hrs = isset($data['gpa_rem_hrs']) ? $data['gpa_rem_hrs'] : '';

            $model->number_warning = isset($data['number_warning']) ? $data['number_warning'] : '';

            $model->gpa = isset($data['gpa']) ? $data['gpa'] : '';

            $model->last_sem_gpa = isset($data['last_sem_gpa']) ? $data['last_sem_gpa'] : '';

            $model->two_sem_gpa = isset($data['two_sem_gpa']) ? $data['two_sem_gpa'] : '';

            $model->created_by = isset($data['user_id']) ? $data['user_id'] : null;


            if($model->save()){

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 200,

                    'data'=> [

                        'details'=>['message' => 'Details saved successfully', 'student_id'=>$model->student_id]

                    ]

                ]));

            }else{

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 417,

                    'data'=> [

                        'errors' => $model->errors

                    ]

                ]));

            }

        }

    }


    public function actionDropsemester()
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new SemesterdropForm();

            $model->student_id = isset($data['student_id']) ? $data['student_id'] : '';

            $model->name = isset($data['name']) ? $data['name'] : '';

            $model->gpa = isset($data['gpa']) ? $data['gpa'] : '';

            $model->mobile = isset($data['mobile']) ? $data['mobile'] : '';

            $model->collage = isset($data['collage']) ? $data['collage'] : '';

            $model->specialization = isset($data['specialization']) ? $data['specialization'] : '';

            $model->term_dropped = isset($data['term_dropped']) ? $data['term_dropped'] : '';

            $model->academic_year = isset($data['academic_year']) ? $data['academic_year'] : '';

            $model->drop_reason = isset($data['drop_reason']) ? $data['drop_reason'] : '';

            $model->created_by = isset($data['user_id']) ? $data['user_id'] : null;


            if($model->save()){

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 200,

                    'data'=> [

                        'details'=>['message' => 'Details saved successfully', 'student_id'=>$model->student_id]

                    ]

                ]));

            }else{

                return json_encode(Generic::getHeader([

                    'isSuccessful'=>true,

                    'statusCode' => 417,

                    'data'=> [

                        'errors' => $model->errors

                    ]

                ]));

            }

        }

    }

    public function actionVerifyotp() 
    {

        if(Yii::$app->request->post()){

            $data = Yii::$app->request->post();

            $model = new ReadmissionForm();

            $email = isset($data['email']) ? $data['email'] : '';
            $otp = isset($data['otp']) ? $data['otp'] : '';

            $connection = Yii::$app->getDb();
            $command = $connection->createCommand("select * from user where email = :email AND otp = :otp", [
                    ':email' => $email, 
                    ':otp' => $otp
                ]);

            $result = $command->queryAll();

            $message = !empty($result) ? 'true' : 'false';
    
            return json_encode(Generic::getHeader([

                'isSuccessful'=>true,

                'statusCode' => 200,

                'data'=> [

                    'details'=>['message' => $message, 'email'=>$data['email']]

                ]

            ]));

        }

    }

    public function actionPdf(){
        
        $mpdf = new mPDF();
        $userData = Yii::$app->request->post();
        $logo = Yii::getAlias('@webroot/cacs-logo.jpeg');
        $body = '<table align="center"><tr><th colspan="2"><img src="'.$logo.'" height="80px"></th></tr>';
        $body .= '<tr><th colspan="2">'.$userData['title'].'</th></tr>';
        $body .= '</table>';
        $mpdf->WriteHTML($body);
        $mpdf->Output('MyPDF.pdf', 'F');
        exit;
    }
    

}
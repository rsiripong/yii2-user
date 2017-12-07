<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace rsiripong\user\controllers;

use rsiripong\user\Finder;
use rsiripong\user\models\RegistrationForm;
use rsiripong\user\models\ResendForm;
use rsiripong\user\models\User;
use rsiripong\user\traits\AjaxValidationTrait;
use rsiripong\user\traits\EventTrait;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use \yii\web\UploadedFile;
use \yii\helpers\BaseFileHelper;
use yii\imagine\Image;
use Imagine\Image\Box;
use rsiripong\rbac\models\Assignment;
use Yii;

/**
 * RegistrationController is responsible for all registration process, which includes registration of a new account,
 * resending confirmation tokens, email confirmation and registration via social networks.
 *
 * @property \rsiripong\user\Module $module
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RegistrationController extends Controller
{
    use AjaxValidationTrait;
    use EventTrait;

    /**
     * Event is triggered after creating RegistrationForm class.
     * Triggered with \rsiripong\user\events\FormEvent.
     */
    const EVENT_BEFORE_REGISTER = 'beforeRegister';

    /**
     * Event is triggered after successful registration.
     * Triggered with \rsiripong\user\events\FormEvent.
     */
    const EVENT_AFTER_REGISTER = 'afterRegister';

    /**
     * Event is triggered before connecting user to social account.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_CONNECT = 'beforeConnect';

    /**
     * Event is triggered after connecting user to social account.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_CONNECT = 'afterConnect';

    /**
     * Event is triggered before confirming user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_CONFIRM = 'beforeConfirm';

    /**
     * Event is triggered before confirming user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_CONFIRM = 'afterConfirm';

    /**
     * Event is triggered after creating ResendForm class.
     * Triggered with \rsiripong\user\events\FormEvent.
     */
    const EVENT_BEFORE_RESEND = 'beforeResend';

    /**
     * Event is triggered after successful resending of confirmation email.
     * Triggered with \rsiripong\user\events\FormEvent.
     */
    const EVENT_AFTER_RESEND = 'afterResend';

    /** @var Finder */
    protected $finder;

    /**
     * @param string           $id
     * @param \yii\base\Module $module
     * @param Finder           $finder
     * @param array            $config
     */
    public function __construct($id, $module, Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($id, $module, $config);
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'actions' => ['register', 'connect'], 'roles' => ['?']],
                    ['allow' => true, 'actions' => ['confirm', 'resend'], 'roles' => ['?', '@']],
                ],
            ],
        ];
    }

    /**
     * Displays the registration page.
     * After successful registration if enableConfirmation is enabled shows info message otherwise
     * redirects to home page.
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionRegister()
    {
        
        $requestpost = \Yii::$app->request->post();
        //\Yii::$app->session->removeAllFlashes();
        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }
        
        /** @var RegistrationForm $model */
        $model = \Yii::createObject(RegistrationForm::className());
        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_REGISTER, $event);
if($requestpost){
            if($requestpost['ajax']){
        $this->performAjaxValidation($model);
}}
      
        
        $profile = new \rsiripong\user\models\Profile();

        if ($model->load(\Yii::$app->request->post()) &&   
                $profile->load(\Yii::$app->request->post()) && 
                //$model->validate() && 
                $model->register() ) {
            
            //getId()
            //profileimage
            $this->Uploads(false,$model->user_id,'profileimage');
            
            $this->trigger(self::EVENT_AFTER_REGISTER, $event);
            
           // Assignment::updateAssignments();
            //$assignment = new Assignment();
            $assignment = Yii::createObject([
            'class'   => Assignment::className(),
            'user_id' => $model->user_id,
        ]);
            
            $assignment->user_id = $model->user_id;
            $assignment->items = ['famer'];
            $assignment->updateAssignments();

            return $this->render('/message', [
                'title'  => \Yii::t('user', 'Your account has been created'),
                'module' => $this->module,
            ]);
        }
        
        //list($profileinitialPreview,$profileinitialPreviewConfig) = $this->getInitialPreview($id,'profileimage');
        $profileinitialPreview = [];
        $profileinitialPreviewConfig = [];

        return $this->render('register', [
            'model'  => $model,
            'profile' =>$profile,
            'module' => $this->module,
             //'initialPreview'=>[],
        //'initialPreviewConfig'=>[],
        'profileinitialPreview'=>$profileinitialPreview,
          'profileinitialPreviewConfig'=>$profileinitialPreviewConfig,
            'filepluginOptions' => []
        ]);
    }

    /**
     * Displays page where user can create new account that will be connected to social account.
     *
     * @param string $code
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionConnect($code)
    {
        $account = $this->finder->findAccount()->byCode($code)->one();

        if ($account === null || $account->getIsConnected()) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = \Yii::createObject([
            'class'    => User::className(),
            'scenario' => 'connect',
            'username' => $account->username,
            'email'    => $account->email,
        ]);

        $event = $this->getConnectEvent($account, $user);

        $this->trigger(self::EVENT_BEFORE_CONNECT, $event);

        if ($user->load(\Yii::$app->request->post()) && $user->create()) {
            $account->connect($user);
            $this->trigger(self::EVENT_AFTER_CONNECT, $event);
            \Yii::$app->user->login($user, $this->module->rememberFor);
            return $this->goBack();
        }

        return $this->render('connect', [
            'model'   => $user,
            'account' => $account,
        ]);
    }

    /**
     * Confirms user's account. If confirmation was successful logs the user and shows success message. Otherwise
     * shows error message.
     *
     * @param int    $id
     * @param string $code
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionConfirm($id, $code)
    {
        $user = $this->finder->findUserById($id);

        if ($user === null || $this->module->enableConfirmation == false) {
            throw new NotFoundHttpException();
        }

        $event = $this->getUserEvent($user);

        $this->trigger(self::EVENT_BEFORE_CONFIRM, $event);

        $user->attemptConfirmation($code);

        $this->trigger(self::EVENT_AFTER_CONFIRM, $event);

        return $this->render('/message', [
            'title'  => \Yii::t('user', 'Account confirmation'),
            'module' => $this->module,
        ]);
    }

    /**
     * Displays page where user can request new confirmation token. If resending was successful, displays message.
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionResend()
    {
        if ($this->module->enableConfirmation == false) {
            throw new NotFoundHttpException();
        }

        /** @var ResendForm $model */
        $model = \Yii::createObject(ResendForm::className());
        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_RESEND, $event);

        $this->performAjaxValidation($model);

        if ($model->load(\Yii::$app->request->post()) && $model->resend()) {
            $this->trigger(self::EVENT_AFTER_RESEND, $event);

            return $this->render('/message', [
                'title'  => \Yii::t('user', 'A new confirmation link has been sent'),
                'module' => $this->module,
            ]);
        }

        return $this->render('resend', [
            'model' => $model,
        ]);
    }
    
    
    
    
 private function Uploads($isAjax=false,$ref=false,$InstancesName = 'upload_ajax') {
       
             if (\Yii::$app->request->isPost) {
                // print_r($_POST);
                //echo "|".$InstancesName."|";
                $images = UploadedFile::getInstancesByName($InstancesName);
                //$images = UploadedFile::getInstancesByName('profileimage');
                //UploadedFile::
                
               // print_r($images);exit;
                if ($images) {

                    //if(!$ref){
                    //if($isAjax===true){
                    //    $ref =Yii::$app->request->post('ref');
                    //}else{
                    //    $Freelance = Yii::$app->request->post('Freelance');
                    //    $ref = $Freelance['ref'];
                    //}
                    //}

                    //$this->CreateDir($ref);
                    //$this->CreateDir();

                    foreach ($images as $file){
                        //if($InstancesName == "profileimage"){ 
                        //$fileName       = '_photo' . '.' . $file->extension;
                        //}else{
                        $fileName       = $file->baseName . '.' . $file->extension;    
                        //}
                        $realFileName   = md5($file->baseName.time()) . '.' . $file->extension;
                        $thumbFileName   = md5($file->baseName.time()+1) . '.' . $file->extension;
                       // $savePath       = \app\models\Hrhdr::getUploadPath().'/';
                        //if($ref){
                        //$savePath       .= $ref.'/';
                        //}
                        $savePath = \app\models\Uploads::getUploadPath();
                        
                       // echo "test|";
                //echo \app\models\Uploads::getUploadPath();
               // echo "|";
                //echo \app\models\Uploads::getUploadUrl();
                        //$savePath       .= $realFileName;
                       // echo $savePath;exit;
                        if($file->saveAs($savePath.$realFileName)){

                            if($InstancesName == 'profileimage'){
                            $model = \app\models\Uploads::find()->where([
                                'ProjectID'=>$ref,
                                'type'=>'2'
                            ])->one();
                            }
                            if(!$model){
                            $model                  = new \app\models\Uploads;}
                            $model->ProjectID       = $ref;
                            $model->file_name       = $fileName;
                            $model->real_filename   = $realFileName;
                            
                            
                            if($this->isImage($savePath.$realFileName)){
                                 //$this->createThumbnail($ref,$realFileName);
                                 if($this->createThumbnail($savePath,$realFileName,$thumbFileName)){
                                     $model->thumb_filename   = $thumbFileName;
                                 }
                            }
                            if($InstancesName == 'upload_ajax'){
                                $model->type   = 1;
                            }elseif($InstancesName == 'profileimage'){
                                 $model->type   = 2;
                            }
                            if(!$model->save()){
                                //print_r($model->errors);
                                //print_r($model->attributes);
                               //exit;
                            }

                            if($isAjax===true){
                                echo json_encode(['success' => 'true']);
                            }

                        }else{
                            if($isAjax===true){
                                echo json_encode(['success'=>'false','eror'=>$file->error]);
                            }
                        }

                    }
                }
            }
    }
        private function createThumbnail($folderName=false,$fileName,$thumbFileName,$width=150){
        

        $orientation =1;
        try {
        $exif = @exif_read_data($folderName.$fileName, 'IFD0');
        $orientation = $exif['Orientation'];
        } catch( Exception $e ){}
        $rotate = 0;
      if($orientation == 6){
          $rotate=90;
      }
      if(Image::getImagine()->open($folderName.$fileName)
        ->thumbnail(new Box($width, $width))
        ->rotate($rotate)
        ->save($folderName.$thumbFileName , ['quality' => 90])){
            return true;
        }
      return false;
    }
    /*
    private function createThumbnail($folderName=false,$fileName,$thumbFileName,$width=150){
  //$uploadPath   = \app\models\Hrhdr::getUploadPath().'/';
  //if($folderName){
   //   $uploadPath .= $folderName.'/';
  //}
  //$fileName = "f561ae26b92190099544d2ed4f43176b.jpg";
  //$file         = $uploadPath.$fileName;
  
  $image        = \Yii::$app->image->load($folderName.$fileName);
  
  //Image::thumbnail('@webroot/img/test-photo.jpg', 120, 120)
  //$image = Image::getImagine();
  //$image2 = $image->open($file);
  //print_r($image2->getSize());exit;
  
  //echo $file.'|'.$image->width ."|".$image->height;
  //exit;
  
  //$image2->thumbnail(new Box(120,120));
  $image->resize($width,null, \yii\image\drivers\Image::INVERSE  );
  //echo $uploadPath.'thumbnail/'.$fileName;exit;
  if($image->save($folderName.$thumbFileName)){
      return true;
  }
  return false;
}
*/

public function isImage($filePath){
        return @is_array(getimagesize($filePath)) ? true : false;
}


    public function getInitialPreview($ref,$InstancesName = 'upload_ajax') {
        
        if($InstancesName == "profileimage"){ 
            $coor = " ProjectID=:ref  and type = 2";
            $data=[':ref'=>$ref];
        }else{
            $coor = "ProjectID=:ref and type = 1";
            $data=[':ref'=>$ref];
        }
        
       // echo $ref."|";exit;
        $datas = \app\models\Uploads::find()->where($coor,$data)->all();
        $initialPreview = [];
        $initialPreviewConfig = [];
        foreach ($datas as $key => $value) {
            array_push($initialPreview, $this->getTemplatePreview($value));
            array_push($initialPreviewConfig, [
                'caption'=> $value->file_name,
                'width'  => '120px',
                'url'    => \yii\helpers\Url::to(['/hrhdr/deletefileajax']),
                'key'    => $value->upload_id
            ]);
        }
        return  [$initialPreview,$initialPreviewConfig];
}

private function getTemplatePreview( $model){
        //$filePath = \app\models\Hrhdr::getUploadUrl();
        
        $filePath = \app\models\Uploads::getUploadPath().'/'.$model->real_filename;
        //.$model->ref.'/'.
        //$filePath .='thumbnail/'.$model->real_filename;
        
        $isImage  = $this->isImage($filePath);
        if($isImage){
            $file = 
                    \yii\helpers\Html::a(
                    \yii\helpers\Html::img(\app\models\Uploads::getUploadUrl().$model->thumb_filename,[
                'class'=>'file-preview-image', 
                'alt'=>$model->file_name, 'title'=>$model->file_name]),
                            \app\models\Uploads::getUploadUrl().$model->real_filename,[
                                'target'=>'_blank'
                            ]);
        }else{
            $file =  \yii\helpers\Html::a("<div class='file-preview-other'> " .
                     "<h2><i class='glyphicon glyphicon-file'></i></h2>" .
                     "</div>",
                            \app\models\Uploads::getUploadUrl().$model->real_filename,[
                                'target'=>'_blank'
                            ]);;
        }
        return $file;
}


}

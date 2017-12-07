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
use rsiripong\user\models\Profile;
use rsiripong\user\models\SettingsForm;
use rsiripong\user\models\User;
use rsiripong\user\Module;
use rsiripong\user\traits\AjaxValidationTrait;
use rsiripong\user\traits\EventTrait;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use \yii\helpers\Json;
use \yii\web\UploadedFile;
use \yii\helpers\BaseFileHelper;

use yii\imagine\Image;
use Imagine\Image\Box;
/**
 * SettingsController manages updating user settings (e.g. profile, email and password).
 *
 * @property \rsiripong\user\Module $module
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class SettingsController extends Controller
{
    use AjaxValidationTrait;
    use EventTrait;

    /**
     * Event is triggered before updating user's profile.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_PROFILE_UPDATE = 'beforeProfileUpdate';

    /**
     * Event is triggered after updating user's profile.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_PROFILE_UPDATE = 'afterProfileUpdate';

    /**
     * Event is triggered before updating user's account settings.
     * Triggered with \rsiripong\user\events\FormEvent.
     */
    const EVENT_BEFORE_ACCOUNT_UPDATE = 'beforeAccountUpdate';

    /**
     * Event is triggered after updating user's account settings.
     * Triggered with \rsiripong\user\events\FormEvent.
     */
    const EVENT_AFTER_ACCOUNT_UPDATE = 'afterAccountUpdate';

    /**
     * Event is triggered before changing users' email address.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_CONFIRM = 'beforeConfirm';

    /**
     * Event is triggered after changing users' email address.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_CONFIRM = 'afterConfirm';

    /**
     * Event is triggered before disconnecting social account from user.
     * Triggered with \rsiripong\user\events\ConnectEvent.
     */
    const EVENT_BEFORE_DISCONNECT = 'beforeDisconnect';

    /**
     * Event is triggered after disconnecting social account from user.
     * Triggered with \rsiripong\user\events\ConnectEvent.
     */
    const EVENT_AFTER_DISCONNECT = 'afterDisconnect';

    /**
     * Event is triggered before deleting user's account.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * Event is triggered after deleting user's account.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_DELETE = 'afterDelete';

    /** @inheritdoc */
    public $defaultAction = 'profile';

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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'disconnect' => ['post'],
                    'delete'     => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajaxautocomplete'],
                        'allow' => true,
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['profile', 'account', 'networks', 'disconnect', 'delete'],
                        'roles'   => ['@'],
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['confirm'],
                        'roles'   => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Shows profile settings form.
     *
     * @return string|\yii\web\Response
     */
    public function actionProfile()
    {
        $id = \Yii::$app->user->identity->getId();
        $model = $this->finder->findProfileById($id);

        if ($model == null) {
            $model = \Yii::createObject(Profile::className());
            $model->link('user', \Yii::$app->user->identity);
        }

        $event = $this->getProfileEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);
        if ($model->load(\Yii::$app->request->post()) && $model->save() ) {
           
            
             $this->Uploads(false,$id,'profileimage');
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Your profile has been updated'));
            $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
            return $this->refresh();
        }
list($profileinitialPreview,$profileinitialPreviewConfig) = $this->getInitialPreview($id,'profileimage');

        return $this->render('profile', [
            'profileinitialPreview'=>$profileinitialPreview,
          'profileinitialPreviewConfig'=>$profileinitialPreviewConfig,
            'model' => $model,
            'filepluginOptions'=>[]
        ]);
    }

    /**
     * Displays page where user can update account settings (username, email or password).
     *
     * @return string|\yii\web\Response
     */
    public function actionAccount()
    {
        /** @var SettingsForm $model */
        $model = \Yii::createObject(SettingsForm::className());
        $event = $this->getFormEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_ACCOUNT_UPDATE, $event);
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('user', 'Your account details have been updated'));
            $this->trigger(self::EVENT_AFTER_ACCOUNT_UPDATE, $event);
            return $this->refresh();
        }

        return $this->render('account', [
            'model' => $model,
        ]);
    }

    /**
     * Attempts changing user's email address.
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

        if ($user === null || $this->module->emailChangeStrategy == Module::STRATEGY_INSECURE) {
            throw new NotFoundHttpException();
        }

        $event = $this->getUserEvent($user);

        $this->trigger(self::EVENT_BEFORE_CONFIRM, $event);
        $user->attemptEmailChange($code);
        $this->trigger(self::EVENT_AFTER_CONFIRM, $event);

        return $this->redirect(['account']);
    }

    /**
     * Displays list of connected network accounts.
     *
     * @return string
     */
    public function actionNetworks()
    {
        return $this->render('networks', [
            'user' => \Yii::$app->user->identity,
        ]);
    }

    /**
     * Disconnects a network account from user.
     *
     * @param int $id
     *
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDisconnect($id)
    {
        $account = $this->finder->findAccount()->byId($id)->one();

        if ($account === null) {
            throw new NotFoundHttpException();
        }
        if ($account->user_id != \Yii::$app->user->id) {
            throw new ForbiddenHttpException();
        }

        $event = $this->getConnectEvent($account, $account->user);

        $this->trigger(self::EVENT_BEFORE_DISCONNECT, $event);
        $account->delete();
        $this->trigger(self::EVENT_AFTER_DISCONNECT, $event);

        return $this->redirect(['networks']);
    }

    /**
     * Completely deletes user's account.
     *
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!$this->module->enableAccountDelete) {
            throw new NotFoundHttpException(\Yii::t('user', 'Not found'));
        }

        /** @var User $user */
        $user  = \Yii::$app->user->identity;
        $event = $this->getUserEvent($user);

        \Yii::$app->user->logout();

        $this->trigger(self::EVENT_BEFORE_DELETE, $event);
        $user->delete();
        $this->trigger(self::EVENT_AFTER_DELETE, $event);

        \Yii::$app->session->setFlash('info', \Yii::t('user', 'Your account has been completely deleted'));

        return $this->goHome();
    }
    public function actionAjaxautocomplete($fname){
        //echo "";
        
        if($_GET['q']){
            $name = $_GET['q'];
            
            //$name = 'ยี่';
            
            /*
             * array(//:all,
                'select'=>'  distinct ('.$fieldname.') Producer',
          'condition'=>$fieldname."  like :Producer ",
          'params'=>array('Producer'=>"%".$name."%")
             */
            $fieldname = $fname;
            $models = Profile::find()
                    ->select('  distinct ('.$fieldname.') '.$fname.' ')
                    ->where($fieldname."  like :Producer ", array('Producer'=>"%".$name."%"))
                    ->all();
             $out = [];
          foreach($models as $rx){ //.each do |rx|
        
        // $name2 .= $rx->first_name;
        // $name2 .=" ";
            $name2 .= $rx->$fname;
            $name2 .= "\n";
            #render :text=>rx.first_name.to_s, :layout=>false
            $out[] = ['value' => $rx->$fname];
            }
            }
      //return $name2;
            return Json::encode($out);
    }
    
    
    
 private function Uploads($isAjax=false,$ref=false,$InstancesName = 'upload_ajax') {
       
             if (\Yii::$app->request->isPost) {
                 
                
                $images = UploadedFile::getInstancesByName($InstancesName);
                //$images = UploadedFile::getInstancesByName('profileimage');
                //UploadedFile::
                
                //print_r($images);exit;
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
                        
                        $uniqueid = time();
                        $uniqueid++;
                        //echo $uniqueid;exit;
                        //if($InstancesName == "profileimage"){ 
                        //$fileName       = '_photo' . '.' . $file->extension;
                        //}else{
                        $fileName       = $file->baseName . '.' . $file->extension;    
                        //}
                        $realFileName   = md5($file->baseName.$uniqueid) . '.' . $file->extension;
                        $uniqueid++;
                        $thumbFileName   = md5($file->baseName.$uniqueid) . '.' . $file->extension;
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
                            
                           // print_r($model->attributes);exit;
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

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

use rsiripong\user\filters\AccessRule;
use rsiripong\user\Finder;
use rsiripong\user\models\Profile;
use rsiripong\user\models\User;
use rsiripong\user\models\UserSearch;
use rsiripong\user\Module;
use rsiripong\user\traits\EventTrait;
use yii\base\ExitException;
use yii\base\Model;
use yii\base\Module as Module2;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use \yii\web\UploadedFile;
use \yii\helpers\BaseFileHelper;
use yii\helpers\Json;

use yii\imagine\Image;
use Imagine\Image\Box;

//use yii\imagine\Image;
//use Imagine\Gd;
//use Imagine\Image\Box;
//use Imagine\Image\BoxInterface;

/**
 * AdminController allows you to administrate users.
 *
 * @property Module $module
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com
 */
class AdminController extends Controller
{
    use EventTrait;

    /**
     * Event is triggered before creating new user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_CREATE = 'beforeCreate';

    /**
     * Event is triggered after creating new user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_CREATE = 'afterCreate';

    /**
     * Event is triggered before updating existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';

    /**
     * Event is triggered after updating existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_UPDATE = 'afterUpdate';

    /**
     * Event is triggered before updating existing user's profile.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_PROFILE_UPDATE = 'beforeProfileUpdate';

    /**
     * Event is triggered after updating existing user's profile.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_PROFILE_UPDATE = 'afterProfileUpdate';

    /**
     * Event is triggered before confirming existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_CONFIRM = 'beforeConfirm';

    /**
     * Event is triggered after confirming existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_CONFIRM = 'afterConfirm';

    /**
     * Event is triggered before deleting existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * Event is triggered after deleting existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_DELETE = 'afterDelete';

    /**
     * Event is triggered before blocking existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_BLOCK = 'beforeBlock';

    /**
     * Event is triggered after blocking existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_BLOCK = 'afterBlock';

    /**
     * Event is triggered before unblocking existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_BEFORE_UNBLOCK = 'beforeUnblock';

    /**
     * Event is triggered after unblocking existing user.
     * Triggered with \rsiripong\user\events\UserEvent.
     */
    const EVENT_AFTER_UNBLOCK = 'afterUnblock';

    /** @var Finder */
    protected $finder;

    /**
     * @param string  $id
     * @param Module2 $module
     * @param Finder  $finder
     * @param array   $config
     */
    public function __construct($id, $module, Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($id, $module, $config);
    }

    /** @inheritdoc */
    /*
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete'  => ['post'],
                    'confirm' => ['post'],
                    'block'   => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ];
    }
*/
    /**
     * Lists all User models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'actions-redirect');
        $searchModel  = \Yii::createObject(UserSearch::className());
        $getSearch = \Yii::$app->request->get();
        //print_r($getSearch);
        $dataProvider = $searchModel->search($getSearch);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        /** @var User $user */
        $user = \Yii::createObject([
            'class'    => User::className(),
            'scenario' => 'create',
        ]);
        $event = $this->getUserEvent($user);

        $this->performAjaxValidation($user);

        $this->trigger(self::EVENT_BEFORE_CREATE, $event);
        if ($user->load(\Yii::$app->request->post()) && $user->create()) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been created'));
            $this->trigger(self::EVENT_AFTER_CREATE, $event);
            return $this->redirect(['update', 'id' => $user->id]);
        }

        return $this->render('create', [
            'user' => $user,
        ]);
    }

    /**
     * Updates an existing User model.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);
        $user->scenario = 'update';
        $event = $this->getUserEvent($user);

        $this->performAjaxValidation($user);

        $this->trigger(self::EVENT_BEFORE_UPDATE, $event);
        if ($user->load(\Yii::$app->request->post()) && $user->save()) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Account details have been updated'));
            $this->trigger(self::EVENT_AFTER_UPDATE, $event);
            return $this->refresh();
        }

        return $this->render('_account', [
            'user' => $user,
        ]);
    }

    /**
     * Updates an existing profile.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdateprofile($id)
    {
        Url::remember('', 'actions-redirect');
        $user    = $this->findModel($id);
        $profile = $user->profile;

        if ($profile == null) {
            $profile = \Yii::createObject(Profile::className());
            $profile->link('user', $user);
        }
        $event   = $this->getProfileEvent($profile);

        $this->performAjaxValidation($profile);

        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);

        if ($profile->load(\Yii::$app->request->post()) ) {
            //exit;
            $profile->save();
            $this->Uploads(false,$id,'profileimage');
            //\Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Profile details have been updated'));
            $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
            return $this->refresh();
        }
        
        
        //list($initialPreview,$initialPreviewConfig) = $this->getInitialPreview($model->id);
list($profileinitialPreview,$profileinitialPreviewConfig) = $this->getInitialPreview($id,'profileimage');

        return $this->render('_profile', [
            'user'    => $user,
            'profile' => $profile,
             //'initialPreview'=>[],
        //'initialPreviewConfig'=>[],
            
        'profileinitialPreview'=>$profileinitialPreview,
          'profileinitialPreviewConfig'=>$profileinitialPreviewConfig,
            
            'filepluginOptions'=>[]
        ]);
    }

    /**
     * Shows information about user.
     *
     * @param int $id
     *
     * @return string
     */
    public function actionInfo($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);

        return $this->render('_info', [
            'user' => $user,
        ]);
    }
 public function actionView($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);

        return $this->render('_info', [
            'user' => $user,
        ]);
    }
    /**
     * If "dektrium/yii2-rbac" extension is installed, this page displays form
     * where user can assign multiple auth items to user.
     *
     * @param int $id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAssignments($id)
    {
        if (!isset(\Yii::$app->extensions['rsiripong/yii2-rbac'])) {
            throw new NotFoundHttpException();
        }
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);

        return $this->render('_assignments', [
            'user' => $user,
        ]);
    }

    /**
     * Confirms the User.
     *
     * @param int $id
     *
     * @return Response
     */
    public function actionConfirm($id)
    {
        $model = $this->findModel($id);
        $event = $this->getUserEvent($model);

        $this->trigger(self::EVENT_BEFORE_CONFIRM, $event);
        $model->confirm();
        $this->trigger(self::EVENT_AFTER_CONFIRM, $event);

        \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been confirmed'));

        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        if ($id == \Yii::$app->user->getId()) {
            \Yii::$app->getSession()->setFlash('danger', \Yii::t('user', 'You can not remove your own account'));
        } else {
            $model = $this->findModel($id);
            $event = $this->getUserEvent($model);
            $this->trigger(self::EVENT_BEFORE_DELETE, $event);
            $model->delete();
            $this->trigger(self::EVENT_AFTER_DELETE, $event);
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been deleted'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Blocks the user.
     *
     * @param int $id
     *
     * @return Response
     */
    public function actionBlock($id)
    {
        if ($id == \Yii::$app->user->getId()) {
            \Yii::$app->getSession()->setFlash('danger', \Yii::t('user', 'You can not block your own account'));
        } else {
            $user  = $this->findModel($id);
            $event = $this->getUserEvent($user);
            if ($user->getIsBlocked()) {
                $this->trigger(self::EVENT_BEFORE_UNBLOCK, $event);
                $user->unblock();
                $this->trigger(self::EVENT_AFTER_UNBLOCK, $event);
                \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been unblocked'));
            } else {
                $this->trigger(self::EVENT_BEFORE_BLOCK, $event);
                $user->block();
                $this->trigger(self::EVENT_AFTER_BLOCK, $event);
                \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been blocked'));
            }
        }

        return $this->redirect(Url::previous('actions-redirect'));
    }
    public function actionDynamicamphur(){
        
        $out = [];
         if (isset($_POST['depdrop_parents'])) {
             $parents = $_POST['depdrop_parents'];
             if ($parents != null) {
                 $ProvinceCode = $parents[0];
                 $list2 = \common\models\Amphur::find()->where('ProvinceCode=:ProvinceCode',array('ProvinceCode'=>$ProvinceCode))->all();
                 $out = $this->MapData($list2,'AmphurCode','AmphurName');
                 echo Json::encode(['output'=>$out, 'selected'=>'']);
                 return;
             }
         }
         echo Json::encode(['output'=>'', 'selected'=>'']);
        
    }
    public function actionDynamictambol(){
        
        
        
        $out = [];
         if (isset($_POST['depdrop_parents'])) {
             $parents = $_POST['depdrop_parents'];
             if ($parents != null) {
                 $AmphurCode = $parents[0];
                 $list2 = \common\models\Tambol::find()->where('AmphurCode=:AmphurCode',array('AmphurCode'=>$AmphurCode))->all();
                 $out = $this->MapData($list2,'TambolCode','TambolName');
                 echo Json::encode(['output'=>$out, 'selected'=>'']);
                 return;
             }
         }
         echo Json::encode(['output'=>'', 'selected'=>'']);
            
        
    }
    
    public function actionDynamicpostcode(){
        $get = \Yii::$app->request->get();
        if($get['tambolCode']){
        $tambolpostcode = \common\models\Tambolpostcode::find()->where('TambolCode=:TambolCode',[':TambolCode'=>$get['tambolCode']])
                ->all();
        $out = $this->MapData($tambolpostcode,'TambolCode','PostCode');
                 echo Json::encode(['output'=>$out, 'selected'=>'']);
                 return;
        }
        echo Json::encode(['output'=>'', 'selected'=>'']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $user = $this->finder->findUserById($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested page does not exist');
        }

        return $user;
    }

    /**
     * Performs AJAX validation.
     *
     * @param array|Model $model
     *
     * @throws ExitException
     */
    protected function performAjaxValidation($model)
    {
        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
            if ($model->load(\Yii::$app->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                echo json_encode(ActiveForm::validate($model));
                \Yii::$app->end();
            }
        }
    }
    
     protected function MapData($datas,$fieldId,$fieldName,$groupName=false){
     $obj = [];
     
     //Yii::t('core','Please Select')
             //array_push($obj, ['id'=>'-','name'=>Yii::t('core','Please Select')]);
     if($groupName == false){
         foreach ($datas as $key => $value) {
             array_push($obj, ['id'=>$value->{$fieldId},'name'=>$value->{$fieldName}]);
         }
     }else{
         //array_push($obj[$groupName], ['id'=>$value->{$fieldId},'name'=>$value->{$fieldName}]);
         foreach ($datas as $key => $value) {
         $obj[$value->{$groupName}][] = ['id'=>$value->{$fieldId},'name'=>$value->{$fieldName}];
         }
     }
     return $obj;
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
        
        
        $initialPreview = [];
        $initialPreviewConfig = [];
        /*
        $datas = \app\models\Uploads::find()->where($coor,$data)->all();
        foreach ($datas as $key => $value) {
            array_push($initialPreview, $this->getTemplatePreview($value));
            array_push($initialPreviewConfig, [
                'caption'=> $value->file_name,
                'width'  => '120px',
                'url'    => \yii\helpers\Url::to(['/hrhdr/deletefileajax']),
                'key'    => $value->upload_id
            ]);
        }
        */
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

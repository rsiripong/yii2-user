<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\helpers\Html;
//use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\ArrayHelper;
use kartik\widgets\DepDrop;
use yii\helpers\Url;
use kartik\widgets\Typeahead;
use common\models\Province;
use common\models\Amphur;
use common\models\Tambol;
use common\models\Emedulvl;

use rsiripong\juidatepicker\juidatepicker;
use kartik\widgets\FileInput;


/**
 * @var yii\web\View              $this
 * @var rsiripong\user\models\User $user
 * @var rsiripong\user\Module      $module
 */

$this->title = Yii::t('user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;


$promptMsg = Yii::t('app','Please Select');
?>
<div class="row">
   <div>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id'                     => 'registration-form',
                    'enableAjaxValidation'   => true,
                    'enableClientValidation' => false,
                     'options' => ['enctype' => 'multipart/form-data'],
                     'layout' => 'horizontal',
  
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'col-sm-9',
        ],
    ],
                    
                ]); ?>

<div class="col-sm-8">                
              
           
                <?= $form->field($model, 'username')->label(\Yii::t('user', 'Email')) ?>
     

                <?php if ($module->enableGeneratingPassword == false): ?>
                    <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'password_repeat')->passwordInput() ?>
                <?php endif ?>
    
                 
 
                 <?= $form->field($profile, 'title')->widget(Typeahead::classname(),['dataset' => [
        [
            
            'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
            'display' => 'value',
            'limit' => 10,
            'remote' => [
                'url' => Url::to(['/user/settings/ajaxautocomplete','fname'=>'title']).'&q=%QUERY' ,
                'wildcard' => '%QUERY'
            ]
        ]]]) ?>

                <?= $form->field($profile, 'name') ?>
                <?= $form->field($profile, 'surname') ?>




    
    
    
   

    </div>
                
    
    
    <div class="  col-sm-4">
        <center>
        <?php
        
        echo  FileInput::widget([
                   'name' => 'profileimage[]',
            'pluginOptions' => array_merge([
        'showCaption' => false,
        'showRemove' => false,
        'showUpload' => false,
        'initialPreview'=> $profileinitialPreview,
        'initialPreviewConfig'=> $profileinitialPreviewConfig,
        'browseClass' => 'btn btn-primary btn-block',
        'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
        'browseLabel' =>  'Select Photo',           
    ],$filepluginOptions),
    'options' => ['accept' => 'image/*'],
])
                     //->label('')
                     ;
        
        ?>
            </center>
  </div>
<div class="col-sm-12">  </div>
 <?= $form->field($profile, 'CID',[
         'template'=>'{label}<div class="col-sm-6">{input}{error}{hint}</div>',
        'options'=>[
        
            'class'=>'form-group col-sm-8'
        ]
    ])->hiddenInput()
            ->label(null,[ 'class'=>'control-label col-sm-6' ]) ?>

<div class="col-sm-8">
    <?php
    //echo $profile->CID;
    $profile->CID1 = substr($profile->CID, 0,1);  
    $profile->CID2 = substr($profile->CID, 1,4);  
    $profile->CID3 = substr($profile->CID, 5,5);
    $profile->CID4 = substr($profile->CID, 10,2);
    $profile->CID5 = substr($profile->CID, 12,1);
    ?>
    
     <div class="col-sm-2">
    <?= $form->field($profile, 'CID1')
            //->textInput(['maxlength' => true,'class'=>'form-control maskcid'])
            ->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '9',
                'clientOptions' => ['repeat' => 1, 'greedy' => false],
                'options'=>['class'=>'form-control maskcid','maxlength' => 1]])
            ->label(false); ?>
        </div>
        <div class="col-sm-3">
    <?= $form->field($profile, 'CID2')
            //->textInput(['maxlength' => true,'class'=>'form-control maskcid'])
            ->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '9',
                'clientOptions' => ['repeat' => 4, 'greedy' => false],
                'options'=>['class'=>'form-control maskcid','maxlength' => 4]])
            ->label('-'); 
            ?>
            </div>
        <div class="col-sm-3">
    <?= $form->field($profile, 'CID3')
            //->textInput(['maxlength' => true,'class'=>'form-control maskcid'])
            ->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '9',
                'clientOptions' => ['repeat' => 5, 'greedy' => false],
                'options'=>['class'=>'form-control maskcid','maxlength' => 5]])
            ->label('-');
                    ?>
            </div>
        <div class="col-sm-2">
    <?= $form->field($profile, 'CID4')
            //->textInput(['maxlength' => true,'class'=>'form-control maskcid'])
            ->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '9',
                'clientOptions' => ['repeat' => 2, 'greedy' => false],
                'options'=>['class'=>'form-control maskcid','maxlength' => 2]])
            ->label('-'); ?>
            </div>
        <div class="col-sm-2">
    <?= $form->field($profile, 'CID5')
            //->textInput(['maxlength' => true,'class'=>'form-control maskcid'])
            ->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '9',
                'clientOptions' => ['repeat' => 1, 'greedy' => false],
                'options'=>['class'=>'form-control maskcid','maxlength' => 1]])
            ->label('-'); ?>
             </div>
    
<?php

$script = <<< JS
        
        jQuery(".maskcid").keyup(function () {
    if (this.value.length == this.maxLength) {
        var n = $(".maskcid").size();
        var index = $(".maskcid").index(this);
        var domEl = $(".maskcid").get(index + 1);
        if((index + 1) < n){
        domEl.focus();
        }
    }
        var x3 = jQuery('#profile-cid1').val();
        x3 += jQuery('#profile-cid2').val();
        x3 += jQuery('#profile-cid3').val();
        x3 += jQuery('#profile-cid4').val();
        x3 += jQuery('#profile-cid5').val();
        jQuery("#profile-cid").val(x3);
});
JS;

$this->registerJs($script);        

?>
    </div>
  
<div class="col-sm-12">  
      <div class="  col-sm-4">
 <?= $form->field($profile, 'brthdate',['horizontalCssClasses' => ['wrapper' => 'col-sm-8',]])
            ->widget(JuiDatePicker::classname())
            ->label(null,[ 'class'=>'control-label col-sm-4' ]) ?>
        </div>
    <div class="  col-sm-4">
<?= $form->field($profile, 'age',['horizontalCssClasses' => ['wrapper' => 'col-sm-8',]])
            ->textInput(['maxlength' => true]) ?>
    </div>
    <div class="  col-sm-4">
     <?php echo  $form->field($profile, 'sex')
            ->inline()
             ->radioList(['1'=>'ชาย','2'=>'หญิง'],['class'=>'radio-inline'])
             //->label(null,[ 'class'=>'control-label col-sm-2' ]) 
             ?>
         </div>
              
</div>
<div class="col-sm-12"> 
    <div class="  col-sm-6">
 <?php 
 
  if($profile->EDULVLID == 99  && $profile->EdulvlOther){
     $displayfiled[0] ="display: inline";
     $displayfiled[1] ="display: none";
     }else{
     $displayfiled[0] ="display: none";
     $displayfiled[1] ="display: inline";
     }
     
          $other = yii\bootstrap\Html::activeTextInput($profile,'EdulvlOther',[
         'class'=>'form-control ',
         'placeholder' => "อื่นๆ ระบุ",
         'style'=>$displayfiled[0],
         //'style'=>"inline",
         ]);
 
 
 $emedulvl = ArrayHelper::map(Emedulvl::find()->all(),'EDULVLID','EDULVLNAME');
       $emedulvl[99] = "อื่นๆ ระบุ";
       
        echo $form->field($profile, 'EDULVLID',[
            'template' => '{label}{beginWrapper}{input}'.$other.'{error}{hint}{endWrapper}',
           'inputOptions' => ['style'=>$displayfiled[1]]
        ])->dropDownList(
                $emedulvl,
                [
                    'prompt'=>$promptMsg 
                
                ]);
				
   

$script = <<< JS
        jQuery('#profile-edulvlid').on('change',function(){
            var other = jQuery('#profile-edulvlother');
            var local = jQuery(this);
                if(local.val() == 99){local.hide();other.show();
                }else{local.show();other.hide();}
            });
        jQuery('#profile-edulvlother').on('keyup',function(){
            var other = jQuery(this);
         var local = jQuery('#profile-edulvlid');
            if(other.val() == ''){
                //alert(other.val());
                local.show();other.hide();
                }
        });
JS;

$this->registerJs($script);
?>   
        </div>
     <div class="  col-sm-6">


                <?= $form->field($profile, 'position_name')->label(null,[ 'class'=>'control-label col-sm-2' ]) ?>
         </div>
        </div>
                <?php 
                
               
       
    
               // $list2 = Durabledivision::find()->all();
        
        
       // echo $form->field($profile, 'ID_Div')->dropDownList(
        //        ArrayHelper::map($list2,'ID','NameDiv2'),
         //       ['id'=>'ddl-ID_Div',
         //           'prompt'=>$promptMsg ]);
    ?>
    
     <?php 
    
    // echo $form->field($profile, 'ID_Sec')->widget(DepDrop::classname(), [
           
     ///       'options'=>['id'=>'ddl-ID_Sec', 'prompt'=>$promptMsg],
     //       'data'=> ArrayHelper::map(Durablesection::find()
     //               ->where("ID_DIV=:ID_DIV",[':ID_DIV'=>$profile->ID_Div])
     //               ->all(),"ID","NameSec2"),
      //      'pluginOptions'=>[
     //           'depends'=>['ddl-ID_Div'],
      //          'placeholder'=>$promptMsg,
      //          'url'=>Url::to(['/durablegroup/dynamicsection'])
            
             
     //       ]
    //    ]);
   
    ?>

                 <?= $form->field($profile, 'address')->textarea()->label(null,[ 'class'=>'control-label col-sm-2' ]) ?>
                
                

<div class="  col-sm-6">
                 <?php 
   
                $list2 = Province::find()->all();
        
        
        echo $form->field($profile, 'ProvinceCode',['horizontalCssClasses' => ['wrapper' => 'col-sm-8',]])->dropDownList(
                ArrayHelper::map($list2,'ProvinceCode','ProvinceName'),
                ['id'=>'ddl-ProvinceCode',
                    'prompt'=>$promptMsg ])->label(null,[ 'class'=>'control-label col-sm-4' ]);
					
					
    ?>
</div>    
<div class="  col-sm-6">
                <?php 
    
     echo $form->field($profile, 'AmphurCode',['horizontalCssClasses' => ['wrapper' => 'col-sm-8',]])->widget(DepDrop::classname(), [
            
            'options'=>['id'=>'ddl-AmphurCode', 'prompt'=>$promptMsg],
            'data'=> ArrayHelper::map(Amphur::find()
                    ->where("ProvinceCode=:ProvinceCode",[':ProvinceCode'=>$profile->ProvinceCode])
                    ->all(),"AmphurCode","AmphurName"),
            'pluginOptions'=>[
                'depends'=>['ddl-ProvinceCode'],
                'placeholder'=>$promptMsg,
                'url'=>Url::to(['admin/dynamicamphur'])
            
             
            ]
        ])->label(null,[ 'class'=>'control-label col-sm-2' ]);
    
    ?>
</div> 
<div class="  col-sm-6">
                
                <?php 
    
     echo $form->field($profile, 'TambolCode',['horizontalCssClasses' => ['wrapper' => 'col-sm-8',]])->widget(DepDrop::classname(), [
            //'type'=>DepDrop::TYPE_SELECT2,
            'options'=>['id'=>'ddl-TambolCode', 'prompt'=>$promptMsg],
            'data'=> ArrayHelper::map(Tambol::find()
                    ->where("AmphurCode=:AmphurCode",[':AmphurCode'=>$profile->AmphurCode])
                    ->all(),"TambolCode","TambolName"),
            'pluginOptions'=>[
                'depends'=>['ddl-AmphurCode'],
                'placeholder'=>$promptMsg,
                'url'=>Url::to(['admin/dynamictambol'])
            
             
            ]
        ])->label(null,[ 'class'=>'control-label col-sm-4' ]);
    
    ?>
</div>    
<div class="  col-sm-6">
                
                <?= $form->field($profile, 'PostCode',['horizontalCssClasses' => ['wrapper' => 'col-sm-7',]])
            ->label(null,[ 'class'=>'control-label col-sm-3' ]) ?>
</div>    

<div class="  col-sm-6">
                <?= $form->field($profile, 'phone',['horizontalCssClasses' => ['wrapper' => 'col-sm-8',]])
            ->label(null,[ 'class'=>'control-label col-sm-4' ]) ?>
</div>     
                <?php //echo $form->field($profile, 'ext_phone') ?>
<div class="  col-sm-6">
                <?= $form->field($profile, 'mobile_phone',['horizontalCssClasses' => ['wrapper' => 'col-sm-7',]])
             ->label(null,[ 'class'=>'control-label col-sm-3' ]) ?>
</div> 
                
                
<div class="  col-sm-6">
                <?= $form->field($model, 'email',['horizontalCssClasses' => ['wrapper' => 'col-sm-8',]])
             ->label(null,[ 'class'=>'control-label col-sm-4' ]) ?>
</div> 
<div class="  col-sm-6">    </div> 


     

    <?php if(0){?>
                <?= $form->field($model, 'email') ?>
            <?= $form->field($profile, 'public_email')->label(null,[ 'class'=>'control-label col-sm-2' ]) ?>
     


<?php echo  $form->field($profile, 'inNSTDAProject')
            ->inline()
             ->radioList(['1'=>'พื้นที่เข้าร่วมโครงการกับ สวทช.','2'=>'ไม่ใช่พื้นที่เข้าร่วมโครงการกับ สวทช.'],['class'=>'radio-inline'])
        ->label(null,[ 'class'=>'control-label col-sm-2' ])
             //->label(null,[ 'class'=>'control-label col-sm-2' ]) 
             ?>      

<?php }?>
                
                
                
                
                
               

<div class="  col-sm-12">
 <?php if(1){?>
                <?= $form->field($model, 'reCaptcha')->widget(
    \himiklab\yii2\recaptcha\ReCaptcha::className()
    ,['siteKey' => '6Lcosg4TAAAAAGa4nb6UTYM1-dopoOCo72Zx5-DS','jsCallback'=>'verifyCallback']
) ?>
                <?php } ?>

</div>
                <script type="text/javascript">
      var verifyCallback = function(response) {
        //alert(response);
        jQuery('#registration-form').submit();
      };
</script>
 <?php if(0){?>

                <?= Html::submitButton(Yii::t('user', 'Sign up'), ['class' => 'btn btn-success btn-block']) ?>
 <?php }?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <p class="text-center">
            <?= Html::a(Yii::t('user', 'Already registered? Sign in!'), ['/user/security/login']) ?>
        </p>
    </div>
</div>
<?php

$script = <<<JS
         
        
 
    jQuery('#profile-brthdate').on('change', function() { 
         
        brthdate = jQuery(this).val();
        
        $.get("../report/calage", { brthdate:brthdate  },
  function(data){
    //alert("Data Loaded: " + data);
        jQuery('#profile-age').val(data);
        //jQuery('#plantproject-finishdate').datepicker( "refresh" );
       // jQuery( '#plantproject-finishdate' ).datepicker( "setDate",data );
        
  });
            });
        
        //ddl-TambolCode
    jQuery('#ddl-TambolCode').on('change', function() {
           
         tambolCode = jQuery(this).val();
         //alert(tambolCode);
        
        $.getJSON("admin/dynamicpostcode", { tambolCode:tambolCode  },
  function(data){
    //alert("Data Loaded: " + data.output[0].name);
        jQuery('#profile-postcode').val(data.output[0].name);
        //jQuery('#plantproject-finishdate').datepicker( "refresh" );
       // jQuery( '#plantproject-finishdate' ).datepicker( "setDate",data );
        
  });
        
        
   });
JS;
        
        $this->registerJs($script);

?>

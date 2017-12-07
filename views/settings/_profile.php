<?php

/*
 * This file is part of the Dektrium project
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
//use app\models\Durablesection;
//use app\models\Durabledivision;
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
 * @var yii\web\View                    $this
 * @var rsiripong\user\models\User       $user
 * @var rsiripong\user\models\Profile    $profile
 */

//$promptMsg = Yii::t('core','Please Select');
$promptMsg = \Yii::t('user', 'Please Select');
?>

<?php 
//$this->beginContent('@rsiripong/user/views/admin/update.php', ['user' => $user])
        ?>

<?php $form = ActiveForm::begin([
    'layout' => 'horizontal',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
     'options' => ['enctype' => 'multipart/form-data'],
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'col-sm-9',
        ],
    ],
]); ?>

<?php
    
      // echo $form->field($profile, 'SCHLID')->dropDownList(
        //        ArrayHelper::map(app\models\Emschl::find()->all(),'SCHLID','SchoolName2'),
         //       [
        //            'prompt'=>$promptMsg ]);
   //    echo Yii::$app->glib->dropdownSelectSchool($profile,$form,['prompt'=>$promptMsg ]);
       
    ?>
<div class="col-sm-8">  
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
    
  </div>

  <?= $form->field($profile, 'CID',[
         'template'=>'{label}<div class="col-sm-6">{input}{error}{hint}</div>',
        'options'=>[
        
            'class'=>'form-group col-sm-8'
        ]
    ])->hiddenInput()
            ->label(null,[ 'class'=>'control-label col-sm-6' ]) ?>

<div class="col-sm-12">
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



     <?= $form->field($profile, 'brthdate',[
          'template'=>'{label}<div class="col-sm-8">{input}{error}{hint}</div>',
         'options'=>[
        
            'class'=>'form-group col-sm-6'
        ]
     ])->widget(JuiDatePicker::classname())
            ->label(null,[ 'class'=>'control-label col-sm-4' ]) ?>

<?php
        //famer-brthdate
        $urlCalAge = Url::to(['/report/callage']);
$script = <<< JS
        jQuery("#profile-brthdate").change(function(){
           
        brthdate = jQuery(this).val();
        $.get("$urlCalAge", { brthdate: brthdate },
  function(data){
   
        jQuery("#profile-age").val(data);
  });


   })
JS;

$this->registerJs($script);        
        ?>


 <?= $form->field($profile, 'age',[
      'template'=>'{label}<div class="col-sm-8">{input}{error}{hint}</div>',
        'options'=>[
        
            'class'=>'form-group col-sm-3'
        ]
    ])->textInput(['maxlength' => true])
            ->label(null,[ 'class'=>'control-label col-sm-4' ]) ?>

     <?php echo  $form->field($profile, 'sex',[
      'template'=>'{label}<div class="col-sm-10">{input}{error}{hint}</div>',
        'options'=>[
        
            'class'=>'form-group col-sm-4'
        ]
    ])
            ->inline()
             ->radioList(['1'=>'ชาย','2'=>'หญิง'],['class'=>'radio-inline'])
             //->label(null,[ 'class'=>'control-label col-sm-2' ]) 
             ?>


<?php 
 
  if($profile->EdulvlID == 99  && $profile->EdulvlOther){
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
 
 
 $emedulvl = ArrayHelper::map(Emedulvl::find()->all(),'EdulvlID','EDULVLNAME');
       $emedulvl[99] = "อื่นๆ ระบุ";
       
        echo $form->field($profile, 'EdulvlID',[
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
                
                <?= $form->field($profile, 'position_name') ?>
        
                <?php 
                
                //echo "test|";
                //echo \app\models\Uploads::getUploadPath();
                //echo "|";
                //echo \app\models\Uploads::getUploadUrl();
       
    
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
                <?= $form->field($profile, 'phone') ?>
                <?php //echo $form->field($profile, 'ext_phone') ?>
                <?= $form->field($profile, 'mobile_phone')->widget(\yii\widgets\MaskedInput::className(), ['mask' => '999-999-9999',]) ?>
                
                
               
                 <?= $form->field($profile, 'address')->textarea() ?>
                
                
                 <?php 
   
                $list2 = Province::find()->all();
        
        
        echo $form->field($profile, 'ProvinceCode')->dropDownList(
                ArrayHelper::map($list2,'ProvinceCode','ProvinceName'),
                ['id'=>'ddl-ProvinceCode',
                    'prompt'=>$promptMsg ]);
					
					
    ?>
                <?php 
    
     echo $form->field($profile, 'AmphurCode')->widget(DepDrop::classname(), [
            
            'options'=>['id'=>'ddl-AmphurCode', 'prompt'=>$promptMsg],
            'data'=> ArrayHelper::map(Amphur::find()
                    ->where("ProvinceCode=:ProvinceCode",[':ProvinceCode'=>$profile->ProvinceCode])
                    ->all(),"AmphurCode","AmphurName"),
            'pluginOptions'=>[
                'depends'=>['ddl-ProvinceCode'],
                'placeholder'=>$promptMsg,
                'url'=>Url::to(['admin/dynamicamphur'])
            
             
            ]
        ]);
    
    ?>
                
                <?php 
    
     echo $form->field($profile, 'TambolCode')->widget(DepDrop::classname(), [
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
        ]);
    
    ?>
                
                <?= $form->field($profile, 'PostCode')->widget(\yii\widgets\MaskedInput::className(), ['mask' => '99999',]) ?>

<?php echo  $form->field($profile, 'inNSTDAProject')
            ->inline()
             ->radioList(['1'=>'พื้นที่เข้าร่วมโครงการกับ สวทช.','2'=>'ไม่ใช่พื้นที่เข้าร่วมโครงการกับ สวทช.'],['class'=>'radio-inline'])
        ->label(null,[ 'class'=>'control-label col-sm-2' ])
             //->label(null,[ 'class'=>'control-label col-sm-2' ]) 
             ?>     


<?php if(0){?>
<?= $form->field($profile, 'callname') ?>
 <?= $form->field($profile, 'public_email') ?>
<?= $form->field($profile, 'website') ?>
<?= $form->field($profile, 'location') ?>
<?= $form->field($profile, 'gravatar_email') ?>
<?= $form->field($profile, 'bio')->textarea() ?>
<?php }?>

<div class="form-group">
    <div class="col-lg-offset-3 col-lg-9">
        <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-block btn-success']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php 
//$this->endContent()
        
        ?>

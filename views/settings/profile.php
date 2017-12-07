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
//use app\models\Durablesection;
//use app\models\Durabledivision;
use yii\helpers\ArrayHelper;
use kartik\widgets\DepDrop;
use yii\helpers\Url;
use kartik\widgets\Typeahead;
use app\models\Province;
use app\models\Amphur;
use app\models\Tambol;
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var rsiripong\user\models\Profile $profile
 */

$this->title = Yii::t('user', 'Profile settings');
$this->params['breadcrumbs'][] = $this->title;
$promptMsg = Yii::t('core','Please Select');
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<div class="row">
    <div class="col-md-3">
        <?= $this->render('_menu') ?>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?= Html::encode($this->title) ?>
            </div>
            <div class="panel-body">
                <?php
                
                echo $this->render('_profile', [
                    
                    'profile' => $model,
                        
                        'profileinitialPreview'=>$profileinitialPreview,
          'profileinitialPreviewConfig'=>$profileinitialPreviewConfig,
            
            'filepluginOptions'=>[]
                        ])
                
                ?>
                <?php if(0){ ?>
                <?php $form = \yii\widgets\ActiveForm::begin([
                    'id' => 'profile-form',
                    'options' => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-sm-offset-3 col-lg-9\">{error}\n{hint}</div>",
                        'labelOptions' => ['class' => 'col-lg-3 control-label'],
                    ],
                    'enableAjaxValidation'   => true,
                    'enableClientValidation' => false,
                    'validateOnBlur'         => false,
                ]); ?>
                
                
                 <?= $form->field($model, 'title')->widget(Typeahead::classname(),['dataset' => [
        [
            
            'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
            'display' => 'value',
            'limit' => 10,
            'remote' => [
                'url' => Url::to(['/user/settings/ajaxautocomplete','fname'=>'title']).'&q=%QUERY' ,
                'wildcard' => '%QUERY'
            ]
        ]]]) ?>
                <?= $form->field($model, 'name') ?>
                <?= $form->field($model, 'surname') ?>
                <?= $form->field($model, 'callname') ?>
                <?= $form->field($model, 'position_name')->widget(Typeahead::classname(),['dataset' => [
        [
            
            'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
            'display' => 'value',
            'limit' => 10,
            'remote' => [
                'url' => Url::to(['/user/settings/ajaxautocomplete','fname'=>'position_name']).'&q=%QUERY' ,
                'wildcard' => '%QUERY'
            ]
        ]]]) ?>
        
                <?php 
    
              //  $list2 = Durabledivision::find()->all();
        
        
       // echo $form->field($model, 'ID_Div')->dropDownList(
        //        ArrayHelper::map($list2,'ID','NameDiv2'),
       //         ['id'=>'ddl-ID_Div',
         //           'prompt'=>$promptMsg ]);
    ?>
    
     <?php 
    
     //echo $form->field($model, 'ID_Sec')->widget(DepDrop::classname(), [
           
      //      'options'=>['id'=>'ddl-ID_Sec', 'prompt'=>$promptMsg],
     //       'data'=> ArrayHelper::map(Durablesection::find()
     //               ->where("ID_DIV=:ID_DIV",[':ID_DIV'=>$model->ID_Div])
     //               ->all(),"ID","NameSec2"),
     //       'pluginOptions'=>[
     //           'depends'=>['ddl-ID_Div'],
     //           'placeholder'=>$promptMsg,
     //           'url'=>Url::to(['/durablegroup/dynamicsection'])
            
             
     //       ]
     //   ]);
   
    ?>
                <?= $form->field($model, 'phone') ?>
                <?php //echo $form->field($model, 'ext_phone') ?>
                <?= $form->field($model, 'mobile_phone') ?>
                
                
                <?= $form->field($model, 'public_email') ?>
                 <?= $form->field($model, 'address')->textarea() ?>
                
                
                 <?php 
   
                $list2 = Province::find()->all();
        
        
        echo $form->field($model, 'ProvinceCode')->dropDownList(
                ArrayHelper::map($list2,'ProvinceCode','ProvinceName'),
                ['id'=>'ddl-ProvinceCode',
                    'prompt'=>$promptMsg ]);
    ?>
                <?php 
    
     echo $form->field($model, 'AmphurCode')->widget(DepDrop::classname(), [
            
            'options'=>['id'=>'ddl-AmphurCode', 'prompt'=>$promptMsg],
            'data'=> ArrayHelper::map(Amphur::find()
                    ->where("ProvinceCode=:ProvinceCode",[':ProvinceCode'=>$model->ProvinceCode])
                    ->all(),"AmphurCode","AmphurName"),
            'pluginOptions'=>[
                'depends'=>['ddl-ProvinceCode'],
                'placeholder'=>$promptMsg,
                'url'=>Url::to(['/emschl/dynamicamphur'])
            
             
            ]
        ]);
    
    ?>
                
                <?php 
    
     echo $form->field($model, 'TambolCode')->widget(DepDrop::classname(), [
            //'type'=>DepDrop::TYPE_SELECT2,
            'options'=>['id'=>'ddl-TambolCode', 'prompt'=>$promptMsg],
            'data'=> ArrayHelper::map(Tambol::find()
                    ->where("AmphurCode=:AmphurCode",[':AmphurCode'=>$model->AmphurCode])
                    ->all(),"TambolCode","TambolName"),
            'pluginOptions'=>[
                'depends'=>['ddl-AmphurCode'],
                'placeholder'=>$promptMsg,
                'url'=>Url::to(['/emschl/dynamictambol'])
            
             
            ]
        ]);
    
    ?>
                
                <?= $form->field($model, 'PostCode') ?>

 <?php if(0){ ?>               
                
                <?= $form->field($model, 'website') ?>

                <?= $form->field($model, 'location') ?>

                <?= $form
                    ->field($model, 'timezone')
                    ->dropDownList(
                        \yii\helpers\ArrayHelper::map(
                            \rsiripong\user\helpers\Timezone::getAll(),
                            'timezone',
                            'name'
                        )
                    ); ?>

                <?= $form
                    ->field($model, 'gravatar_email')
                    ->hint(
                        \yii\helpers\Html::a(
                            Yii::t('user', 'Change your avatar at Gravatar.com'),
                            'http://gravatar.com'
                        )
                    ) ?>

                <?= $form->field($model, 'bio')->textarea() ?>
 <?php }?>
                <div class="form-group">
                    <div class="col-lg-offset-3 col-lg-9">
                        <?= \yii\helpers\Html::submitButton(
                            Yii::t('user', 'Save'),
                            ['class' => 'btn btn-block btn-success']
                        ) ?><br>
                    </div>
                </div>

                <?php \yii\widgets\ActiveForm::end(); ?>
                
                <?php } ?>
            </div>
        </div>
    </div>
</div>

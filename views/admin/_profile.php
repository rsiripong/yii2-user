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
use app\models\Durablesection;
use app\models\Durabledivision;
use yii\helpers\ArrayHelper;
use kartik\widgets\DepDrop;
use yii\helpers\Url;
use kartik\widgets\Typeahead;
use app\models\Durabletbprovince;
use app\models\Durabletbamphur;
use app\models\Durabletbtambol;

/**
 * @var yii\web\View                    $this
 * @var rsiripong\user\models\User       $user
 * @var rsiripong\user\models\Profile    $profile
 */

$promptMsg = Yii::t('core','Please Select');
?>

<?php $this->beginContent('@rsiripong/user/views/admin/update.php', ['user' => $user]) ?>

<?php $form = ActiveForm::begin([
    'layout' => 'horizontal',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'col-sm-9',
        ],
    ],
]); ?>


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
                <?= $form->field($profile, 'callname') ?>
                <?= $form->field($profile, 'position_name') ?>
        
                <?php 
    
                $list2 = Durabledivision::find()->all();
        
        
        echo $form->field($profile, 'ID_Div')->dropDownList(
                ArrayHelper::map($list2,'ID','NameDiv2'),
                ['id'=>'ddl-ID_Div',
                    'prompt'=>$promptMsg ]);
    ?>
    
     <?php 
    
     echo $form->field($profile, 'ID_Sec')->widget(DepDrop::classname(), [
           
            'options'=>['id'=>'ddl-ID_Sec', 'prompt'=>$promptMsg],
            'data'=> ArrayHelper::map(Durablesection::find()
                    ->where("ID_DIV=:ID_DIV",[':ID_DIV'=>$profile->ID_Div])
                    ->all(),"ID","NameSec2"),
            'pluginOptions'=>[
                'depends'=>['ddl-ID_Div'],
                'placeholder'=>$promptMsg,
                'url'=>Url::to(['/durablegroup/dynamicsection'])
            
             
            ]
        ]);
   
    ?>
                <?= $form->field($profile, 'phone') ?>
                <?= $form->field($profile, 'ext_phone') ?>
                <?= $form->field($profile, 'mobile_phone') ?>
                
                
                <?= $form->field($profile, 'public_email') ?>
                 <?= $form->field($profile, 'address')->textarea() ?>
                
                
                 <?php 
   
                $list2 = Durabletbprovince::find()->all();
        
        
        echo $form->field($profile, 'ProvinceCode')->dropDownList(
                ArrayHelper::map($list2,'ProvinceCode','ProvinceName'),
                ['id'=>'ddl-ProvinceCode',
                    'prompt'=>$promptMsg ]);
    ?>
                <?php 
    
     echo $form->field($profile, 'AmphurCode')->widget(DepDrop::classname(), [
            
            'options'=>['id'=>'ddl-AmphurCode', 'prompt'=>$promptMsg],
            'data'=> ArrayHelper::map(Durabletbamphur::find()
                    ->where("ProvinceCode=:ProvinceCode",[':ProvinceCode'=>$profile->ProvinceCode])
                    ->all(),"AmphurCode","AmphurName"),
            'pluginOptions'=>[
                'depends'=>['ddl-ProvinceCode'],
                'placeholder'=>$promptMsg,
                'url'=>Url::to(['/durablegroup/dynamicamphur'])
            
             
            ]
        ]);
    
    ?>
                
                <?php 
    
     echo $form->field($profile, 'TambolCode')->widget(DepDrop::classname(), [
            //'type'=>DepDrop::TYPE_SELECT2,
            'options'=>['id'=>'ddl-TambolCode', 'prompt'=>$promptMsg],
            'data'=> ArrayHelper::map(Durabletbtambol::find()
                    ->where("AmphurCode=:AmphurCode",[':AmphurCode'=>$profile->AmphurCode])
                    ->all(),"TambolCode","TambolName"),
            'pluginOptions'=>[
                'depends'=>['ddl-AmphurCode'],
                'placeholder'=>$promptMsg,
                'url'=>Url::to(['/durablegroup/dynamictambol'])
            
             
            ]
        ]);
    
    ?>
                
                <?= $form->field($profile, 'PostCode') ?>


<?php if(0){?>
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

<?php $this->endContent() ?>

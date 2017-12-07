<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
//use app\models\Durabledivision;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use rsiripong\juidatepicker;

//$promptMsg = Yii::t('core','Please Select');
?>
<div class="durableitem-search">

    <?php $form = ActiveForm::begin([
        //'action' => ['index'],
        'action'=>[$searchaction],
        'method' => 'get',
    ]); ?>
    
    <?php echo $form->field($model, 'ItemSearch')->textInput() ?>
    
    <?php 
    //echo $form->field($model, 'ID_Div')->dropDownList($items); 
    // Yii::$app->glib->dropdownDiv($form,$model);
    
   
                //$list2 = Durabledivision::model()->findAll();
                //$list2 = Durabledivision::find()->all();
       
        
       // echo $form->field($model, 'ID_Div')->dropDownList(ArrayHelper::map($list2,'ID','NameDiv2'),
         //       ['id'=>'ddl-ID_Div','prompt'=>$promptMsg]);
    ?>
	<?php if(0){?>
    <div class="col-md-6">
    <?php echo $form->field($model, 'AccessTime_Start')->widget(JuiDatePicker::classname()); ?>
        </div><div class="col-md-6">
    <?php echo $form->field($model, 'AccessTime_End')->widget(JuiDatePicker::classname()); ?>
    </div>
	<?php }?>
    <?php 
    //echo $form->field($model, 'ID_Div')->dropDownList($items); 
    // Yii::$app->glib->dropdownDiv($form,$model);
    
   
                //$list2 = Durabledivision::model()->findAll();
                //$list2 = Durabledivision::find()->all();
       
        
        echo $form->field($model, 'Status')->dropDownList(  $model->statuslist,
                ['id'=>'ddl-ID_Div','prompt'=>$promptMsg]);
    ?>
    
    
 <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

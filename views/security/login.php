<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use rsiripong\user\widgets\Connect;
use yii\helpers\Html;
//use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View                   $this
 * @var rsiripong\user\models\LoginForm $model
 * @var rsiripong\user\Module           $module
 */

$this->title = Yii::t('user', 'Sign in');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<div class="row">
    <div class="col-md-4 col-md-offset-6 col-sm-6 col-sm-offset-3 "  style="margin-top: 160px;">
        
<?php if(0){?>        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
<?php }?>            
            
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id'                     => 'login-form',
                    'action'=>  yii\helpers\Url::to(['/user/login','t'=>time()]),
                    //'enableAjaxValidation'   => true,
                    //'enableClientValidation' => false,
                    //'validateOnBlur'         => false,
                    //'validateOnType'         => false,
                    //'validateOnChange'       => false,
                     'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-sm-4',
            'offset' => 'col-sm-offset-4',
            'wrapper' => 'col-sm-8'
        ]
    ],
    'layout' => 'horizontal',
                    
                ]) ?>

                <?= $form->field(
                    $model,
                    'login',
                    ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => '1']]
                ) ?>

                <?= $form
                    ->field(
                        $model,
                        'password',
                        ['inputOptions' => ['class' => 'form-control', 'tabindex' => '2']]
                    )
                    ->passwordInput()
                    ->label(
                        Yii::t('user', 'Password')
                        .($module->enablePasswordRecovery ?
                            ' (' . Html::a(
                                Yii::t('user', 'Forgot password?'),
                                ['/user/recovery/request'],
                                ['tabindex' => '5']
                            )
                            . ')' : '')
                    ) ?>
<?php if(0){?>
                <?= $form->field($model, 'rememberMe')->checkbox(['tabindex' => '4']) ?>
<?php }?>
                
                <?php if(0){?>
                <?= $form->field($model, 'reCaptcha')->widget(
    \himiklab\yii2\recaptcha\ReCaptcha::className()
    ,['siteKey' => '6Lcosg4TAAAAAGa4nb6UTYM1-dopoOCo72Zx5-DS','jsCallback'=>'verifyCallback']
) ?>
                <?php } ?>
                <script type="text/javascript">
      var verifyCallback = function(response) {
        //alert(response);
        jQuery('#login-form').submit();
      };
</script>
                
                 <?php if(1){?>
                <?= Html::submitButton(
                    Yii::t('user', 'Sign in'),
                    ['class' => 'btn btn-primary btn-block', 'tabindex' => '3']
                ) ?>
                 <?php }?>

                <?php ActiveForm::end(); ?>
                
                <br/>
                <?php if ($module->enableConfirmation): ?>
            <p class="text-center">
                <?= Html::a(Yii::t('user', 'Didn\'t receive confirmation message?'), ['/user/registration/resend']) ?>
            </p>
        <?php endif ?>
        <?php if ($module->enableRegistration): ?>
            <p class="text-center">
                <?php
                
                //echo Html::a(Yii::t('user', 'Don\'t have an account? Sign up!'), ['/user/registration/register']);
                
                
                echo Html::button(Yii::t('user', 'Don\'t have an account? Sign up!'),
                    ['class'=>'btn btn-primary btn-block',
                        'onclick'=>"window.location.href = '" . \Yii::$app->urlManager->createUrl(['/user/registration/register']) . "';",
                        'data-toggle'=>'tooltip',
                        'title'=>Yii::t('user', 'Don\'t have an account? Sign up!'),
                    ]
                )
                
                ?>
                
                <?php
                
                //echo Html::a(Yii::t('user', 'Don\'t have an account? Sign up!'), ['/user/registration/register']);
                
                
                echo Html::button(Yii::t('user', 'Back to website'),
                    ['class'=>'btn btn-primary btn-block',
                        'onclick'=>"window.location.href = '" . \Yii::$app->urlManager->createUrl(['../frontend']) . "';",
                        'data-toggle'=>'tooltip',
                        //'title'=>Yii::t('user', 'Don\'t have an account? Sign up!'),
                    ]
                )
                
                ?>
            </p>
        <?php endif ?>
            </div>
<?php if(0){?>            
            
        </div>
<?php }?>        
        
        
        <?= Connect::widget([
            'baseAuthUrl' => ['/user/security/auth'],
        ]) ?>
    </div>
</div>

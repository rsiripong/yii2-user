<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace rsiripong\user\models;

use rsiripong\user\traits\ModuleTrait;
use Yii;
use yii\base\Model;

/**
 * Registration form collects user input on registration process, validates it and creates new User model.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RegistrationForm extends Model
{
    use ModuleTrait;
    /**
     * @var string User email address
     */
    public $email;

    /**
     * @var string Username
     */
    public $username;

    /**
     * @var string Password
     */
    public $password;
    public $password_repeat;
    
    //public $captcha;
    
    public $user_id;
    
     public $reCaptcha;
        /**
         * @inheritdoc
         */
        

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $user = $this->module->modelMap['User'];

        return [
            // username rules
            'usernameLength'   => ['username', 'string', 'min' => 3, 'max' => 255],
            'usernameTrim'     => ['username', 'filter', 'filter' => 'trim'],
            //'usernamePattern'  => ['username', 'match', 'pattern' => $user::$usernameRegexp],
            'usernameRequired' => ['username', 'required'],
            'usernameUnique'   => [
                'username',
                'unique',
                'targetClass' => $user,
                'message' => Yii::t('user', 'This username has already been taken')
            ],
            // email rules
            'emailTrim'     => ['email', 'filter', 'filter' => 'trim'],
            //'emailRequired' => ['email', 'required'],
            'emailPattern'  => ['email', 'email'],
            'emailUnique'   => [
                'email',
                'unique',
                'targetClass' => $user,
                'message' => Yii::t('user', 'This email address has already been taken')
            ],
            // password rules
            'passwordRequired' => ['password', 'required', 'skipOnEmpty' => $this->module->enableGeneratingPassword],
            'passwordLength'   => ['password', 'string', 'min' => 4, 'max' => 72],
            ['password_repeat', 'required'],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'skipOnEmpty' => false,'message'=>"Passwords don't match" ],
           
            
           // ['captcha', 'required'],
            //['captcha', 'captcha']
            [['reCaptcha'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className()
                ,'secret' => '6Lcosg4TAAAAAGFy172rLJWDcyv6ccfdumIPu2c6'
                ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'    => Yii::t('user', 'Email'),
            'username' => Yii::t('user', 'Username'),
            'password' => Yii::t('user', 'Password'),
            'password_repeat' => Yii::t('user', 'Password Repeat'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'register-form';
    }

    /**
     * Registers a new user account. If registration was successful it will set flash message.
     *
     * @return bool
     */
    public function register()
    {
      /*
        if (!$this->validate()) {
            //print_r($this->errors);
            //exit;
            return false;
            
        }
       * 
       */
 
        /** @var User $user */
        $user = Yii::createObject(User::className());
        $user->setScenario('register');
        $this->loadAttributes($user);

        if (!$user->register()) {
         //  print_r($user->errors);
           // exit;
            return false;
        }
        $this->user_id = $user->id;
        
        Yii::$app->session->setFlash(
            'info',
            Yii::t(
                'user',
                'Your account has been created and a message with further instructions has been sent to your email'
            )
        );

        return true;
    }

    /**
     * Loads attributes to the user model. You should override this method if you are going to add new fields to the
     * registration form. You can read more in special guide.
     *
     * By default this method set all attributes of this model to the attributes of User model, so you should properly
     * configure safe attributes of your User model.
     *
     * @param User $user
     */
    protected function loadAttributes(User $user)
    {
        $user->setAttributes($this->attributes);
    }
}

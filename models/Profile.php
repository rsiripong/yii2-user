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
use yii\db\ActiveRecord;

//use \app\models\Durabledivision;

/**
 * This is the model class for table "profile".
 *
 * @property integer $user_id
 * @property string  $name
 * @property string  $public_email
 * @property string  $gravatar_email
 * @property string  $gravatar_id
 * @property string  $location
 * @property string  $website
 * @property string  $bio
 * @property string  $timezone
 * @property User    $user
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com
 */
class Profile extends ActiveRecord
{
    use ModuleTrait;
    /** @var \rsiripong\user\Module */
    protected $module;
    
    var $CID1;
    var $CID2;
    var $CID3;
    var $CID4;
    var $CID5;

    /** @inheritdoc */
    public function init()
    {
        $this->module = \Yii::$app->getModule('user');
    }

    /**
     * Returns avatar url or null if avatar is not set.
     * @param  int $size
     * @return string|null
     */
    public function getAvatarUrl($size = 200)
    {
        return '//gravatar.com/avatar/' . $this->gravatar_id . '?s=' . $size;
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getUser()
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }
    

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['CID1','string', 'max' => 1],
            ['CID2','string', 'max' => 4],
            ['CID3','string', 'max' => 5],
            ['CID4','string', 'max' => 2],
            ['CID5','string', 'max' => 1],
            'bioString'            => ['bio', 'string'],
            
            'nameUnique'   => [
                ['name','surname'],
                'unique',
                'targetAttribute' => ['name','surname'],
                'message' => \Yii::t('user', 'This username has already been taken')
            ],
            
            //'timeZoneValidation'   => ['timezone', 'validateTimeZone'],
            
            //'publicEmailPattern'   => ['public_email', 'email'],
            //'gravatarEmailPattern' => ['gravatar_email', 'email'],
            
            'publicEmailPattern'   => ['public_email', 'string'],
            'gravatarEmailPattern' => ['gravatar_email', 'string'],
            
            'websiteUrl'           => ['website', 'url'],
            'nameLength'           => ['name', 'string', 'max' => 255],
            'surnameLength'           => ['surname', 'string', 'max' => 255],
            'publicEmailLength'    => ['public_email', 'string', 'max' => 255],
            'gravatarEmailLength'  => ['gravatar_email', 'string', 'max' => 255],
            'locationLength'       => ['location', 'string', 'max' => 255],
            'websiteLength'        => ['website', 'string', 'max' => 255],
            [[
                //'ID_Div','ID_Sec',
               // 'SCHLID',
                'title','callname','position_name','phone','EdulvlID',
                //'ext_phone',
                'inNSTDAProject','PostCode','CID','brthdate','age','sex',
                'mobile_phone','PostCode','TambolCode','AmphurCode','ProvinceCode','address','EdulvlOther'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'           => \Yii::t('user', 'Name'),
            'fullname'           => \Yii::t('user', 'Name'),
            'surname'           => \Yii::t('user', 'SurName'),
            'public_email'   => \Yii::t('user', 'Email (public)'),
            'gravatar_email' => \Yii::t('user', 'Gravatar email'),
            'location'       => \Yii::t('user', 'Location'),
            'website'        => \Yii::t('user', 'Website'),
            'bio'            => \Yii::t('user', 'Bio'),
            
            
            'ID_Div'       => \Yii::t('user', 'Division'),
            'ID_Sec'       => \Yii::t('user', 'Section'),
            'title'       => \Yii::t('user', 'Title'),
            'callname'       => \Yii::t('user', 'Callname'),
            'phone'       => \Yii::t('user', 'Phone'),
            'address'       => \Yii::t('user', 'Address'),
            //'ext_phone'       => \Yii::t('user', 'Ext Phone'),
            'mobile_phone'       => \Yii::t('user', 'Mobile Phone'),
            'position_name'       => \Yii::t('user', 'Position Name'),
            'TambolCode'       => \Yii::t('user', 'Tambol'),
            'AmphurCode'       => \Yii::t('user', 'Amphur'),
            'ProvinceCode'       => \Yii::t('user', 'Province'),
            'PostCode'       => \Yii::t('user', 'Post Code'),
            'SurName'=> \Yii::t('user', 'SurName'),
            //'SCHLID'=> \Yii::t('user', 'Schlid'),
            'CID' => \Yii::t('user', 'CID'),
            'brthdate' => \Yii::t('user', 'birth date'),
            'age' => \Yii::t('user', 'age'),
            'sex' => \Yii::t('user', 'sex'),
            'inNSTDAProject' => \Yii::t('user', 'in NSTDA Project'),
            'EDULVLID' => \Yii::t('user', 'Education'),
        ];
    }

    /**
     * Validates the timezone attribute.
     * Adds an error when the specified time zone doesn't exist.
     * @param string $attribute the attribute being validated
     * @param array $params values for the placeholders in the error message
     */
    public function validateTimeZone($attribute, $params)
    {
        if (!in_array($this->$attribute, timezone_identifiers_list())) {
            $this->addError($attribute, \Yii::t('user', 'Time zone is not valid'));
        }
    }

    /**
     * Get the user's time zone.
     * Defaults to the application timezone if not specified by the user.
     * @return \DateTimeZone
     */
    public function getTimeZone()
    {
        try {
            return new \DateTimeZone($this->timezone);
        } catch (\Exception $e) {
            // Default to application time zone if the user hasn't set their time zone
            return new \DateTimeZone(\Yii::$app->timeZone);
        }
    }

    /**
     * Set the user's time zone.
     * @param \DateTimeZone $timezone the timezone to save to the user's profile
     */
    public function setTimeZone(\DateTimeZone $timeZone)
    {
        $this->setAttribute('timezone', $timeZone->getName());
    }

    /**
     * Converts DateTime to user's local time
     * @param \DateTime the datetime to convert
     * @return \DateTime
     */
    public function toLocalTime(\DateTime $dateTime = null)
    {
        if ($dateTime === null) {
            $dateTime = new \DateTime();
        }

        return $dateTime->setTimezone($this->getTimeZone());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        //if ($this->isAttributeChanged('gravatar_email')) {
        //    $this->setAttribute('gravatar_id', md5(strtolower(trim($this->getAttribute('gravatar_email')))));
        //}

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        //return '{{%profile}}';
        return 'userprofile';
    }
    
    public function getFullname(){
        return $this->title.' '.$this->name." ".$this->surname;
    }
    
    // public  function getiDDiv(){
       // return @$this->hasOne(Hospital::className(), ['code' => 'hospital_code']);
    //    return @$this->hasOne(Durabledivision::className(), ['ID' => 'ID_Div']);
    //}
}

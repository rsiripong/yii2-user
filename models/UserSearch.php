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

use rsiripong\user\Finder;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form about User.
 */
class UserSearch extends Model
{
    /** @var string */
    public $username;

    /** @var string */
    public $email;

    /** @var int */
    public $created_at;

    /** @var string */
    public $registration_ip;

    /** @var Finder */
    protected $finder;
    
       public $ItemSearch;
   // public $DivOnly = 0;
    public $AccessTime_Start;
    public $AccessTime_End;
    public $ID_Div;
    public $Status;

    /**
     * @param Finder $finder
     * @param array  $config
     */
    public function __construct(Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            'fieldsSafe' => [['username', 'email', 'registration_ip',
                'created_at','ItemSearch','ID_Div','AccessTime_Start','AccessTime_End','Status'], 'safe'],
            'createdDefault' => ['created_at', 'default', 'value' => null],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username'        => Yii::t('user', 'Username'),
            'email'           => Yii::t('user', 'Email'),
            'created_at'      => Yii::t('user', 'Registration time'),
            'registration_ip' => Yii::t('user', 'Registration ip'),
        ];
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        
        $query = $this->finder->getUserQuery();
       $query->joinWith(['profile profile',]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate() )) {
            
            
            return $dataProvider;
        }

        if ($this->created_at !== null) {
            $date = strtotime($this->created_at);
            $query->andFilterWhere(['between', 'created_at', $date, $date + 3600 * 24]);
        }
        $query->andFilterWhere(['profile.ID_Div'=>$this->ID_Div ]);
        if(($this->ItemSearch) !=''){
        $query->andWhere("username+profile.name+profile.surname+profile.callname+profile.public_email like :ItemSearch",array(':ItemSearch'=>'%'.$this->ItemSearch.'%'));
        }
        
        if($this->Status=='1'){
            $query->andWhere(' blocked_at  IS NULL');
        }
         if($this->Status=='2'){
            $query->andWhere(' blocked_at  IS NOT  NULL');
        }
        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
                //->andFilterWhere(['like', 'username+durableuserprofile.name', $this->ItemSearch])
               
            ->andFilterWhere(['registration_ip' => $this->registration_ip]);
 
        return $dataProvider;
    }
    
      public function getStatuslist(){
            return array('1'=>'ใช้งาน','2'=>'ยกเลิกการใช้งาน');
        }
}

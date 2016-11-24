<?php


namespace rsiripong\user\traits;

use rsiripong\user\Module;

/**
 * Trait ModuleTrait
 * @property-read Module $module
 * @package rsiripong\user\traits
 */
trait ModuleTrait
{
    /**
     * @return Module
     */
    public function getModule()
    {
        return \Yii::$app->getModule('user');
    }
}

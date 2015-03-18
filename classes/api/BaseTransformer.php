<?php namespace DMA\Friends\Classes\API;

use Model;
use Response;
use League\Fractal\TransformerAbstract;
use League\Fractal\Scope;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;


class BaseTransformer extends TransformerAbstract
{
    /**
     * Return all attributes of the model
     * @var $instance October\Model
     * @param array
     */
    public function transform($instance)
    {
        if(!is_null($instance)) {
            return $this->getData($instance);
        }
        return [];
    }

    /**
     * Return all attributes of the model
     * @var $instance October\Model
     * @param array
     */
    public function getData($instance){
        return $instance->getAttributes();
    }
    
    
    /**
     * Quick hack until Fractal support embeded data without the use of
     * the data-key
     *
     * @see https://github.com/thephpleague/fractal/issues/37
     * 
     * {@inheritDoc}
     */    
    public function processIncludedResources(Scope $scope, $data)
    {
        $embeded = parent::processIncludedResources($scope, $data);
        $embeded = array_map(function($d){
            return current($d);
        }, $embeded);
        return $embeded;
    }


}



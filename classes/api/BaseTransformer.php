<?php namespace DMA\Friends\Classes\API;

use Model;
use Response;
use League\Fractal\TransformerAbstract;
use League\Fractal\Scope;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;


class BaseTransformer extends TransformerAbstract
{
    
    /**
     * When true call getExtendedData and append it to 
     * the tranformation
     * @var boolean
     */
    protected $useExtendedData = true;
    
    /**
     * 
     * @param boolean $basic 
     */
    public function __construct($useExtendedData=null)
    {
        $this->useExtendedData = (is_null($useExtendedData)) ? $this->useExtendedData : $useExtendedData;
    }
    
    /**
     * Return all attributes of the model
     * @var $instance October\Model
     * @param array
     */
    public function transform($instance)
    {
        if(!is_null($instance)) {
            $data = $this->getData($instance);
            if($this->useExtendedData) {
                $extended = $this->getExtendedData($instance);
                if(is_array($extended)) {
                    $data = array_merge($data, $extended);
                }
            }
            return $data;
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
     * Return extended data of the model. Useful for
     * creating details views of a model
     * @var $instance October\Model
     * @param array
     */
    public function getExtendedData($instance){
        return [];
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
        if (!is_null($embeded) && !is_bool($embeded)) {
            $embeded = array_map(function($d){
                return current($d);
            }, $embeded);
        }
        return $embeded;
    }


}



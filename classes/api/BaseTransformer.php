<?php namespace DMA\Friends\Classes\API;

use Log;
use Model;
use Response;
use League\Fractal\TransformerAbstract;
use League\Fractal\Scope;


class BaseTransformer extends TransformerAbstract
{
    
    /**
     * When true call getExtendedData and append it to 
     * the tranformation
     * @var boolean
     */
    protected $useExtendedData = true;
    
    /**
     * Don't enclude embeds even if the transformer
     * is set to use extended data. Useful improve 
     * perforamce and remove redundate data in nested data
     * @var array
     */
    protected $excludeEmbededs = [];
    
    
    /**
     * @param boolean $useExtendedData 
     */
    public function __construct($useExtendedData=null, $excludeEmbededs=[])
    {
        $this->useExtendedData = (is_null($useExtendedData)) ? $this->useExtendedData : $useExtendedData;
        $this->excludeEmbededs = $excludeEmbededs;
    }
    
    /**
     * Return all attributes of the model
     * @var $instance October\Model
     * @param array
     */
    public function transform($instance)
    {
        try{
            if(!is_null($instance)) {
                $data = $this->getData($instance);
                if($this->useExtendedData) {
                    $extended = $this->getExtendedData($instance);
                    if(is_array($extended)) {
                        $data = array_merge($data, $extended);
                    }
                }
                
                // Remove exclude embeds 
                $includes = array_diff($this->getDefaultIncludes(), $this->excludeEmbededs);
                $includes = array_unique($includes);
                
                $this->setDefaultIncludes( $includes);
                
                return $this->sanitize($data);
            }
            return [];
        }catch( Exception $e){
            // Send exception to log
            Log::error( get_class($this) . ' : ' .  $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Re-throw exception
            throw $e;
            
        }
    }
    
    /**
     * Basic funtion to clean up invisible unicode characters.
     * @internal
     * @param array $data
     */
    protected function sanitize(array $data){
        // Regex to find invisible unicode characters
        $invChar = '/\p{C}+/u';
        
        foreach($data as $key => $value){
            if(is_string($value)) {
                $data[$key] = preg_replace($invChar, '', $value);
            }
        }
        return $data;
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



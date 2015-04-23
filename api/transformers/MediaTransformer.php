<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;

class MediaTransformer extends BaseTransformer {
    
    

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getData()
     */
    public function getData($instance)
    {
        return [
                'thumbnail'      => $this->getImageUrl($instance, [50, 50]),
                'medium'         => $this->getImageUrl($instance, [300, 300]),
                'large'          => $this->getImageUrl($instance),
        ];
    }


    /**
     * Get Image URL of the reward
     * @param October\Rain\Database\Model $instance
     * @param array $size use the following format [$width, $height]
     */
    protected function getImageUrl(Model $instance, $size=null)
    {
        try{
            if (!is_null($instance->image)) {
                if (!is_null($size) && is_array($size)) {
                    return $instance->image->getThumb($size[0], $size[1], ['extension' => 'png']);
                }else{
                    # Force original image to be a PNG. I am doing this for consistency with other 
                    # images derivatives. But it my not necessary and getPath() will be enough
                    return $instance->image->getThumb('auto', 'auto', ['extension' => 'png']);
                }
            }
        }catch(\Exception $e){
            // Do nothing
        }
        return null;
    }
    
      
}

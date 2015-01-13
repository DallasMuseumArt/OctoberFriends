<?php namespace DMA\Friends\Activities;

use Httpful\Request;
use RainLab\User\Models\User;
use DMA\Friends\Models\Activity;
use DMA\Friends\Models\ActivityMetadata;
use DMA\Friends\Classes\ActivityTypeBase;

class LikeWorkOfArt extends ActivityTypeBase
{

    /**
     * {@inheritDoc}
     */
    public function details()
    {
        return [
            'name'          => 'Like a work of art',
            'description'   => 'enter an assession id to like a work of art',
        ];
    }

    /**
     * {%inheritDoc}
     */
    public static function process(User $user, $params)
    {
        if (!isset($params['code']) || empty($params['code'])) return false;

        if (!$data = self::isAssessionNumber($params['code'])) return false;

        if ($activity = Activity::findActivityType('LikeWorkOfArt')->first()) {          
            if($ret = parent::process($user, ['activity' => $activity])){
                
                // TODO: Find a better way to pass this data
                $activity->objectData = $data;
                unset($data['object_title']); // Don't save title in user metadata table
                
                ActivityMetadata::addUserActivity($user, $activity, $data);
            }
            return $ret;
            
        }

        return false;

    }

    /**
     * Take a string of text and determine if it is an assession #
     * TODO: This function will eventually be return to provide some sort of
     * proper validation to ensure that we are actually pulling a valid
     * assession number
     *
     * @param string A string of text to check 
     *
     * @return mixed boolean, array
     * boolean False if code is an assession number
     * array if code is an assession number
     */
    public static function isAssessionNumber($code)
    {
        // Brain API request template URL
        $template = 'http://brain.dma.org/api/v1/collection/object/?fields=id,number,title&format=json&number=%s';
        
        // Clean code from spaces in case user miss type it
        $code     = str_replace(' ', '', $code);
        
        // Get URL
        $url      = sprintf($template, urlencode($code));
        
        // Call Brain
        $response = Request::get($url)                   
                            ->send();
        
        if($obj = @$response->body->results[0]){
            $data = [
                'object_id'     => $obj->id,
                'object_number' => $obj->number,
                'object_title'  => $obj->title
            ];
            
            return $data;
           
        }
        return false;
        
    }
}
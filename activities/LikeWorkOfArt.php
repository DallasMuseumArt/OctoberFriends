<?php namespace DMA\Friends\Activities;

use Log;
use Lang;
use Session;
use Httpful\Request;
use RainLab\User\Models\User;
use DMA\Friends\Models\Activity;
use DMA\Friends\Models\ActivityMetadata;
use DMA\Friends\Classes\ActivityTypeBase;
use DMA\Friends\Classes\FriendsLog;
use DMA\Friends\Models\Settings as FriendsSettings;

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
    public static function process(User $user, $params = [])
    {        
        if (!isset($params['code']) || empty($params['code'])) return false;

        if ($activity = Activity::findActivityType('LikeWorkOfArt')->first()) {          
            $code = $params['code'];
            
            if ($data = self::isAssessionNumber($code)){
            
                // Skip activity process if user has already like this work of art
                $likeCount = ActivityMetadata::hasMetadataValue($user, $activity, 'object_id', $data['object_id'])->count();
     
                if ($likeCount == 0) {
                    // User haven't like this artwork yet
                    if ($ret = parent::process($user, ['activity' => $activity])) {
                        
                        // TODO: Find a better way to pass this data
                        $activity->objectData = $data;
    
                        // Save user metada activity
                        ActivityMetadata::addUserActivity($user, $activity, $data, ['object_title']);
    
                        FriendsLog::artwork([
                            'user'          => $user,
                            'artwork_id'    => $params['code'],
                        ]);
                    }
                
                    return $ret;
                } else {
                    Session::put('activityError', Lang::get('dma.friends::lang.activities.alreadyLikeWorkArtError', ['code' => $params['code']]));
                }
                
            } else {
                // Verify if user try to enter an Object number
                // Regex expression to match object number format
                $re = "/((([a-zA-Z0-9_\\-]+\\.){1,})([a-zA-Z0-9_\\-]+))/";
                $isObjectNumber = (preg_match_all($re, str_replace(' ', '',$code)) > 0); 
                       
                if ( $isObjectNumber ) {
                    Session::put('activityError', Lang::get('dma.friends::lang.activities.likeWorkArtCodeError', ['code' => $params['code']]));
                }
            }     
       }

        return false;

    }

    /**
     * Take a string of text and determine if it is an assession object
     *
     * @param string A string of text to check 
     *
     * @return mixed boolean, array
     * boolean False if code is an assession number
     * array if code is an assession number
     */
    public static function isAssessionNumber($code)
    {
        // Artwork API request template URL
        $baseUrl = FriendsSettings::get('artwork_api_baseurl',  false);
        $query   = FriendsSettings::get('artwork_api_endpoint',  false);
        
        if( $baseUrl && $query ) {
                
            // Build collection REST API URL 
            $template = rtrim($baseUrl, '/') . '/' . ltrim($query, '/'); 
                
            // Get Artwork API headers
            $headers = [];
            $headerSettings = FriendsSettings::get('artwork_api_headers', []);
            foreach ( $headerSettings as $h ){
                $key   = array_get($h, 'artwork_api_header', Null);
                $value = array_get($h, 'artwork_api_header_value', Null);
                
                if (!is_null($key) && !is_null($value) ){
                    $headers[$key] = $value;
                }
                
            }
            
            // Clean code from spaces in case user miss type it
            $code     = str_replace(' ', '', $code);

            
            // Get URL
            $url      = sprintf($template, urlencode($code));
            
            \Log::info($url);
            
            // Call REST API
            $response = Request::get($url) 
                                ->addHeaders($headers)
                                ->send();
            
            // TODO: find a way to abstract this into the settings pluging
            // so is not tide to a specific business logic. For now
            // this relay on DMA Brain API structure
            if($obj = @$response->body->results[0]){
                $data = [
                    'object_id'     => $obj->id,
                    'object_number' => $obj->number,
                    'object_title'  => $obj->title
                ];
                
                return $data;
               
            }
        }else{
            Log::error('Friends setting "artwork_api_baseurl and artwork_api_endpoint" settings are empty. Please configure them in the backend of OctoberCMS.');
        }
        return false;
        
    }
}
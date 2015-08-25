<?php namespace DMA\Friends\API\Resources;

use Response;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\Models\Settings as FriendsSettings;

class SettingsResource extends BaseResource {

    /**
     * @SWG\Definition(
     *     definition="settings.artwork_api.headers",
     *     type="object",
     *     @SWG\Property(
     *          property="key",
     *          type="string",  
     *     ),     
     *     @SWG\Property(
     *          property="value",
     *          type="string",  
     *     )    
     * )
     * 
     * 
     * @SWG\Definition(
     *     definition="settings.artwork_api",
     *     type="object",
     *     @SWG\Property(
     *          property="url",
     *          type="string",  
     *     ),     
     *     @SWG\Property(
     *          property="headers",
     *          type="array",
     *          items=@SWG\Schema(ref="#/definitions/settings.artwork_api.headers")
     *     )     
     * )
     * 
     * @SWG\Definition(
     *     definition="settings",
     *     type="object",
     *     @SWG\Property(
     *          property="artwork_api",
     *          type="object",
     *          ref="#/definitions/settings.artwork_api"
     *     )
     * )
     * 
     * 
     * 
     * @SWG\Get(
     *     path="settings",
     *     description="Returns public settings",
     *     summary="Return all public settings",
     *     tags={ "settings"},
     *        
     *     
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/settings")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *    )
     * )
     */
 
    public function index()
    {
        
       return array_merge([
            'artwork_api' => $this->getAPISettings(),    
       ], $this->getExtraSettings());
    }
    
    /**
     * Get Artwork API settings
     * @internal
     * @return array
     */
    private function getAPISettings()
    {
        // API SETTINGS
        // Get Artwork API headers
        $headers = [];
        $headerSettings = FriendsSettings::get('artwork_api_headers', []);
        foreach ( $headerSettings as $h ){
            $key   = array_get($h, 'artwork_api_header', Null);
            $value = array_get($h, 'artwork_api_header_value', Null);
        
            if (!is_null($key) && !is_null($value) ){
                $headers[] = [ 'header' =>  $key,  'value' => $value ];
            }
        
        }
        
        return [
            'url' => FriendsSettings::get('artwork_api_baseurl',  ''),
            'headers' => $headers
        ];
    }
       
    private function getExtraSettings()
    {
        $extra = [];
        
        // Get array of extra settings configured in OctoberCMS backend
        $raw = FriendsSettings::get('rest_api_extra_settings', []);
        foreach ($raw as $grp){
            $name   = $grp['rest_api_group_settings'];
            $values = $grp['rest_api_group_values'];
            
            $name = $this->normalizeKeyNames($name);
            
            // Convert group values into a dictionary
            foreach($values as $pair){
                $key   = $pair['rest_api_group_key'];
                $value = $pair['rest_api_group_value'];
                
                $key = $this->normalizeKeyNames($key);
                $extra[$name][$key] = $value;
            }
            
        }
        
        return $extra;
    }
    
    
    private function normalizeKeyNames($key)
    {
        // Lowercase
        $key = strtolower($key);
        // remove leading and tailling spaces
        $key = trim($key);
        // convert spaces to underscored
        $key = str_replace(' ', '_', $key);
        return $key;
    }
       
    public function show($id)
    {
        return Response::api()->errorForbidden();
    }
    
}

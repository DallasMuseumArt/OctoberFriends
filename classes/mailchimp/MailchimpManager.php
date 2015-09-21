<?php namespace DMA\Friends\Classes\Mailchimp;

use Log;
use Event;
use Exception;
use DMA\Friends\Classes\Mailchimp\MailchimpClient;
use DMA\Friends\Models\Settings as FriendSettings;

class MailchimpManager
{
    /**
     * @var string Mailchimp list id
     */
    private $listId;
   
    /**
     * Cached instance of MailChimpClient
     * @var \DMA\Friends\Classes\Mailchimp\MailchimpClient
     */
    private $mailchimp;
    
    /**
     * Get Instance of MailChimp REST Client
     * @return \DMA\Friends\Classes\Mailchimp\MailchimpClient
     */
    protected function getClient()
    {
        if( !$this->mailchimp ){
            $api_key      = FriendSettings::get('mailchimp_api_key', null);
            $listId       = FriendSettings::get('mailchimp_list_id', null);
            if ($api_key && $listId) {
                $this->mailchimp =  new MailchimpClient($api_key);
                $this->listId  = $listId;
            }else{
                Log::error('MailChimp API key or List ID are not configured in Friend Settings.');
            }
        }
        return $this->mailchimp;
    }
    
    /**
     * Create or Updated a member in the configure Mailchimp list,
     *  - If the member DON'T EXISTS in the list it will be added to it.
     *  - If the member EXITS in the list and $update is TRUE. Member in the 
     *    list will be updated with data in the DB
     *  - IF the member EXITS in the list and $update is FALSE. No data will
     *    be send to MailChimp
     *  
     * @param string $memberUID User email address
     * @param RainLab\User\Model\User $user
     * @param boolean $update Default value is true
     */
    public function syncMemberToMailChimp($memberUID, $user, $update=true)
    {
             
        # 1. Preparing data
        # 1.1. Get User mail, first name and last name
        $email      = $user->email;
        $firstname  = $user->metadata->first_name;
        $lastname   = $user->metadata->last_name;
        $emailOptIn = $user->metadata->email_optin;
       
        # 1.2. Extract merge fields ( extra fields usually points, membership, etc )
        // Consult merge_tags settings in the Mailchimp target list
        
        /* Mailchimp merge_tags*/
        # RESTATUS   // Partner Status
        # RENUMBER   // RE Constituent ID
        # FRNUMBER   // Friends Number
        # FRPOINTS   // Friends Points
        # FRVISITS
        # REEXPIRES  // Partner Expiration Date
        # ZIP
        # RELEVEL    // Partner Level
        # FRJOINDATE // Friends Join Date MM/DD/YYYY
       
        $joinDate = $user->created_at;
        $joinDate = ($joinDate) ? $joinDate->format('m/d/Y') : '';
        
        // TODO : Find a generic way to configure this fields from the backend
        // At the moment this are DMA specific configurations in Mailchimp List
        $merge_fields = [
            'FRPOINTS'   => $user->points,
            'FRJOINDATE' => $joinDate, 
            'FRNUMBER'   => $user->barcode_id, 
        ];

        // 1.3. Member status in the list base on 
        $memberStatus = ($emailOptIn) ? MailchimpClient::MEMBER_STATUS_SUBSCRIBED : MailchimpClient::MEMBER_STATUS_UNSUBSCRIBED;

        // 1.4 Detect if is required to update email address in Mailchmip
        if ($memberUID != $email){
            $merge_fields['EMAIL'] = $memberUID;
            $merge_fields['NEW-EMAIL'] = $email;
        }
        
               
        // 2. Call Mailchimp
        // The following method will detect the member doesn't exist in the list and will register it.
        $command = ($update) ? 'updateMember' : 'addMember';

        
        $this->doMailchimpCall($command, $memberUID, $firstname, $lastname, 
                               $memberStatus, $merge_fields, []);
        
    }
    
    /**
     * Internal use.
     * If promise fails with 404. Automatically doCreateUser is called
     */
    private function doMailchimpCall($command, $memberUID, $firstname, $lastname, $memberStatus, $merge_fields, $interests)
    {

        // Log::debug($command);
        // Log::debug($memberUID);
        // Log::debug($merge_fields);
        
        if (!in_array($command, ['updateMember', 'addMember'])){
            throw new Exception('Only updateMember and addMember methods are supported');    
        }
        
        if ( $client = $this->getClient() ) {
            $listId = $this->listId;
            
            // Prepare request
            $promise = $client->{$command}($listId, $memberUID, $firstname, $lastname, $memberStatus, $merge_fields, $interests);
            $promise = $promise->then(
                    function(MailchimpResponse $mailchimpResponse) use ($command, $memberUID){
                        // Succesful call
                        // Do nothing maybe add logging here
                        Log::debug('MailChimp successful command [ ' . $command . ' ] for member ' . $memberUID  );
                    },
                    function(MailchimpException $e) use ($listId, $command, $memberUID, $firstname, 
                                                         $lastname, $memberStatus, $merge_fields, $interests){
                        // Unsuccesful call
                        switch ($e->getCode())
                        {
                            case 404:
                                // Member don't exists in the list
                                // or list doesn't exist. We will try to
                                // the register the member
                                if ($command == 'updateMember'){
                                    Log::debug( 'User not found in Mailchimp list' );
                                    //unset($merge_fields['EMAIL']);
                                    //unset($merge_fields['NEW-EMAIL']);
                                    

                                    // Get interest ids of the configured groups
                                    $interests = [];

                                    $interesIds = FriendSettings::get('mailchimp_interest_id', []);
                                    foreach($interesIds as $key => $value ){
                                        $interests[$value] = true;
                                    }
                                    
                                    $this->doMailchimpCall('addMember', $memberUID, $firstname, $lastname, 
                                                                        $memberStatus, $merge_fields, $interests);
                                } else {
                                    Log::critical("List $listId does not exists.");
                                }
                                break;
            
                            case 400:
                                // Data fail to validated more likely
                                // merge_fields are wrong type or incorreclty mapped
                                Log::critical("Member $memberUID data failed Mailchimp validation.", $merge_fields);
                                break;
                        }
                     }
            );
            
            // Execute request
            $promise->wait();
        }
        
    }
    
    
    /**
     * Register events in the the platform that should trigger 
     * and Update or creation a member in the configured 
     * Mailchimp list
     */
    public function bindEvents()
    {
        if (!FriendSettings::get('mailchimp_integration', false)){
            return;
        }
        
        // Friends platform events
        $events = [
            'dma.friends.user.pointsEarned',
            'dma.friends.user.pointsRemoved',
        ];
        
        // Bind update or create events for the following models
        $models = [
            'RainLab\User\Models\User'    => [],
            'DMA\Friends\Models\Usermeta' => [ 'relUser' => 'user']
        ];
        
        foreach($models as $model => $options){
        
            $events[] = 'eloquent.created: ' . $model;
            $events[] = 'eloquent.updated: ' . $model;
        }
        

        $context = $this;        
        foreach($events as $evt){
        
            $fn = function() use ($context, $models, $evt){
                // TODO : How to detect multiple events of the same user
                // within an transaction. eg. Register a user fires 10 events
                // This causes 10 individual calls to Mailchimp
                
                $args = func_get_args();
                
                // First parameter should a model instance
                // but just in case we validated it exists
                if( $instance = @$args[0] ){

                    Log::debug('called ' .  $evt);
                    Log::debug(get_class($instance));
                    
                    $instanceClass = get_class($instance);
                    
                    $user = $instance;
                    if ( $instanceClass != 'RainLab\User\Models\User' ) {
                        // Get user relation field from the model
                        if( $relUser = @$models[$instanceClass]['relUser'] ) {
                            $user = $instance->{$relUser};
                        }
                    }

                    if ($user) {
                        $mailchimpMemberUID = $user->email;
                        
                        if ($context->startsWith($evt, 'eloquent.updated')){
                            // Detect if user change email
                            if ($newEmail = array_get($user->getDirty(), 'email', Null)){
                                $mailchimpMemberUID = $instance->getOriginal('email');
                            }
                        }
                        
                        // This events should call Mailchimp only if the $user model has a metadata model
                        // because the metadata model contains the email_optin status
                        // and name of the user. 
                        // In some cases is possible that the user has already data in the metadata table
                        // but the user reference in memory is not updated yet. So the following if stament
                        // tries to address the issue.  
                        
                        if (!$user->metadata) {
                            // Try to reload the user model from the database
                            // only if is not an user eloquent model event
                            // this is because we could be too earlier in the creation of the user
                            // but if a not eloquent event is trigger is because the user 
                            // is fully created but the reference in memory is outdated 
                            //if (!$context->startsWith($evt, 'eloquent')){
                            $user = $user->fresh();
                            //}
                           
                        }
                                
                        if ($user->metadata) {
                            // Updated or create a member in mailchimp list
                            $context->syncMemberToMailChimp($mailchimpMemberUID, $user);
                            
                        }
                        
                    }
   
      
         
                }
                
            };
           
            // Start listening this event
            Event::listen($evt, $fn);
        }
        
   
    }
    
    /**
     * Get list of groups on the MailChimp list
     */
    public function getMailchimpGroupList()
    {
        if($client = $this->getClient()){
            $promise = $client->getMailChimpGroupList($this->listId);
            $response = $promise->wait();
            return array_get($response->data, 'categories', []);
        }
        return [];
    }
    
    /**
     * Get list interest ids of a given group
     */
    public function getMailchimpInterestList($groupId)
    {
        $client = $this->getClient();
        if($client && $groupId ){
            $promise = $client->getMailChimpInterestList($this->listId, $groupId);
            $response = $promise->wait();
            return array_get($response->data, 'interests', []);
        }
        return [];
    }
    

    # UTILS
    private function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
     
    

}

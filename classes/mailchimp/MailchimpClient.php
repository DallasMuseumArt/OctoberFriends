<?php namespace DMA\Friends\Classes\Mailchimp;

use Log;
use Exception;
use DMA\Friends\Classes\Mailchimp\BaseMailchimpClient as BaseClient;

/**
 * Helper class to deal with mailchimp friends business logic 
 * @author Kristen Arnold, Carlos Arroyo 
 *
 */
class MailchimpClient extends BaseClient
{

    /**
     * Helper method to  add a new member to a list
     * @param string $listId
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $status Options are subscribed, unsubscribed, cleaned, pending
     * @param array $merge_fields See Mailchimp list for available merge_tags
     * @param array $interests associative array of group interest the member can join.
     *              format should be [ interest_id => <bool> ]. eg [ '3df5y36' => true ]
     * @return \Guzzle\Promise
     */    
    public function addMember($listId, $userEmail, $firstname, $lastname,  
                              $status=BaseClient::MEMBER_STATUS_SUBSCRIBED, $merge_fields=[], $interests=[])
    {
        
        $data = [
          'email_address'       => $userEmail,
          'email_type'          => 'html',
          'status'              => ($status) ? $status : BaseClient::MEMBER_STATUS_UNSUBSCRIBED,
          'merge_fields'        => array_merge([
                'FNAME' => $firstname,
                'LNAME' => $lastname  
          ], $merge_fields)
        ];
        
        if($interests) {
            $data['interests']  = $interests;
        }
        
        $endpoint = '/lists/' . $listId . '/members/';
        
        return $this->post($endpoint, $data);
       
     }
     
     
     /***
      * Helper method to change user email address
      */
     public function changeMemberEmail($listId, $currentEmail, $newEmail)
     {
         $merge_fields['EMAIL']     = $currentEmail;
         $merge_fields['NEW-EMAIl'] = $newEmail;
         $this->updateMember($listId, $currentEmail, null, null, null, $merge_fields);
     }
     
     
     /**
      * Helper method to  add a new member to a list
      * @param string $listId
      * @param string $email
      * @param string $firstname
      * @param string $lastname
      * @param string $status Options are subscribed, unsubscribed, cleaned, pending
      * @param array $merge_fields See Mailchimp list for available merge_tags
      * @param array $interests associative array of group interest the member can join.
      *              format should be [ interest_id => <bool> ]. eg [ '3df5y36' => true ]
      * @return \Guzzle\Promise
      */
     public function updateMember($listId, $email, $firstname=Null, $lastname=Null, 
                                  $status=Null, $merge_fields=Null, $interests=Null)
     {
         
         $memberId = $this->getMemberID($email);
         $endpoint = '/lists/' . $listId . '/members/' . $memberId;
         
         // Payload
         $data                  = [];
         $data['merge_fields']  = [];
         
         // Please note that Mailchimp API will not update the email address
         // To updated 
         $data['email_address'] = $email; 
         
         if ($status) {
             $data['status'] = $status;
         }
         
         if ($firstname) {
             $data['merge_fields']['FNAME'] = $firstname;
         }
         
         if ($lastname) {
             $data['merge_fields']['LNAME'] = $lastname;
         }
         
         if (is_array($merge_fields)) {
             $data['merge_fields'] =  array_merge( $data['merge_fields'], $merge_fields);
         }
     
         if ($interests) {
             $data['interests'] = $interests;
         }
         
         return  $this->patch($endpoint, $data);
 
     }
    
     
     /**
      * Helper method to get MailChimp list of groups
      * @param string $listId
      */
     public function getMailChimpGroupList($listId)
     {
        $endpoint = '/lists/' . $listId . '/interest-categories/';
        return  $this->get($endpoint);
     }
     
     /**
      * Helper method to get MailChimp group interest ids
      * @param string $listId
      */
     public function getMailChimpInterestList($listId, $groupId)
     {
         $endpoint = '/lists/' . $listId . '/interest-categories/' . $groupId . '/interests';
         return  $this->get($endpoint);
     }
     
}

<?php namespace DMA\Friends\API\Transformers;

use Model;

use DMA\Friends\Classes\API\BaseTransformer;

class UserMetadataTransformer extends BaseTransformer {
    

    public function getData($instance)
    {
        return [
            'birth_date'            => $instance->birth_date,      
            'email_optin'           => ($instance->email_optin)?true : false,    
            'gender'                => $instance->gender,
            'race'                  => $instance->race,
            'household_income'      => $instance->household_income,
            'household_size'        => $instance->household_size,
            'education'             => $instance->education,    
            'current_member'        => ($instance->current_member)?true : false,
            'current_member_number' => $instance->current_member_number,
            'facebook'              => $instance->facebook,
            'twitter'               => $instance->twitter,
            'instagram'             => $instance->instagram,
        ];
    }    
    
}

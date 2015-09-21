<?php namespace DMA\Friends\API\Transformers;

use Model;

use DMA\Friends\Classes\API\BaseTransformer;

class UserMetadataTransformer extends BaseTransformer {
    
    
    /**
     * @SWG\Definition(
     *    definition="user.metadata",
     *    description="User profile definition",
     *    required={"birth_date", "email_optin", "gender", "race", "household_income", "household_size", "education", 
     *              "current_member", "current_member_number", "facebook", "twitter", "instagram"},
     *    @SWG\Property(
     *         property="birth_date",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="email_optin",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         description="Get an update list from endpoint users/profile-options/gender",  
     *         property="gender",
     *         type="string",
     *         enum={"Male", "Female", "Non Binary/Other"}
     *    ),    
     *    @SWG\Property(
     *         description="Get an update list from endpoint users/profile-options/race",
     *         property="race",
     *         type="string",
     *         enum={"White", "Hispanic", "Black or African American", "American Indian or Alaska Native", "Asian", "Native Hawaiian or Other Pacific Islander", "Two or more races", "Other"}
     *    ),
     *    @SWG\Property(
     *         description="Get an update list from endpoint users/profile-options/household_income",  
     *         property="household_income",
     *         type="string",
     *         enum={"Less then $25,000", "$25,000 - $50,000", "$50,000 - $75,000", "$75,000 - $150,000", "$150,000 - $500,000", "$500,000 or more"}
     *    ),
     *    @SWG\Property(
     *         description="Get an update list from endpoint users/profile-options/education", 
     *         property="education",
     *         type="string",
     *         enum={"K-12", "High School/GED", "Some College", "Vocational or Trade School", "Bachelors Degree", "Masters Degree", "PhD"}
     *    ),
     *    @SWG\Property(
     *         property="current_member",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="current_member_number",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="facebook",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="twitter",
     *         type="string"
     *    ),
     *    @SWG\Property(
     *         property="instagram",
     *         type="string"
     *    )
     * )
     */

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getData()
     */
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

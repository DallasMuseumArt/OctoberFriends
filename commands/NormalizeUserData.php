<?php
namespace DMA\Friends\Commands;

use Log;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use RainLab\User\Models\User;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

/**
 * Normalize user data ( clean phone numbers, .. )
 *
 * @package DMA\Friends\Commands
 * @author Kristen Arnold, Carlos Arroyo
 */
class NormalizeUserData extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'friends:normalize-users';

    /**
     * @var integer counter of invalid phone numbers
     */
    private $invalidPhones = 0;
    
    /**
     * @var integer counter of valid phone numbers
     */
    private $validPhones = 0;

    /**
     * @var integer counter of user without phone
     */
    private $noPhone = 0;
    
    /**
     * Read and process incomming data from listenable channels
     * @return void
     */
    public function fire()
    {
        
        User::chunk(200, function($users)
        {
            foreach ($users as $user)
            {
                $this->normalizePhone($user);
              
            }
        });
        
        var_dump([
            'valid phones'   => $this->validPhones,
            'invalid phones' => $this->invalidPhones,
            'no phones'      => $this->noPhone,
        ]);

    }
    
    protected function normalizePhone($user) 
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($user->phone, "US");
            
            if ($phoneUtil->isValidNumber($numberProto)){
                $cleanPhone = $phoneUtil->format($numberProto, PhoneNumberFormat::E164);
                $this->validPhones++;
                
                //var_dump($cleanPhone);
            }else{
                $this->invalidPhones++;
            }
            
        } catch (\libphonenumber\NumberParseException $e) {
            $this->noPhone++;
        }
    }
}
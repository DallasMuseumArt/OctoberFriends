<?php
namespace DMA\Friends\Commands;

use DB;
use Log;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use RainLab\User\Models\User;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Carbon\Carbon;

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
     * @var array counter holder
     */    
    private $counters = [];
    
    /**
     * @var array to report data fail to be normalize. The key is the user_id
     */
    private $dataReport = [];
    
    /**
     * Read and process incomming data from listenable channels
     * @return void
     */
    public function fire()
    {
        
        // Long run queries fill memory pretty quickly due to a default
        // behavior of Laravel where all queries are log in memory. Disabling
        // this log fix the issue. See http://laravel.com/docs/4.2/database#query-logging
        DB::connection()->disableQueryLog();
        
        
        User::chunk(200, function($users)
        {

            foreach ($users as $user)
            {
                $this->increaseCounter('total_users');
                
                $data = $this->normalize($user);                

                foreach($data as $attr => $value) {
                    $user->{$attr} = $value;
                }
                
                $user->forceSave();
                
            }
        });
        
        
        var_dump($this->counters);
        var_dump($this->writeReport());

    }
    
    protected function normalize($user)
    {
        return [
           'phone' => $this->normalizePhone($user)
        ];

    }
    

    
    /**
     * Convert user phone field in to E.164 format
     * @param unknown $user
     * @return Ambigous <NULL, string, unknown, string>
     */
    protected function normalizePhone($user) 
    {
        $cleanPhone = null;
        $key = 'valid_phone';
        
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            // Get country code using configure timezone
            $tz = Carbon::now()->getTimezone();
            $country_code = array_get($tz->getLocation(), 'country_code', 'US');
            
            // Parse phone number
            $numberProto = $phoneUtil->parse($user->phone, $country_code);
            
            if ($phoneUtil->isValidNumber($numberProto)){
                $cleanPhone = $phoneUtil->format($numberProto, PhoneNumberFormat::E164);
                
            }else{
                               
                // Emails that contain numbers can be mistaken as a vanity number ( 800 GODMA )
                // so just for reporting purpose I check if the phone containts '@' is more likely it is an email 
                $isEmail = preg_match('/@/', $user->phone);
                $key = ($isEmail) ? 'not_a_phone' : 'invalid_phone'; 
                $this->addToReport($user, $key, $user->phone);
                
            }
            
        } catch (\libphonenumber\NumberParseException $e) {
           
            $key = 'phone_empty';
            if(!empty($user->phone)){
                $key = 'not_a_phone';
                $this->addToReport($user, $key, $user->phone);
            }
            
        }
        
        $this->increaseCounter($key);
        
        return $cleanPhone;
    }
    
    protected function increaseCounter($name)
    {
        if($counter = @$this->counters[$name]) {
            $counter++;
        } else {
           $counter = 1;  
        }
        $this->counters[$name] = $counter;
    }
    
    
    protected function writeReport()
    {
        $filePath = __DIR__ . '/../../../../uploads/public/data_normalize_report_' . date('dmYHis'). '.csv';
        // Export generated ids
        $file = fopen($filePath,"w");
        fputcsv($file, ['user_id', 'key', 'value']);
        foreach ($this->dataReport as $userId => $data) {

            foreach($data as $key => $value) {
                $row = [ $userId, $key,  $value ];
                fputcsv($file, $row);
            }
    
        }
    
        fclose($file);
    
        return $filePath;
    }
    
    protected function addToReport($user, $key, $value)
    {
        $this->dataReport[$user->getKey()][$key] = $value;
    }
}
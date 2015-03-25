<?php namespace DMA\Friends\API\Transformers;

use Carbon\Carbon;

trait DateTimeTransformerTrait {
    
    /**
     * Normalize time
     * @param string $time
     */
    protected function normalizeTime($time)
    {
        if(!is_null($time)){
            return Carbon::parse($time)->toTimeString();
        }
        return null;
    }
    
    /**
     * Covert Carbon datetime to ISO string format
     * @param Carbon\Carbon $carbonDate
     * @param string $bit
     * Part of the date time to convert. Options are date, time, null
     */
    protected function carbonToIso($carbonDate, $bit=null)
    {
        if(!is_null($carbonDate)){
            if (is_null($bit)){
                return $carbonDate->toIso8601String();
            }else if ($bit == 'date'){
                return $carbonDate->toDateString();
            }else if ($bit == 'time'){
                return $carbonDate->toTimeString();
            }
        }
    }
    
}

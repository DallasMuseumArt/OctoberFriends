<?php namespace DMA\Friends\Traits;

use DMA\Friends\Models\Rating;
use DMA\Friends\Models\UserRate;
use RainLab\User\Models\User;

trait Rateable
{
    
    private $minRating = 1;
    private $maxRating = 5;
    
    public $extendMorphOne = [
        'rating'  => ['DMA\Friends\Models\Rating', 'name' => 'object'],
    ];
    
    
    public function __call($name, $params = null)
    {
        // A bit of hackery here, by injecting a morphOne relation to 
        // current model
        $this->morphOne = array_merge($this->morphOne, $this->extendMorphOne);
        return parent::__call($name, $params);
    }
    
    /**
     * Helper function to get existing rating instance 
     * and if it doesn't exists creates a new one
     */
    protected function getRating(){

        // Find if a rating entry exists for the given object otherwise create one for this object
        if (!$rating = $this->rating()->getResults()){
            // Create a new Rating an created the relation 
            // to this object
            $rating = new Rating();
            $rating->save();
    
            // Add to relation
            $this->rating()->save($rating);
            // Reload is necessary otherwise we get a dirty instance
            // of Rating 
            $rating->reload();
        }       
        
        return $rating;
    } 
    
    /**
     * Add activity ratings
     */
    public function addRating(User $user, $rateValue, $comment=null)
    {
        if ($rateValue >= $this->minRating && $rateValue <= $this->maxRating) {
        
            // Get model's rating instance 
            $rating = $this->getRating();
            
            // Create a new user rate instace
            $rate = new UserRate();
            $rate->user = $user;
            $rate->rate = $rateValue;
            $rate->comment = $comment;
            
            $success = True;
            
            try {
                $rating->rates()->save($rate);
            }catch(\Illuminate\Database\QueryException $e){

                if($e->getCode() == 23000 ){
                    // If this exception is throw is because
                    // the user has already rate this object         
                    $success = False;               
                }else{
                    // Relaunch exception
                    throw $e;
                }
                
            }
            
            // Update rating cached values
            $rating->reCalculateRating();
            
            return [$success, $rating];
            
        } else {
            throw new \Exception("Rating value should be between [ $this->minRating - $this->maxRating ]");
        }

    }
    
    
    /**
    * Return and a dictionary of total 
    * ratings and average rating
    */
    public function getRatingStats()
    {

        // Get model's rating instance 
        $rating = $this->getRating();
        return [   
            'total'   => $rating->total,
            'average' => $rating->average
        ];
       
    }
    
    /**
     * Helper to return user rates and comments
     */
    public function getRates()
    {
        $ratings = $this->getRating();
        return $ratings->rates()->orderBy('created_at', 'desc');;
    }
    

    
}

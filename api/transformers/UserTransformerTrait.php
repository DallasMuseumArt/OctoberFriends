<?php namespace DMA\Friends\API\Transformers;

trait UserTransformerTrait {
    
    private $user = null;
    
    /**
     * 
     * @param \RainLab\User\Model\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
    
    /**
     * @return \RainLab\User\Model\User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    
    
}

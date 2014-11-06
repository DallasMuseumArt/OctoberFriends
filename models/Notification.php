<?php namespace DMA\Friends\Models;

use Event;
use Model;


/**
 * DMA Notification model
 * @package DMA\Friends\Models
 * @author Carlos Arroyo
 *
 */
class Notification extends Model{
    
    /**
     * @var string The database table used by the model.
     */
    protected $table = 'dma_friends_notification';
    
    /**
     * @var array Validation rules
     */
    public $rules = [];
            
    /**
     * @var array List of datetime attributes to convert to an instance of Carbon/DateTime objects.
     */    
    public $dates = ['created_at', 'updated_at', 'sent_at']; 
    
    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['Rainlab\User\Models\User']    
    ];
    
    /**
     * @var array Polyphormic relations
     */    
    public $morphTo = [
        'object',
    ];


    /**
	 * {@inheritDoc}
     */
    public function save(array $data = NULL, $sessionKey = NULL)
    {
        if(is_null($this->sent_at)){
            $this->sent_at = $this->freshTimestamp();
        }
        parent::save($data, $sessionKey);
    }
    
    /**
     * Helper to mark notifications as read
     */
    public function markAsRead()
    {
        $notification->is_read = true;
        $notification->save();
    }
    

    /**
     * Scope for selecting un-read notifications.
     * @param mixed $query
     */
    public function scopeUnread($query)
    {
    	return $query->where('is_read', '=', 0);
    }
    
    /**
     * Scope method for mark all selected messages 
     * as read.
     */
    public function scopeMarkAllAsRead()
    {
        self::unread()->update(['is_read' => true ]);
    }
    
}


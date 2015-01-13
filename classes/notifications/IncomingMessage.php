<?php namespace DMA\Friends\Classes\Notifications;

/**
 * Generic Incoming Messages
 * @author Carlos Arroyo
 */
class IncomingMessage
{

    protected $data = [];

    public function __construct($channelKey)
    {
        $this->data['channelKey'] = $channelKey;
    }

    /**
     * Set origin of the message
     * @param mixed $user
     * @param string $name
     * Optional parameter to give a friendly name to the user
     */
	public function from($user, $name='')
	{
	    $this->data['fromUser'] = $user;
	    $this->data['fromUserName'] = $name;
	}

	/**
	 * Return origin of the message
	 * @return mixed
	 */
	public function getFrom()
	{
		return @$this->data['fromUser'];
	}

	/**
	 * Set content of the message
	 * @param string message
	 */
	public function setContent($message)
	{
        $this->data['content'] = $message;
	}


	/**
	 * Returns raw incomming message content
	 * @return string
	 */
	public function getRawContent()
	{
        $content = @$this->data['content'];
	    return (!is_null($content))?$content:'';
	}


	/**
	 * Return a normalize content with no leading or tailing spaces and clean uniform whitespaces. 
	 * Useful to do lookups in the database with the content of the message.
	 * @return $string
	 */
	public function getContent()
	{
	    $content = $this->getRawContent();
	    
	    // Remove leading and ending white spaces
	    $content = trim($content);
	
	    // Normalize white spaces
	    $content = preg_replace('/[ ]{2,}/i', ' ', $content);
	    return $content;
	}
	
	
	/**
	 * Add array of match string found in the content.
	 * This is use manly por input regex expressions
	 * @param array $matches
	 */
	public function setMatches(array $matches)
	{
	    $this->data['matches'] = $matches;
	}


    /**
     * Return string matches in the content of the message.
     * This is use manly for input with regex expressions.
     * @return array
     */
	public function getMatches()
	{
	    return @$this->data['matches'];
	}

	/**
	 * Add extra data to the notification.
	 * This variables are accesable within the template.
	 * @param array $data
	 */
	public function addData(array $data){
	    $this->data = array_merge($this->data, $data);
	}

	/**
	 * @vre array
	 */
	public function getData()
	{
	    return $this->data;
	}


	/**
	 * Get channel key of the Channel which this message
	 * was recived
	 * @return string $channelCode
	 */
	public function getChannelKey()
	{
		return @$this->data['channelKey'];
	}
}

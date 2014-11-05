<?php namespace DMA\Friends\Classes\Notifications;

/**
 * Generic notification
 * @author Carlos Arroyo
 */
class NotificationMessage
{

    protected $data = [];
    private   $view;


    /**
     * Set user to send notification
     * @param RainLab\User\Models\User|string $user
     * @param string $name
     * Optional parameter to give a friendly name to the user
     */
	public function to($user, $name='')
	{
        $this->data['toUser'] = $user;
        $this->data['toUserName'] = $name;
	}

	/**
	 * @return RainLab\User\Models\User
	 */
    public function getTo()
    {
       return @$this->data['toUser'];
    }

    /**
     * Set user from the notification is been send
     * @param RainLab\User\Models\User|string $user
     * @param string $name
     * Optional parameter to give a friendly name to the user
     */
	public function from($user, $name='')
	{
	    $this->data['fromUser'] = $user;
	    $this->data['fromUserName'] = $name;
	}

	/**
	 * Return instance of the sender of the notification
	 * @return RainLab\User\Models\User
	 */
	public function getFrom()
	{
		return @$this->data['fromUser'];
	}

	/**
	 * Set subject to the notification
	 * @param string $subject
	 */
	public function subject($subject)
	{
        $this->data['subject'] = $subject;
	}

	/**
	 * Returns subject of the notification
	 * @return string
	 */
	public function getSubject()
	{
	    return @$this->data['subject'];
	}

	/**
	 * override view prefix of the notification
	 * @param string $view
	 */
	public function setView($view)
	{
		$this->view = $view;
	}

	/**
	 * Return view that this message will use to format the content
	 * @return string
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * Returns formated content using the configured view
	 * @return string
	 */
	public function getContent()
	{
	    // TODO : throw exceptions if view is null
	    $data = $this->getData();
	    return \View::make($this->view, $data);
	}

	/**
	 * A notification can be associated to and instace of a model.
	 * Useful to give some specific information in the notification.
	 * @param mixed|Model
     * Instance of any Laravel/OctoberCMS model
	 * @return string
	 */
	public function attachObject($object)
	{
	    // TODO : Think is is necesary verify that the is an instance of
	    // of a model
	    $this->data['attachObject'] = $object;
	}

	/**
	 * Return attached object to this notification
	 * @return mixed|Model
	 */
	public function getAttachObject()
	{
	    return @$this->data['attachObject'];
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
	 * @return array
     * Return an associative array with all data in this message
	 */
	public function getData()
	{
	    return $this->data;
	}
}

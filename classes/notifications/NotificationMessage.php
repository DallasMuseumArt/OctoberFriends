<?php namespace DMA\Friends\Classes\Notifications;

use App;
use File;
use View;
use DMA\Friends\Classes\Notifications\Templates\TemplateParser;
use DMA\Friends\Classes\Notifications\Exceptions\TemplateNotFoundException;

/**
 * Generic notification
 * @author Carlos Arroyo
 */
class NotificationMessage
{

    protected $data = [];
    private   $view;
    private   $viewSettings = [];
    private   $templateInfo = null;


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
	 * Set message to the notification
	 * @param string $subject
	 */
	public function message($message)
	{
		$this->data['message'] = $message;
	}
	
	/**
	 * Returns message of the notification
	 * @return string
	 */
	public function getMessage()
	{
		return @$this->data['message'];
	}
	
	/**
	 * override view prefix of the notification
	 * @param string $view
	 */
	public function setView($view)
	{
	    $this->templateInfo = null;
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
	 * Add settings to the view. This settings are use individually
	 * by each Channel.
	 * 
	 * @param string $view
	 */
	public function addViewSettings(array $settings)
	{
	    if (!is_null($settings) && is_array($settings)){
	       $this->viewSettings = $settings;
	    }
	}
	
	/**
	 * Return settings of the view use by this message
	 * @return string
	 */
	public function getViewSettings()
	{
	    // Merge pass settings by code with settings define in the 
	    // template defined settings
	    $templateInfo = $this->getTemplateInfo();
	    return array_merge(array_get($templateInfo, 'settings', []), $this->viewSettings);
	}
	
	
	/**
	 * Returns formated content using the configured view and selected template 
	 * within the view.
	 * 
	 * @param $template Template section to format the content. Could be main or alternative 
	 * @return string
	 */
	public function getContent($template = 'main')
	{
	    // TODO : throw exceptions if view is null
	    $data = $this->getData();
	    $templateInfo = $this->getTemplateInfo();
	    $template = array_get($templateInfo, $template, '');
	    
	    
	    // $twig = App::make('twig.string');
	    // return $twig->render($template, $data);
	    
	    $twig = App::make('twig.environment');
	    $twingTemplate = $twig->createTemplate($template);
	    return $twingTemplate->render($data);
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
	
	/**
	 * Get template info
	 * @return array
	 * Returns an associative array with the following keys: 'settings', 'template_1', 'template_2'
	 */
	protected function getTemplateInfo()
	{
	    if(is_null($this->templateInfo)){
	        try{
	            $view = $this->getView();
	            $this->templateInfo = $this->parseTemplate($view);
	        }catch(TemplateNotFoundException $e){
	            // Fallback to default view template
	            $view = substr($view, 0, strrpos($view, '.')) . '.simple';
	            $this->templateInfo = $this->parseTemplate($view);
	        }
	    }
	    return $this->templateInfo;
	} 

	/**
	 * Internal function to parse template
	 * 
	 * @param string $view
	 * @throws \DMA\Friends\Classes\Notifications\Exceptions\TemplateNotFoundException
	 * @return array
	 * Returns an associative array with the following keys: 'settings', 'template_1', 'template_2'
	 */
	private function parseTemplate($view)
	{
        try{
            $path = File::get(View::make($view)->getPath());
            return TemplateParser::parse($path);
        }catch(\InvalidArgumentException $e){
            throw new TemplateNotFoundException("Notification view [ $view ] not found");
        }
	}
	
}

<?php namespace DMA\Friends\Classes\Notifications;

use Closure;
use DMA\Friends\Models\Settings;
use DMA\Friends\Classes\Notifications\Channels\Listenable;


/**
 * DMA notification manager system
 * @author Carlos Arroyo
 */
class ChannelManager
{


    /**
     * Dictionary of register channel input validators
     * @var array
     */
    private $inputValidators = [];

    /**
     * Dictionary of register channels
     * @var array
     */
    private $channels = [];



    /**
     * Register inputs
     * @param array $inputs classnames of input validators to be register
     */
    public function registerInputValidators(array $validators)
    {
    	foreach($validators as $class){
    		$this->inputValidators[$class::getCode()] = $class;
    	}
    }


    /**
     * Register channels
     * @param array $channels classnames of channels to be register
     */
    public function registerChannels(array $channels)
    {
       foreach($channels as $class => $details){

           $ch = \App::make($class);

           // Run channel configurations
           $ch->configChannel();
           $ch->info = $details;
           $this->channels[$ch->getKey()] = $ch;
       }

    }


    /**
     * Return an array of all register notification channels
     * @param $onlyListenable Return only channels that implement read method
     * @return array
     */
    public function getRegisterChannels($onlyListenable=false)
    {
        if(!$onlyListenable){
            return $this->channels;
        }else{
            $channels = [];
            foreach ($this->channels as $key => $ch){
                if ($ch instanceof Listenable){
                    $channels[$key] = $this->channels[$key];
                }
            }
            return $channels;
        }
    }

    /**
     * Return an instance of the given key channel
     * @param string $channelKey
     * @return DMA\Friends\Classes\Notifications\Channels:
     */
    public function getChannelInstance($channelKey){
        return $this->channels[$channelKey];
    }

    /**
     * Return settings fields that should be add to
     * Friends settings.
     * @return array
     */
    public function getChannelSettingFields(){
    	$extra = [];
    	foreach($this->getRegisterChannels() as $ch){
    		$fields = $ch->settingFields();
    		if(is_array($fields)){
    			foreach($fields as $key => $opts){
    				$tab = $ch->info['name'] . ' settings';
    				$opts['tab'] = $tab;
    				$extra[$key] = $opts;
    			}
    		}
    	}
    	return $extra;
    }    

    /**
     * Pass a notification message to the Clouser before send through each channel
     * @param Closure $callback
     * @param \DMA\Friends\Classes\Notifications\NotificationMessage $notification
     * @throws \InvalidArgumentException
     * @return mixed
     */
    protected function callNotificationBuilder($callback, $notification)
    {
        if($callback instanceof Closure){
            return call_user_func($callback, $notification);
        }

        throw new \InvalidArgumentException('Callback must be a Closure');
    }

    /**
     * Send a notification using all register channels if $sendBy is not given.
     * @param string $notificationName
     * @param Clouser $callback
     * @param array $data
     * @param array $sentBy
     * @throws \Exception
     * @return \DMA\Friends\Classes\Notifications\NotificationMessage
     */
    public function send($notificationName, $callback, array $sentBy=[])
    {

        $notification = new NotificationMessage;

        // Call callback to allow configure notification
        $this->callNotificationBuilder($callback, $notification);

        // Channels
        // Send notification to register channel
        $channels = [];
        if (count($sentBy) > 0){
            foreach($sentBy as $key){
                if($ch = @$this->channels[$key]){
                    $channels[$key] =$ch;
                }else{
                    throw new \Exception('Invalid channel ' . $key);
                }
            }
        }else{
            $channels = $this->channels;
        }

        $activeChannels = Settings::get('active_notification_channels', []);

        if (!empty($activeChannels)){
            // Filter out channels that are not enable in the settings
            $channels = array_filter($channels, function($ch) use($activeChannels){
                if(in_array($ch->getKey(), $activeChannels)){
                    return true;
                }
                return false;
            });
        }else{
            $channels = [];
        }

        foreach($channels as $channel){
            try{
                // Update view template for each channel
                $view = sprintf('dma.friends::notifications.%s.%s', $channel->getKey(), $notificationName);
                $notification->setView($view);

                // Send notification
                $channel->send($notification);
            }catch(\Exception $e){
                \Log::error($e);
                //throw $e;
            }
        }

        return $notification;
    }

    /**
     * Implements internal polling for channels that are listenable
     */
    public function readChannels(){

        $activeChannels = Settings::get('active_listenable_channels', []);
        $channels = $this->channels;
        // Filter out channels that are not enable in the settings
        $channels = array_filter($channels, function($ch) use($activeChannels){
        	if(in_array($ch->getKey(), $activeChannels)){
        		return true;
        	}
        	return false;
        });

        foreach($channels as $ch){

        	try{
                if($ch instanceof Listenable){
                    // tell channel to check for new incoming data
                    $data = $ch->read();
                    if (count($data) > 0){
                        $channelCode = $ch->getKey();
                        $event = "dma.channel.$channelCode.incoming.data";
                        \Event::fire($event, [$data]);
                    }
                }

        	}catch(\Exception $e){
        		\Log::error($e);
        	}
        }
    }

    private function is_assoc($array)
    {
    	return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    public function listen($arguments, Closure $callback)
    {
        $bindChannels    = [];
        $inputValidators = [];

        if(is_string($arguments)){
            // 1. if $arguments is string maybe is a single channelKey
            $bindChannels[] = $arguments;

        }elseif (is_array($arguments) && !$this->is_assoc($arguments)) {
            // 2. if $arguments is an array of only string if so
            // is a list of channels
            $bindChannels = $arguments;

        }elseif (is_array($arguments) && $this->is_assoc($arguments)){
            // 3. if $arguments is an associative array first position
            // could be and array of channelKeys or a single channelKey
            $bindChannels = array_shift($arguments);
            if (!is_array($bindChannels)){
                $bindChannels = [$bindChannels];
            }

            // 4. find input validators requested
            foreach($arguments as $inputCode => $args){
               if ($class = @$this->inputValidators[$inputCode]){
                   // Create a new instance of the input validator
                   $class = new \ReflectionClass($class);
                   $args = (is_array($args)) ? $args : [$args];
                   $inputValidators[$inputCode] = $class->newInstanceArgs($args);

                }
            }

        }else{
            throw new Exception(sprintf('Listen arguments are invalid:  %s ', $arguments) );
        }



        // Bind channel listener event
        foreach($bindChannels as $channelKey){
            // Start listen incoming messages for each active channel
            $channelKey = strtolower($channelKey);
            $event = "dma.channel.$channelKey.incoming.data";

            \Event::listen($event, function(array $messages) use ($callback, $inputValidators){
                foreach($messages as $message){
                    $valid = true;
                    $content = $message->getContent();

                    //Apply input validators
                    $matches = [];
                    foreach($inputValidators as $code => $input){
                        $valid = $input->valid($content);
                        if (!$valid){
                            break;
                        }else{
                            $matches[$code] = $input->getMatches($content);
                        }
                    }

                    // Delegate event if the message is valid
                    if ($valid){
                        // Add matches
                        $message->setMatches($matches);
                        $callback($message);
                    }
                }

            });
        }

    }


}

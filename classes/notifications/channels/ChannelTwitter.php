<?php namespace DMA\Friends\Classes\Notifications\Channels;

use TwitterAPIExchange;
use DMA\Friends\Models\Settings;
use DMA\Friends\Classes\Notifications\Channels\Channel;
use DMA\Friends\Classes\Notifications\Channels\Listenable;
use DMA\Friends\Classes\Notifications\NotificationMessage;
use DMA\Friends\Classes\Notifications\IncomingMessage;

/**
 * Channel for sending Directed Messages notifications via Twitter.
 * This channel is experimental. 
 * @author Carlos Arroyo
 *
 */
class ChannelTwitter implements Channel, Listenable
{
    private $client;
    private $settings;

	public static function getKey()
	{
		return 'twitter';
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getDetails()
	 */
	public function getDetails()
	{
	    return [
	            'name'           => 'Twitter',
	            'description'    => 'Send notifications via Twitter. (Experimental)'
	    ];
	}	
	
	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::configChannel()
	 */
	public function configChannel()
	{
	    $this->settings = array(
    		'oauth_access_token'          => Settings::get('twitter_access_token'),
    		'oauth_access_token_secret'   => Settings::get('twitter_access_token_secret'),
    		'consumer_key'                => Settings::get('twitter_consumer_key'),
    		'consumer_secret'             => Settings::get('twitter_consumer_key_secret'),
	    );

	}

	/**
	 * Get an instance of a Twitter client
	 * @return \DMA\Friends\Classes\Notifications\Channels\TwitterAPIExchange
	 */
	private function getClient()
	{

	    if(is_null($this->client)){
	        $this->client = new TwitterAPIExchange($this->settings);
	    }
	    return $this->client;
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::settingFields()
	 */
	public function settingFields()
	{
		return [
            'twitter_access_token' => [
                'label' => 'Access token',
                'span'  => 'auto'
            ],
            'twitter_access_token_secret' => [
                'label' => 'Access token secret',
                'span'  => 'auto'
            ],
            'twitter_consumer_key' => [
                'label' => 'Consumer key',
                'span'  => 'auto'
            ],
            'twitter_consumer_key_secret' => [
                'label' => 'Consumer key secret',
                'span'  => 'auto'
            ],
            'twitter_search_hashtag' => [
                'label' => 'Search tweets with hashtag',
                'span'  => 'auto'
            ],
            'twitter_since_id' => [
	            'label' => 'Current Since ID',
	            'span'  => 'auto',
	            'description' => 'This field is for tracking and debugin purposes'
		    ],
            'twitter_max_id' => [
                'label' => 'Current Max ID',
	            'span'  => 'auto',
	            'description' => 'This field is for tracking and debugin purposes'
		    ]

		];

	}

	/**
	 * Use NotificationMessage setData method to add user twitterHandle
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::send()
	 */
	public function send(NotificationMessage $message)
	{
	    // TODO : add validation to control the size of the message.
	    $data          = $message->getData();
	    $txt           = $message->getContent();
	    $screen_name   = $message->getTo();

	    $url = 'https://api.twitter.com/1.1/direct_messages/new.json';
	    $postfields = [
	       'screen_name' => $screen_name,
	       'text' => $txt
	    ];
	    $requestMethod = 'POST';

	    $client = $this->getClient();
	    $response = $client->setPostfields($postfields)
                    	    ->buildOauth($url, $requestMethod)
                    	    ->performRequest();


	    echo($response);
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Listenable::readChannel()
	 */
	public function read()
	{
        //https://api.twitter.com/1.1/search/tweets.json
	    $url = 'https://api.twitter.com/1.1/search/tweets.json';
	    $requestMethod = 'GET';

	    $getfield = '?q=' . Settings::get('twitter_search_hashtag');
	    $maxId = Null;
	    If( $sinceId = Settings::set('twitter_since_id') &&
	        $maxId   = Settings::get('twitter_max_id') ){
	        $getfield = $getfield . '&since_id=' . $sinceId;
	        $getfield = $getfield . '&max_id=' . $maxId;
	    }

	    $client = $this->getClient();
	    $response = $client->setGetfield($getfield)
    	    ->buildOauth($url, $requestMethod)
    	    ->performRequest();

	    // Convert results to php objects
	    $result = json_decode($response);


	    // Convert tweets into messages
	    $tweetIds = [];
	    $messages = [];
	    foreach($result->statuses as $tweet){

	        $tweetId = $tweet->id;
	        // Because max_id is also includes the tweet that we have already
	        // processed if max_id is present we remove this tweet from the messages.
	        if ($tweetId != $maxId){
    	       $tweetIds[] = $tweet->id;

    	       $msg = new IncomingMessage($this->getKey());
    	       $msg->from($tweet->user->id, $tweet->user->name);
    	       $msg->setContent($tweet->text);

    	       $messages[]=$msg;
	        }

	    }

	    // Get max_id and since_id
	    // Further information on this Twitter parameters go to
	    // https://dev.twitter.com/rest/public/timelines
	    Settings::set('twitter_max_id',   min($tweetIds));
	    Settings::set('twitter_since_id', max($tweetIds));

	    return $messages;

	}




}

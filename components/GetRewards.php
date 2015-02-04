<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use DMA\Friends\Classes\Notifications\NotificationMessage;
use DMA\Friends\Models\Reward;
use DMA\Friends\Models\Bookmark;
use DMA\Friends\Classes\RewardManager;
use Auth;
use View;
use Session;
use Flash;
use Postman;

class GetRewards extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Get Rewards',
            'description' => 'Provide a listing of available rewards'
        ];
    }

    public function defineProperties()
    {
        return [
            'limit' => [
                'title'     => 'Limit',
                'default'   => 8,
            ],
        ];
    }

    public function onRun()
    {

        $results = $this->getResults();

        $this->page['rewards'] = $results['rewards'];
        $this->page['links']   = $results['links'];

    }

    public function onUpdate()
    {
        $filter = post('filter');
        $results = $this->getResults($filter);
        
        $this->page['rewards']  = $results['rewards'];
        $this->page['links']    = $results['links'];

        return [
            '#rewards' => $this->renderPartial('@default'),
        ];
    }

    public function getResults($filter = null)
    {
        $renderedRewards = [];

        $rewards = Reward::isActive();
        $user = Auth::getUser();

        switch($filter) {
            case 'qty':
                $rewards->where('inventory', '>', 0);
                break;
            case 'time':
                $rewards->whereNotNull('date_begin');
                $rewards->whereNotNull('date_end');
                break;
            case 'bookmarked':
                $user = \Auth::getUser();
                $rewards->whereHas('bookmarks', function($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                });
                break;
            case 'all':
            default:
                $rewards->whereNull('inventory');
                $rewards->orWhere('inventory', '>', 0);
                break;
        }

        $rewards = $rewards->orderBy('points')->paginate($this->property('limit'));

        foreach($rewards as $reward) {
            $renderedRewards[] = [
                'rendered' => View::make('dma.friends::rewardPreview', ['reward' => $reward, 'user' => $user])->render(),
                'id' => $reward->id,
            ];
        }

        return [
            'links' => $rewards->links(),
            'rewards' => $renderedRewards,
        ];
    }

    public function onRedeemModal()
    {
        $reward = $this->loadReward();
        return RewardManager::render($this, $reward);
    }

    public function onRedeem()
    {
        $id = post('id');
        $user = Auth::getUser();
        RewardManager::redeem($id, $user);

        // Send Flash and kiosk notification
        Postman::send('simple', function(NotificationMessage $notification) use ($user){
        
            // Set user in the notification
            $notification->to($user, $user->name);
             
            // Send code and activity just in case we want to use in the template
            $notification->addData([]);
        
            // Determine the content of the message and type of message
            $message = Session::pull('rewardMessage');
            $type    = ($message) ? 'info' : 'error'; // Only for flash messages
            
            if($type == 'error'){
                $message = Session::pull('rewardError');
            }
                        
            // Set type of flash 
            $notification->addViewSettings(['type' => $type]);
                     
            $notification->message($message);
             
        }, ['flash', 'kiosk']);
        
        
        return [
            '.modal-dialog'     => "<script type='text/javascript'>$('button.close').click();</script>",
            '#flashMessages'    => $this->renderPartial('@flashMessages'),
            'span.points'       => number_format($user->points),
        ];
    }

    public function onBookmarkAdd()
    {
        $reward = $this->loadReward();
        $user = Auth::getUser();

        Bookmark::saveBookmark($user, $reward);
        return [
            '.bookmark' => View::make('dma.friends::onBookmarkRemove', ['id' => $reward->id])->render(),
        ];
    }

    public function onBookmarkRemove()
    {
        $reward = $this->loadReward();
        $user = Auth::getUser();

        Bookmark::removeBookmark($user, $reward);
        return [
            '.bookmark' => View::make('dma.friends::onBookmarkAdd', ['id' => $reward->id])->render(),
        ];
    }

    public function loadReward()
    {
        $id = post('id');
        return Reward::find($id);
    }

}
<?php namespace DMA\Friends\Models;

use Model;
use Auth;
use DMA\Friends\Models\Bookmark;
use Smirik\PHPDateTimeAgo\DateTimeAgo as TimeAgo;
use System\Models\MailTemplate;
use Backend\Models\UserGroup;

/**
 * Reward Model
 */
class Reward extends Model
{

    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_rewards';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['touch'];

    protected $dates = ['date_begin', 'date_end'];

    public $rules = [ 
        'title' => 'required',
    ];  

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'users' => ['Rainlab\User\Models\User', 'dma_friends_reward_user'],
    ];

    public $attachOne = [
        'image' => ['System\Models\File']
    ];

    public $morphMany = [ 
        'activityLogs'  => ['DMA\Friends\Models\ActivityLog', 'name' => 'object'],
        'bookmarks'     => ['DMA\Friends\Models\Bookmark', 'name' => 'object'],
    ];

    public function scopefindWordpress($query, $id)
    {   
        return $query->where('wordpress_id', $id);
    }  

    public function scopeIsActive($query)
    {
        return $query->where('is_published', '=', 1)
            ->where('is_archived', '=', 0)
            ->where('is_hidden', '=', 0);
    }

    public function getPointsFormattedAttribute()
    {
        return number_format($this->points);
    }

    /**
     * Mutator function to return the pivot timestamp as time ago
     * @return string The time since the badge was earned
     */
    public function getTimeAgoAttribute($value)
    {
        if (!isset($this->pivot->created_at)) return null;

        $timeAgo = new TimeAgo;
        return $timeAgo->get($this->pivot->created_at);
    }

    public function getIsBookmarkedAttribute()
    {
        $user = Auth::getUser();
        return (boolean)Bookmark::findBookmark($user, $this);
    }

    public function getEmailTemplateOptions()
    {
        MailTemplate::syncAll();
        $mailTemplate = new MailTemplate;

        \Debugbar::info($mailTemplate->listRegisteredTemplates());
        $templates = $mailTemplate->listRegisteredTemplates();

        $options[] = 'No Template Defined';

        foreach($templates as $key => $template) {
            $options[$key] = '<strong>' . $key . '</strong> - ' . $template;
        }

        return $options;

    }

    public function getAdminEmailTemplateOptions()
    {
        return $this->getEmailTemplateOptions();
    }

    public function getAdminEmailGroupOptions()
    {
        $options[] = 'None';
        $groups = UserGroup::all();
        foreach($groups as $group) {
            $options[$group->id] = $group->name;
        }

        return $options;
    }

}

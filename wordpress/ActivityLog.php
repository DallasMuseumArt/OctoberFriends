<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Models\ActivityLog as OctoberActivityLog;
use DMA\Friends\Models\Activity as OctoberActivity;
use DMA\Friends\Models\Badge as OctoberBadge;
use DMA\Friends\Models\Location as OctoberLocation;
use DMA\Friends\Models\Reward as OctoberReward;
use DMA\Friends\Models\Step as OctoberStep;
use Illuminate\Support\Facades\DB;

class ActivityLog extends Post
{

    public function __construct()
    {
        $this->model = new OctoberActivityLog;
        parent::__construct();
    }

    /** 
     * Import user accounts from wordpress
     *
     * @param int $limit
     * The amount of records to import at one time
     */
    public function import($limit = 0)
    {
        $count  = 0;
        $table  = $this->model->table;
        $id     = (int)DB::table($table)->max('id');

        $wordpressLogs = $this->db
            ->table('wp_badgeos_logs')
            ->where('id', '>', $id)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        // Use dummy model to get action types
        $l = new $this->model;

        foreach ($wordpressLogs as $wlog) {

            if (!in_array($wlog->action, $l->actionTypes)) continue;

            $object = false;

            $log                = new $this->model;
            $log->id            = $wlog->id;
            $log->user_id       = $wlog->user_id;
            $log->action        = $wlog->action;
            $log->message       = $wlog->message;
            $log->points_earned = $wlog->points_earned;
            $log->total_points  = $wlog->total_points;
            $log->timestamp     = $wlog->timestamp;
            $log->timezone      = $wlog->timezone;
    
            if ($wlog->action == 'artwork') {
                $log->artwork_id = $wlog->object_id;
                $object = OctoberActivity::where('activity_type', '=', 'LikeWorkOfArt')->first();
            } else {

                // Get the wordpress post type
                $post_type = $this->db
                    ->table('wp_posts')
                    ->select('post_type')
                    ->where('ID', $wlog->object_id)
                    ->first();

                if (isset($post_type->post_type)) {
                    // Convert the post type to a usable object model
                    switch($post_type->post_type) {
                        case 'activity':
                            $object = OctoberActivity::findWordpress($wlog->object_id);
                            break;
                        case 'badge':
                            $object = OctoberBadge::findWordpress($wlog->object_id);
                            break;
                        case 'badgeos-rewards':
                            $object = OctoberReward::findWordpress($wlog->object_id);
                            break;
                        case 'dma-location':
                            $object = OctoberLocation::findWordpress($wlog->object_id);
                            break;
                        case 'step':
                            $object = OctoberStep::findWordpress($wlog->object_id);
                            break;
                        default:
                            continue;
                    }
                } else {
                    continue;
                }
 
            }   

            try {
                if ($log->save()) {

                    // If the log is related to an object, save that relation
                    if ($object) {
                        $object = $object->first();
                        // Ugly hack to get sync working for now
                        if (get_class($object) == 'DMA\Friends\Wordpress\ActivityLog') continue;
                        $object->activityLogs()->save($log);
                    }
                    $count++;
                } 
            } catch(Exception $e) {
                echo "Failed to import log entry id: " . $log->id . "\n";
                echo $e->getMessage() . "\n";
            }
        }  

        return $count;
    }

}

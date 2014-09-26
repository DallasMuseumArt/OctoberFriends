<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Models\ActivityLog as OctoberActivityLog;
use Illuminate\Support\Facades\DB;

class ActivityLog extends Post
{

    public function __construct()
    {
        $this->model = new OctoberActivityLog;
        parent::__construct();
    }

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

            $log                = new $this->model;
            $log->id            = $wlog->id;
            $log->site_id       = $wlog->site_id;
            $log->user_id       = $wlog->user_id;
            $log->action        = $wlog->action;
            $log->message       = $wlog->message;
            $log->points_earned = $wlog->points_earned;
            $log->total_points  = $wlog->total_points;
            $log->timestamp     = $wlog->timestamp;
            $log->timezone      = $wlog->timezone;
    
            if ($wlog->action == 'artwork') {
                $log->artwork_id = $wlog->object_id;
            } else {
                $log->object_id = $wlog->object_id;

                $type = $this->db
                    ->table('wp_posts')
                    ->select('post_type')
                    ->where('ID', $log->object_id)
                    ->first();

                if ($type) {
                    $log->object_type = $type->post_type;
                }   
            }   

            if ($log->save()) {
                $count++;
            } 
        }  

        return $count;
    }

}

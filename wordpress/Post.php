<?php

namespace DMA\Friends\Wordpress;

use Illuminate\Support\Facades\DB;
use DMA\Friends\Models\Activity;

/**
 * Abstract class for importing/exporting wordpress posts to and from laravel models
 */

class Post {

    /**
     * array The schema defines the mapping between wordpress and
     * laravel.  With the key being the wordpress key, and the value
     * being the Laravel key
     */
    public $schema = [
        // Base post table fields
        'ID'                                            => 'wordpress_id',
        'post_date'                                     => 'created_at',
        'post_content'                                  => 'description',
        'post_title'                                    => 'title',
        'post_excerpt'                                  => 'excerpt',
        'post_status'                                   => 'is_published',
        'post_modified'                                 => 'updated_at',
        // Post metadata fields

        /* Achievement Type */
        // This may need to be implemented later, but it doesnt look like it 
        // is a useful content type for anything
        '_badgeos_plural_name'                          => null,
        '_badgeos_singular_name'                        => null,

        /* Activities */
        '_badgeos_activity_lockout'                     => 'activity_lockout',
        '_badgeos_time_restriction'                     => 'time_restriction',
        '_badgeos_time_restriction_days'                => 'time_restriction_data',
        '_badgeos_time_restriction_hour_begin'          => 'time_restriction_data',
        '_badgeos_time_restriction_hour_end'            => 'time_restriction_data',
        '_dma_accession_id'                             => 'activity_code',

        /* Badges */
        '_badgeos_active'                               => 'is_archived',
        '_badgeos_congratulations_text'                 => 'congratulations_text',
        '_badgeos_earned_by'                            => 'earned_by',
        '_badgeos_hidden'                               => 'is_hidden',
        '_badgeos_maximum_earnings'                     => 'maximum_earnings',
        '_badgeos_maximum_time'                         => 'maximium_time',
        '_badgeos_special'                              => 'special',
        '_badgeos_time_between_steps_max'               => 'time_between_steps_max',
        '_badgeos_time_between_steps_min'               => 'time_between_steps_min',
        // Unused fields
        '_badgestack_badge_unlock_options'              => null,
        '_badgestack_point_value'                       => null,

        // Shared by Activities and Badges
        '_badgeos_points'                               => 'points',
        '_badgeos_time_restriction_date_begin'          => 'date_begin',
        '_badgeos_time_restriction_date_end'            => 'date_end',

        /* Location */
        '_dma_location_printer_ip'                      => 'printer_membership',
        '_dma_location_printer_reward'                  => 'printer_reward',

        /* Rewards */
        '_dma_reward_barcode'                           => 'barcode',
        '_dma_reward_days_valid'                        => 'days_valid',
        '_dma_reward_enable_email'                      => 'enable_email',
        '_dma_reward_end_date'                          => 'date_end',
        '_dma_reward_fine_print'                        => 'fine_print',
        '_dma_reward_inventory'                         => 'inventory',
        '_dma_reward_points'                            => 'points',
        '_dma_reward_redemption_email'                  => 'redemption_email',
        '_dma_reward_start_date'                        => 'date_begin',

        /* Step */
        '_badgeos_achievement_type'                     => 'achievement_type',
        '_badgeos_count'                                => 'count',
        '_badgeos_trigger_type'                         => 'trigger_type',
        '_badgestack_step_unlock_options'               => 'unlock_options',
        '_badgestack_trigger_badgestack_unlock_badge'   => 'trigger_unlock_badge',

        /* none */
        '_badgeos_awarded_points'                       => null,
        '_badgeos_log_achievement_id'                   => null,
        '_badgeos_time_restriction_limit_checkin'       => null,
        '_badgeos_total_user_points'                    => null,

    ];

    /**
     * Handle special settings fields for restricted hours and dates
     */
    protected $restrictedTimes = [
        '_badgeos_time_restriction_days',
        '_badgeos_time_restriction_hour_begin',
        '_badgeos_time_restriction_hour_end'
    ];

    /**
     * Exclude fields from being imported
     */
    protected $excludeFields = [];
    
    /**
     * Post type in wordpress
     */
    public $postType = null;

    /**
     * A laravel model to represent the data
     */
    public $model = null;

    public function __construct()
    {
        $this->db = DB::connection('friends_wordpress');
    }

    /**
     * Imports a wordpress post as an elequent model
     *
     * @param int $limit
     * The number of records to import at once
     *
     * @return $count
     * The number of records that have been imported
     */ 
    public function import($limit = 0)
    {
        $count  = 0;
        $id     = (int)DB::table($this->model->table)->max('wordpress_id');

        $posts = $this->db->table('wp_posts')
            ->where('ID', '>', $id)
            ->where('post_type', $this->postType)
            ->get();

        foreach($posts as $post) {
            $this->mergeMetadata($post);

            $model = new $this->model;

            foreach((array)$post as $key => $val) {
                if (in_array($key, $this->excludeFields)) continue;

                if (isset($this->schema[$key])) {
                    $val = $this->convertValues($this->schema[$key], $val);

                    if (!in_array($this->schema[$key], $this->restrictedTimes)) {
                        $model->{$this->schema[$key]} = $val;
                    }
                } elseif ($key == 'time_restriction_data') {
                    $model->time_restriction_data = $this->convertTimeRestrictedData($post->{$key});
                }

            }

            if ($model->forceSave()) {
                $count++;
            }
        }

        return $count;

    }

    /**
     * Merge wordpress metadata with the original post
     *
     * @param $post
     * A wordpress post object
     */
    public function mergeMetadata(&$post)
    {
        $metadata = $this->db->table('wp_postmeta')
            ->where('post_id', $post->ID)
            ->get();

        foreach($metadata as $meta) {
            if (in_array($meta->meta_key, $this->restrictedTimes)) {
                // For now we will save as a meta:key combo.  The data will be sorted later in processing
                $post->time_restriction_data[] = $meta->meta_key . '|' . $meta->meta_value;
            } else {
                $post->{$meta->meta_key} = $meta->meta_value;
            }
        }

    }

    /**
     * Converts special fields to the appropriate value type
     * 
     * @param $key
     * @param $val
     *
     * @return $val
     * The converted value 
     */
    protected function convertValues($key, $val)
    {
        // Fields that start with is_ are booleans
        // And need to be reformated to a real boolean field
        if (substr($key, 0, 3) == 'is_') {
            $val = $this->realBoolean($val);
        }   

        switch($key) {
            case 'time_restriction':
                $val = $this->realTimeRestriction($val);
                break;
            case 'date_begin':
            case 'date_end':
                if (!is_numeric($val)) {
                    $val = strtotime($val);
                }
                $val = $this->epochToTimestamp($val);
                break;
        }

        return $val;
    }

    /**
     * convert wordpress' infinite possibility of boolean values into a real boolean value
     *
     * @param $val
     *
     * @return $val
     */
    protected function realBoolean($val) 
    {
        $val = strtolower($val);

        switch($val) {
            case 'yes':
            case 'hidden':
            case 'publish':
                $val = true;
                break;
            case 'no':
            case 'show':
            case 'draft':
                $val = false;
                break;
        }

        return $val;
    }

    /**
     * Convert a time restriction into the appropriate constant
     *
     * @param $val
     *
     * @return $val
     */
    protected function realTimeRestriction($val)
    {
        if ($val == 'hours') {
            $val = Activity::TIME_RESTRICT_HOURS;
        } else if ($val == 'days') {
            $val = Activity::TIME_RESTRICT_DAYS;
        } else {
            $val = Activity::TIME_RESTRICT_NONE;
        }

        return $val;
    }

    /**
     * Convert an epoch value to the appropriate timestamp
     *
     * @param $val
     *
     * @return $timestamp
     */
    public function epochToTimestamp($val)
    {
        return date('Y-m-d H:i:s', $val);
    }

    /**
     * Converts "time restricted" data settings for activities
     * into a serialized field of configuration settings
     *
     * @param array $data
     *
     * @return $data
     * A serialized version of the processed data
     */
    protected function convertTimeRestrictedData($data)
    {
        $d = [
            'start_time'    => null,
            'end_time'      => null,
            'days'          => [
                1   => false,
                2   => false,
                3   => false,
                4   => false,
                5   => false,
                6   => false,
                7   => false,
            ]
        ];

        foreach($data as $val) {
            list($k, $v) = explode('|', $val);
            
            switch($k) {
                case '_badgeos_time_restriction_hour_begin':
                    $d['start_time'] = $v;
                    break; 
                case '_badgeos_time_restriction_hour_end':
                    $d['end_time'] = $v;
                    break; 
                case '_badgeos_time_restriction_days':
                    $d['days'][date('N', strtotime($v))] = true;
                    break;
            }    
        }

        return serialize($d);

    }
}

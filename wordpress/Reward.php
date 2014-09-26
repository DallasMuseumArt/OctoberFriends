<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Models\Reward as OctoberReward;

class Reward extends Post
{
    /** 
     * Override the default post type
     */
    public $postType = 'badgeos-rewards';

    public function __construct()
    {
        $this->model = new OctoberReward;
        parent::__construct();
    }

}

<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Models\Reward as OctoberReward;

class Reward extends Post
{
    public $postType = 'badgeos-rewards';

    public function __construct()
    {
        $this->model = new OctoberReward;
        parent::__construct();
    }

}

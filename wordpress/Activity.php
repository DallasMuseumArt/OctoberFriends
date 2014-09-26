<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Models\Activity as OctoberActivity;

class Activity extends Post
{
    /** 
     * Override the default post type
     */
    public $postType = 'activity';

    public function __construct()
    {
        $this->model = new OctoberActivity;
        parent::__construct();
    }

}

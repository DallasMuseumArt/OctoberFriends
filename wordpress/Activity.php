<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Models\Activity as OctoberActivity;

class Activity extends Post
{
    public $postType = 'activity';

    public function __construct()
    {
        $this->model = new OctoberActivity;
        parent::__construct();
    }

}

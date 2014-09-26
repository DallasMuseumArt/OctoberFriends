<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Models\Badge as OctoberBadge;

class Badge extends Post
{
    /**
     * Override default post type
     */
    public $postType    = 'badge';

    public function __construct()
    {
        $this->model = new OctoberBadge;
        parent::__construct();
    }

}

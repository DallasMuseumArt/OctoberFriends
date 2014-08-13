<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Models\Location as OctoberLocation;

class Location extends Post
{
    public $postType  = 'dma-location';

    protected $excludeFields = [
        'post_excerpt',
        'is_published',
        'post_status',
    ];

    public function __construct()
    {
        $this->model = new OctoberLocation;
        parent::__construct();
    }

}

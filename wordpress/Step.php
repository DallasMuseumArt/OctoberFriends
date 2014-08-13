<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Models\Step as OctoberStep;

class Step extends Post
{
    public $postType = 'step';

    protected $excludeFields = [ 
        'post_excerpt',
        'post_content',
        'is_published',
        'post_status',
    ];  

    public function __construct()
    {
        $this->model = new OctoberStep;
        parent::__construct();
    }

}

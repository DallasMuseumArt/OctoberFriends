<?php namespace DMA\Friends\API\Resources;

use DMA\Friends\Classes\API\BaseResource;

class ActivityResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Activity';

    protected $transformer  = '\DMA\Friends\API\Transformers\ActivityTransformer';


    public function __construct()
    {
        // Add additional routes to Activity resource
        $this->addAdditionalRoute('checkin', 'checkin/{user}/{code}', ['GET','POST']);
    }


    public function checkin($user, $code)
    {
        return ['data' => [$user, $code]];
    }

}

<?php namespace DMA\Friends\API\Transformers;

use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;
use DMA\Friends\API\Transformers\UserMetadataTransformer;

class RewardTransformer extends BaseTransformer {
   
    use DateTimeTransformerTrait;
   
    /**
     * List of default resources to include
     *
     * @var array
     */
    protected $defaultIncludes = [
            'media'
    ];
    
    /**
     * @SWG\Definition(
     *    definition="reward",
     *    description="Reward definition",
     *    type="object",
     *    required={"id", "title", "media"},
     *    @SWG\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="title",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="media",
     *         type="object",
     *         ref="#/definitions/media"
     *    )
     * )
     */    
     
    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getData()
     */    
    public function getData($instance)
    {
        $data = [
            'id'            => $instance->id,
            'title'         => $instance->title
        ];
        
        return $data;
    }

    /**
     * @SWG\Definition(
     *    definition="reward.extended",
     *    description="Full reward definition",
     *    type="object",
     *    required={"id", "title", "description", "excerpt", "is_published", "is_archived", "is_hidden", 
     *              "points", "barcode", "date_begin", "date_end", "days_valid", "fine_print", "inventory", "media"},
     *    @SWG\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="title",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="description",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="excerpt",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="is_published",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="is_published",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="is_archived",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="is_hidden",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="points",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="barcode",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="date_begin",
     *         type="string",
     *         format="date-time"
     *    ),
     *    @SWG\Property(
     *         property="date_end",
     *         type="string",
     *         format="date-time"
     *    ),
     *    @SWG\Property(
     *         property="days_valid",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="fine_print",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="inventory",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="media",
     *         type="object",
     *         ref="#/definitions/media"
     *    )
     * )
     */

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getExtendedData()
     */
    public function getExtendedData($instance)
    {
        return [
                'description'   => $instance->description,
                'excerpt'       => $instance->excerpt,
                'is_published'  => ($instance->is_published)?true:false,
                'is_archived'   => ($instance->is_archived)?true:false,
                'is_hidden'     => ($instance->is_hidden)?true:false,
                'points'        => $instance->points,
                'barcode'       => $instance->barcode,
                'date_begin'    => $this->carbonToIso($instance->date_begin),
                'date_end'      => $this->carbonToIso($instance->date_end),
                'days_valid'    => $instance->days_valid,
                'fine_print'    => $instance->fine_print,
                'inventory'     => $this->getInventory($instance),
                #'enable_email'  => $instance->enable_email,
                #'redemption_email' => $instance->redemption_email,
                #'enable_admin_email' => $instance->enable_admin_email,
                #'email_template' => $instance->email_template,
                #'admin_email_template' => $instance->admin_email_template,
                #'admin_email_group' => $instance->admin_email_group,
                #'admin_email_address' =>  $instance->admin_email_address,
        ];
    
    }    
        
    /**
     * Normalize inventory value
     * @param Model $instance
     * @return int
     */
    protected function getInventory(Model $instance)
    {
        try{
            if (!is_null($instance->inventory)) {
                return $instance->inventory;
            }
        }catch(\Exception $e){
            // Do nothing
        }
        return 0;
    }
    

    /**
     * Include Media
     *
     * @return League\Fractal\ItemResource
     */
    public function includeMedia(Model $instance)
    {
        return $this->item($instance, new MediaTransformer);
    }
    
    
}

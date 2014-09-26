<?php

namespace DMA\Friends\Wordpress;

use Illuminate\Support\Facades\DB;
use DMA\Friends\Models\Usermeta;
use Rainlab\User\Models\User as OctoberUser;
use Rainlab\User\Models\Country;
use Rainlab\User\Models\State;

class User extends Post
{

    public function __construct()
    {
        $this->model = new OctoberUser;
        parent::__construct();
    }

    /**
     * Import user accounts from wordpress
     *
     * @param int $limit
     * The amount of records to import at one time
     */
    public function import($limit = 0)
    {
        $count  = 0;
        $table  = $this->model->table;
        $id     = (int)DB::table($table)->max('id');

        $wordpressUsers = $this->db
            ->table('wp_users')
            ->where('id', '>', $id)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        foreach($wordpressUsers as $wuser) {

            if (empty($wuser->user_email) || count($this->model->where('email', $wuser->user_email)->get())) {
                continue;
            }

            $user               = new $this->model;
            $user->id           = $wuser->ID;
            $user->created_at   = $wuser->user_registered;
            $user->name         = $wuser->user_nicename;
            $user->email        = $wuser->user_email;

            // This gets changed to a real password later
            $user->password = 'temppassword';
            $user->password_confirmation = 'temppassword';

            $this->updateMetadata($user, $wuser->ID);

            // Manually update the password hash as the model wants to rehash and validate it
            $this->model->where('id', $user->id)->update(['password' => $wuser->user_pass]);

            $count++;

        }

        return $count;
    }

    /**
     * Updates the metadata from wordpress on existing users
     */
    public function updateExistingUsers()
    {
        foreach(OctoberUser::all() as $user) {
            $id = $this->db
                ->table('wp_users')
                ->select('ID')
                ->where('user_email', $user->email)
                ->first();

            $this->updateMetadata($user, $id->ID);
        }
    }

    /**
     * Updates and save the metadata for a user object
     */
    public function updateMetadata(OctoberUser $user, $wordpressId)
    {
        $metadata = $this->db
            ->table('wp_usermeta')
            ->where('user_id', $wordpressId)
            ->get();

        // Organize the metadata for mapping to user fields
        $data = [
            'home_phone'            => '',
            'street_address'        => '',
            'city'                  => '',
            'state'                 => '',
            'zip'                   => '',
            'first_name'            => '',
            'last_name'             => '',
            '_badgeos_points'       => '',
            'email_optin'           => false,
            'current_member'        => false,
            'current_member_number' => '',
        ];

        foreach($metadata as $mdata) {
            $data[$mdata->meta_key] = $mdata->meta_value;
        }

        $user->phone            = $data['home_phone'];
        $user->street_addr      = $data['street_address'];
        $user->city             = $data['city'];
        $user->zip              = $data['zip'];

        // Populate state and country objects
        if (!empty($data['state'])) {
            $state = State::where('code', strtoupper($data['state']))->first();
            if (!$state) {
                $state = State::where('name', $data['state'])->first();
            }

            if ($state) {
                $user->state()->associate($state);
                $user->country()->associate(Country::find($state->country_id));
            }
        }

        $metadata                           = new Usermeta;
        $metadata->first_name               = $data['first_name'];
        $metadata->last_name                = $data['last_name'];
        $metadata->points                   = $data['_badgeos_points'];
        $metadata->email_optin              = $data['email_optin'];
        $metadata->current_member           = $data['current_member'];
        $metadata->current_member_number    = $data['current_member_number'];

        try {
            $user->forceSave();
            $user->metadata()->delete();
            $user->metadata()->save($metadata);
        } catch(Exception $e) {

        }
    }

}

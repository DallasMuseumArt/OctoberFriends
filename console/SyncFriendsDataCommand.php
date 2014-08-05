<?php

namespace DMA\Friends\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use DMA\Friends\Models\Badge;
use DMA\Friends\Models\Usermeta;
use Rainlab\User\Models\User;
use Rainlab\User\Models\Country;
use Rainlab\User\Models\State;

class SyncFriendsDataCommand extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'friends:sync-data';

    /**
     * @var string The console command description.
     */
    protected $description = 'Syncronize wordpress data into October';

    /**
     * @var object Contains the database object when fired
     */
    protected $db = null;

    /**
     * @var Number of records to process per run
     */
    protected $limit = 1000;

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        $this->db = DB::connection('friends_wordpress');

        $this->sync();

        $this->output->writeln('Sync processed ' . $this->limit . ' records');
    }

    protected function sync()
    {
        $u = new User;
        $id = (int)DB::table($u->table)->max('id');

        $wordpressUsers = $this->db
            ->table('wp_users')
            ->where('id', '>', $id)
            ->orderBy('id', 'asc')
            ->limit($this->limit)
            ->get();

        foreach($wordpressUsers as $wuser) {

            if (empty($wuser->user_email)) {
                $this->output->writeln('invalid account');
                var_dump($wuser);
                continue;
            }

            if (count(User::where('email', $wuser->user_email)->get())) {
                $this->output->writeln('duplicate account');
                var_dump($wuser);
                continue;
            }

            $user               = new User;
            $user->id           = $wuser->ID;
            $user->created_at   = $wuser->user_registered;
            $user->name         = $wuser->user_nicename;
            $user->email        = $wuser->user_email;

            // TODO figure out how to migrate password
            //$user->password     = $wuser->user_pass;
            $user->password = 'temppassword';
            $user->password_confirmation = 'temppassword';


            $metadata = $this->db
                ->table('wp_usermeta')
                ->where('user_id', $wuser->ID)
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
                $user->metadata()->save($metadata);

                // Manually update the password hash as the model wants to rehash and validate it
                User::where('id', $user->id)->update(['password' => $wuser->user_pass]);
            } catch(ValidateException $e) {
                $this->output->writeln('account failed: ' . $user->email);
            }

            $this->output->writeln('saved user: ' . $user->email);
        }

        
    }
}

<?php namespace DMA\Friends\Commands;

use Log;
use RainLab\User\Models\User;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use DMA\Friends\Facades\MailChimpIntegration;
use Illuminate\Console\Command;

/**
 * Synchronize Friends users to a MailChip list
 *
 * @package DMA\Friends\Commands
 * @author Kristen Arnold, Carlos Arroyo
 */
class SyncFriendsToMailChimp extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'friends:sync-friends-mailchimp';

    /**
     * @var string The console command description.
     */
    protected $description = 'Syncronize Friends users data into MailChimp';

    /**
     * @return void
     */
    public function fire()
    {
          $this->synchronizeUser();
    }

    protected function synchronizeUser()
    {
        $alwaysUpdate = $this->option('force-update');
        
        $this->progressbar = $this->getHelperSet()->get('progress');
        $this->progressbar->start($this->output, User::count());
                
        User::chunk(500, function($users) use ($alwaysUpdate) {
            foreach($users as $user) {
                $this->progressbar->advance();
                
                $email = $user->email;
               
                MailChimpIntegration::syncMemberToMailChimp($email, $user, false);
            }
        });
        
       $$this->progressbar->finish();
    }
    
    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
                ['force-update', 'u', InputOption::VALUE_NONE, 'Always update member in the list with Friends member data', null],
        ];
    }

    
}
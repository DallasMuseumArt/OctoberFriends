<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Flash;
use Lang;
use File;
use Hash;
use Cms\Classes\Theme;
use RainLab\User\Models\State;
use DMA\Friends\Models\Usermeta;
use DMA\Friends\Classes\UserExtend;

class UserProfile extends ComponentBase
{

    use \System\Traits\ViewMaker;

    public function componentDetails()
    {
        return [
            'name'        => 'User profile form',
            'description' => ''
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->addAssets();

        $user = $this->getUser();

        $this->page['options'] = Usermeta::getOptions();
        $this->page['user'] = $user;

    }

    /**
     * render the change avatar popup
     */
    public function onAvatar()
    {

        $avatars = [];

        $themePath = UserLogin::getThemeDir();
        $avatarPath = $themePath . '/assets/images/avatars/*.jpg';

        // loop through all the files in the plugin's avatars directory and parse the file names
        foreach ( glob($avatarPath ) as $file ) { 
            $path = str_replace(base_path(), '', $file);

            $avatars[] = [
                'path' => $path,
                'basename' => basename($path)
            ];
        }

        $user = Auth::getUser();

        return $this->renderPartial('@modalDisplay', [
            'title' => 'Select an avatar',
            'content' => $this->renderPartial('@avatars', [
                'avatars' => $avatars,
                'userAvatar' => $user->avatar->file_name,
            ]),
        ]);

    }

    public function onAvatarSave()
    {
        $user = Auth::getUser();

        $avatar = post('avatar');

        if ($avatar) 
            UserExtend::uploadAvatar($user, $avatar);

        return [
            '.avatar-image' => '<img src="' . $user->avatar->getThumb(100, 100) . '"/>',
            '.modal-body'   => "<script type='text/javascript'>$('button.close').click();</script>",
        ];

    }

    /**
     * Renders modal dialog with password change form
     */
    public function onPassword()
    {
        return $this->makePartial('changepassword');
    }

    /**
     * Verify that the user password is correct and save the new password
     */
    public function onPasswordSave()
    {
        $data = post();
        $user = $this->getUser();

        if (Hash::check($data['old_password'], $user->password)) {
            $user->password = $data['password'];
            $user->password_confirmation = $data['password_confirm'];
            
            if ($user->save()) {

                // Re-authenticate the user
                $user = Auth::authenticate([
                    'email'     => $user->email,
                    'password'  => $data['password'],
                ], true);

                Flash::info(Lang::get('dma.friends::lang.user.passwordSave'));
            } else {
                Flash::error(Lang::get('dma.friends::lang.user.passwordFail'));
            }
        } else {
            Flash::error(Lang::get('dma.friends::lang.user.passwordFail'));
        }

        return [
            '#flashMessages' => $this->renderPartial('@flashMessages'),
            '.modal-body'  => "<script type='text/javascript'>$('button.close').click();</script>",
        ];

    }

    /**
     * Save the users profile
     */
    public function onSave()
    {
        $user = $this->getUser();

        $vars = post();
        foreach($vars as $key => $val) {
            if ($key == 'metadata') {
                foreach($val as $metakey => $metaval) {
                    $user->metadata->{$metakey} = $metaval;
                }

            } else {
                if ($key == "phone") {
                    $val = UserExtend::parsePhone($val);
                }

                $user->{$key} = $val;
            }
        }

        if ($user->push()) {
            Flash::info(Lang::get('dma.friends::lang.user.save'));
        } else {
            Flash::error(Lang::get('dma.friends::lang.user.saveFailed'));
        }

        return [
            '#flashMessages' => $this->renderPartial('@flashMessages'),
        ];
    }

    /**
     * Get the authenticated user
     * @return User $user
     * A user object
     */
    public function getUser()
    {
        $user = Auth::getUser();

        if (!$user) return false;

        return $user;
    }

    /**
     * Add all css/js assets
     */
    public function addAssets()
    {
        $this->addJs('/modules/system/assets/vendor/bootstrap/js/modal.js');
        $this->addJs('/modules/backend/assets/js/october.popup.js');
    }

    /**
     * Implementation of ViewMaker->makePartial that renders a partial in
     * a modal dialog and can accept a partial from the active theme
     *
     * @param string $partial
     * Name of the partial to be rendered.  The partial must reside in the active
     * theme's "partials" directory
     *
     * @param array $params
     * An array of parameters to be passed to makePartial.  See Backend\Traits\ViewMaker 
     * for details
     *
     * @param boolean $throwException
     * if true an exception will be thrown if the partial is not available
     *
     * @return string $content
     * A rendered partial
     */
   
    public function makePartial($partial, $params = [], $throwException = true)
    {   
        $partialsDir = self::getThemePartialsDir();
        $partialPath = $partialsDir . $partial . '.htm';

        if (!File::isFile($partialPath)) {
            $partialPath = $this->getViewPath($partial) . '.htm';
        }

        if (!File::isFile($partialPath)) {
            if ($throwException)
                throw new SystemException(Lang::get('backend::lang.partial.not_found', ['name' => $partialPath]));
            else
                return false;
        }   

        return $this->makeFileContents($partialPath, $params);
    }   

    /**
     * Get the path to the theme's partials
     *
     * @return string $path
     */ 
    protected static function getThemePartialsDir()
    {
        $theme = Theme::getActiveTheme();
        return $theme->getPath() . '/partials/';
    }

}

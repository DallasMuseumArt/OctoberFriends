<?php
namespace DMA\Friends\Wordpress;

use DMA\Friends\Wordpress\PasswordHash;
use RainLab\User\Models\User;

class Auth
{

    public static function verifyPassword($password, $hash)
    {
        $hasher = new PasswordHash(8, TRUE);

        return $hasher->CheckPassword($password, $hash);
    }

    public static function verifyFromEmail($login, $pass)
    {
        if (!$login)
            return false;

        // Could also be
        if (!$user = User::whereEmail($login)->first()) {
            return;
        }

        $result = self::verifyPassword($pass, $user->password);

        if ($result) {
            $user->password = $user->password_confirmation = $pass;
            $user->forceSave();
            return true;
        }

        return false;
    }

}

<?php namespace DMA\Friends\Classes;

use RainLab\User\Models\User;

/** 
 * Interface to allow plugins implement custom memberships
 * on Friends platform
 * 
 * @package DMA\Friends\Classes
 * @author Kristen Arnold, Carlos Arroyo
 */

interface FriendsMembershipInterface
{
    public function retriveById($id);
    public function retriveByCredentials(array $credentials);
    public function verifyMembership($membershipData, array $inputData);
    public function getMembershipHintsAttributes();
    public function saveMembership(User $user, $membership);
}

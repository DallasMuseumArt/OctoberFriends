<?php

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Tests\MuffinCase;
use DMA\Friends\Models\Settings;


class UserGroupModelTest extends MuffinCase
{
	 
	public function __construct()
    {
    }

    public function testCreateModelInstance()
    {
        $group = FactoryMuffin::create('DMA\Friends\Models\UserGroup');
        $this->assertInstanceOf('DMA\Friends\Models\UserGroup', $group);
        $this->assertInstanceOf('RainLab\User\Models\User', $group->owner);
    }
    
    //
    // Test group manipulation using plain Eloquent query builder
    //
    
    public function testCanHaveMembers()
    {
    	$group = FactoryMuffin::create('DMA\Friends\Models\UserGroup');
    	    	
    	// Create member instances
    	$member1 = FactoryMuffin::instance('RainLab\User\Models\User');
    	$member2 = FactoryMuffin::instance('RainLab\User\Models\User');
    	
		// Add members to the group and save them into the DB   	
    	$group->users()->save($member1);
    	$group->users()->save($member2);
    
    	// Validate if the members are the same   	
    	$this->assertEquals($group->users[0]->getKey(), $member1->getKey());
    	$this->assertEquals($group->users[1]->getKey(), $member2->getKey());
    	
    	// Check if group validates all rules when saving
    	$this->assertTrue($group->save());
    
    }
    
    
    /**
     * Expecting integrity constraint violation exception because this test produce
     * member a duplication in a group. 
     * 
     * @expectedException     Illuminate\Database\QueryException
     * @expectedExceptionCode 23000
     */    
    public function testCanNotHaveDuplicateMembers()
    {
		// TODO : Find a better solution to validate this exception is raise.
		// This method works with MySQL and MariaDB, This test could fail using 
		// other databases.
		
    	$group = FactoryMuffin::create('DMA\Friends\Models\UserGroup');
    	
    	// Create member instances
    	$members = FactoryMuffin::seed(2, 'RainLab\User\Models\User');
    	
    	foreach ($members as $member){
    		$group->users()->attach($member->id);
    	}
    	
    	// Here this test should fail and raise and exception because
    	// this member already exists
    	$group->users()->attach($members[0]->id);
    
    }


    public function testGroupInactiveCanNotHaveMembers()
    {

    	//$this->markTestIncomplete('Found in Laravel a best practice for handlering this case.');
 
     	$attrs = ['is_active' => false];
    	$group = FactoryMuffin::create('DMA\Friends\Models\UserGroup', $attrs);
    	 
    	// Create member instances
    	$user = FactoryMuffin::create('RainLab\User\Models\User');
    	 
    	$group->addUser($user);
    	
    	// This should be zero
    	$this->assertEquals(0, count($group->getUsers()));
    	
    }
    
    //
    // Test row level Group API manipulation.
    //
    
    /**
     * Test Add user to group and sent invite.
     */
    public function testAddMemberToGroup()
    {
    	// Create and empty group
    	$group = FactoryMuffin::create('DMA\Friends\Models\UserGroup');
    
    	// Create member instances
    	$user = FactoryMuffin::create('RainLab\User\Models\User');

    	$this->assertTrue($group->addUser($user));
    	
    	// If sent_invite is set True the invite was sent succesfully.
    	//$this->assertTrue($user->pivot->sent_invite);
    	
    	// Validate if the members are the same
    	$this->assertEquals($group->users[0]->getKey(), $user->getKey());
    	
    	return array($group, $user);
    
    }   

   /**
    * Test Remove  user from a group.
    * @depends testAddMemberToGroup
    */
    public function testUserInGroup(array $data)
    {
    	list($group, $user) = $data;
    	 
    	// Remove user
    	$this->assertTrue($group->inGroup($user));

    	foreach($group->users as $u){
    		$this->assertTrue($u->getKey() == $user->getKey());
    	}
    	
    	//$this->assertEquals(0, count($group->users));
    	
    	return [$group, $user];
    }
       
    /**
     * Test Remove  user from a group.
     * @depends testUserInGroup
     */
    public function testMemberAcceptRejectInvite(array $data)
    {
    	list($group, $user) = $data;

    	
    	// Test invite is pending
    	$this->assertFalse((bool)$user->pivot->is_confirmed);
    	
    	// accept invite
    	$group->acceptInvite($user);
    
    	// Test User accept invite
    	$this->assertTrue((bool)$user->pivot->is_confirmed);
    
    	// reject invite
    	$group->rejectInvite($user);
    	
    	// Test User accept invite
    	$this->assertFalse((bool)$user->pivot->is_confirmed);

    	
    	return [$group, $user];
    }
    
    
    
    /**
     * Test Remove  user from a group.
     * @depends testMemberAcceptRejectInvite
     */    
    public function testRemoveMemberFromGroup(array $data)
    {
    	list($group, $user) = $data;
    	
    	$this->assertEquals(1, count($group->getUsers()));
   		// Remove user
    	$group->removeUser($user);
    	
    	// Test cached relationship count
    	$this->assertEquals(0, count($group->getUsers()));
    	
    	// Test Eloquent relationship count 
    	$this->assertEquals(0, count($group->users()->get()));
    }

    

    /**
     * Test Limit group size.
     */
    public function testGroupLimitSize()
    {
    	// Create and pre-filled group
    	$group = FactoryMuffin::create('filled:DMA\Friends\Models\UserGroup');
    	
    	// Group limit from settings
    	$limit = Settings::get('maximum_users_group');
    	
    	$this->assertEquals($limit, count($group->getUsers()));
    	
    	// Add new user and expect an exception
    	// Create member instances
    	$user = FactoryMuffin::create('RainLab\User\Models\User');
    	$this->assertFalse($group->addUser($user));
    	
    	// Test that the new user were not added
    	$this->assertEquals($limit, count($group->getUsers()));

    }
    
    /***
     * Test bulk send invite to members of the group
    */
    public function testSentInviteToMembers()
    {
    	// Create a group with 5 members
    	$group = FactoryMuffin::create('filled:DMA\Friends\Models\UserGroup');
    	$group->sendUserInvitations();
    
    	// Test if all invites were sent
    	foreach($group->getUsers() as $user){
    		$this->assertTrue((bool)$user->pivot->sent_invite);
    	}
    
    }
    
}

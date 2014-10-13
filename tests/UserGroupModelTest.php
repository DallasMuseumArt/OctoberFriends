<?php

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Tests\MuffinCase;
use DMA\Friends\Models\Settings;
use DMA\Friends\Models\UserGroup;


class UserGroupModelTest extends MuffinCase
{
	 
	public function __construct()
    {
    }

    public function testResetGroups()
    {
    	UserGroup::markInactiveGroups();
    	$this->assertEquals(0, count(UserGroup::where('is_active', '=', true)->get()));
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
    public function testGroupAcceptance(array $data)
    {
    	list($group, $user) = $data;

    	
    	// Test invite is pending
    	$this->assertEquals($user->pivot->membership_status, UserGroup::MEMBERSHIP_PENDING);
    	
    	// accept invite
    	$group->acceptMembership($user);
    
    	// Test User accept invite
    	$this->assertEquals($user->pivot->membership_status, UserGroup::MEMBERSHIP_ACCEPTED);
    
    	// reject invite
    	$group->rejectMembership($user);
    	
    	// Test User reject invite
    	$this->assertEquals($user->pivot->membership_status, UserGroup::MEMBERSHIP_REJECTED);
    	  	
    	// reject invite
    	// $group->cancelMembership($user);
    	 
    	// Test User reject invite
    	// $this->assertEquals($user->pivot->membership_status, UserGroup::MEMBERSHIP_CANCELLED);    	
    	
    	return [$group, $user];
    }

    /**
     * Test user cancel after accept join to a group.
     * @depends testUserInGroup
     */
    public function testUserCancelMembership(array $data)
    {
    	list($group, $user) = $data;
    
    	 
    	// Create member instances
    	$user = FactoryMuffin::create('RainLab\User\Models\User');
    	$this->assertTrue($group->addUser($user));
    	
    	// Test invite is pending
    	$this->assertEquals($user->pivot->membership_status, UserGroup::MEMBERSHIP_PENDING);
    	 
    	// accept invite
    	$group->acceptMembership($user);
    
    	// Test User accept invite
    	$this->assertEquals($user->pivot->membership_status, UserGroup::MEMBERSHIP_ACCEPTED);
    
    	// cancel membership
    	$group->cancelMembership($user);
    	 
    	// Test User reject invite
    	$this->assertEquals($user->pivot->membership_status, UserGroup::MEMBERSHIP_CANCELLED);

    	return [$group, $user];
    }
    
    
    /**
     * Test Remove  user from a group.
     * @depends testUserCancelMembership
     */    
    public function testRemoveMemberFromGroup(array $data)
    {
    	list($group, $user) = $data;
    	
    	// Remove cancelled user
    	$group->removeUser($user);
    	
    	// Check Eloquent relationship count of the  group it should be zero
    	$this->assertEquals(1, count($group->users()->get()));
    	
    	// Test that a pending user can be deleted
    	// Create member instances
    	$user = FactoryMuffin::create('RainLab\User\Models\User');
    	$this->assertTrue($group->addUser($user));
    	
    	// Should be 1 because there is a rejected user
    	$this->assertEquals(1, count($group->getUsers()));
   		// Remove user
    	$group->removeUser($user);
    	
    	// Test count of valid users in the group
    	$this->assertEquals(0, count($group->getUsers()));
    	
    	// Test Eloquent relationship count it should be 1 
    	// Because there is a rejected user
    	$this->assertEquals(1, count($group->users()->get()));
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
    	
    	// Test an user reject invited so a new user can be invited
    	$group->rejectMembership($group->users[0]);

    	// Append new user
    	$this->assertTrue($group->addUser($user));
    	 
    	// Test that the new user were not added
    	$this->assertEquals($limit, count($group->getUsers()));
    	 
    }

    
    /**
     * Test User can join multiple active groups
     */
    public function testUserCantJoinMultipleGroups()
    {
    	// Create two empty groups
    	$group1 = FactoryMuffin::create('DMA\Friends\Models\UserGroup');
    	$group2 = FactoryMuffin::create('DMA\Friends\Models\UserGroup');
    	 
    	// Create a new user 
    	$user = FactoryMuffin::create('RainLab\User\Models\User');
    	
    	// Invite user to join both groups
    	$this->assertTrue($group1->addUser($user));
    	$this->assertTrue($group2->addUser($user));
    	    	 
    	// User accept to join to group1
    	$this->assertTrue($group1->acceptMembership($user));
    	
    	// group2 do not accept user
    	$this->assertFalse($group2->acceptMembership($user));
    	
    	// User1 cancel membership in group1 and join group2 using pending invite
    	$this->assertTrue($group1->rejectMembership($user));
    	$this->assertTrue($group2->acceptMembership($user));
    	 
    
		// Test that a user with accepted membership can not be invited
    	$user2 = FactoryMuffin::create('RainLab\User\Models\User');
    	$this->assertTrue($group1->addUser($user2));    	

    	// User2 accept to join to group1
    	$this->assertTrue($group1->acceptMembership($user2));
    	
    	// Test that user2 can not join group2
    	$this->assertFalse($group2->addUser($user2));    	

    	// User2 cancel membership in group1
    	$this->assertTrue($group1->cancelMembership($user2));    

    	// Test that user2 can join group2
    	$this->assertTrue($group2->addUser($user2));    	
    	
    	// User2 accept to join to group2
    	$this->assertTrue($group2->acceptMembership($user2));    	
    }    
}

<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class RoleTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function testAddingUserToRole()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $role = new Role();
            $role->name = 'myRole';
            $role->validate();
            $saved = $role->save();
            $this->assertTrue($saved);
            $benny = User::getByUsername('benny');
            //Add the role to benny
            $benny->role = $role;
            $saved = $benny->save();
            $this->assertTrue($saved);
            $roleId = $role->id;
            unset($role);
            $role = Role::getById($roleId);
            $this->assertEquals(1, $role->users->count());
            $this->assertTrue($role->users[0]->isSame($benny));

            //Now try adding billy to the role but from the other side, from the role side.
            $billy = User::getByUsername('billy');
            $role->users->add($billy);
            $saved = $role->save();
            $this->assertTrue($saved);
            $billy->forget(); //need to forget billy otherwise it won't pick up the change. i tried unset(), test fails
            $billy = User::getByUsername('billy');
            $this->assertTrue($billy->role->id > 0);
            $this->assertTrue($billy->role->isSame($role));
        }

        public function testAddingChildRoleAsAParentRole()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $childRole              = new Role();
            $childRole->name        = 'childRole';
            $parentRole             = new Role();
            $parentRole->name       = 'parentRole';
            $parentRole->roles->add($childRole);
            $grandParentRole        = new Role();
            $grandParentRole->name  = 'grandParentRole';
            $grandParentRole->roles->add($parentRole);
            $saved                  = $grandParentRole->save();
            $this->assertTrue($saved);
            $parentRole->role       = $childRole;
            $this->assertFalse($parentRole->validate());
            $this->assertEquals('You cannot select a child role for the parent role', $parentRole->getError('role'));
            $grandParentRole->role  = $childRole;
            $this->assertFalse($grandParentRole->validate());
            $this->assertEquals('You cannot select a child role for the parent role', $grandParentRole->getError('role'));

        }
    }
?>

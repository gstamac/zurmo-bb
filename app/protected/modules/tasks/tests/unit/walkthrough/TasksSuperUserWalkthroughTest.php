<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2015 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2015. All rights reserved".
     ********************************************************************************/

    /**
     * Task module walkthrough tests.
     */
    class TasksSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $superAccountId  = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superAccountId2 = self::getModelIdByModelNameAndName ('Account', 'superAccount2');
            $superContactId  = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
            $account  = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $contact  = Contact::getById($superContactId);

            //confirm no existing activities exist
            $activities = Activity::getAll();
            $this->assertEquals(0, count($activities));

            //Test just going to the create from relation view.
            $this->setGetArray(array(   'relationAttributeName' => 'Account', 'relationModelId' => $superAccountId,
                                        'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalCreateFromRelation');

            //add related task for account using createFromRelation action
            $activityItemPostData = array('Account' => array('id' => $superAccountId));
            $this->setGetArray(array('relationAttributeName' => 'Account', 'relationModelId' => $superAccountId,
                                     'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData, 'Task' => array('name' => 'myTask')));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalSaveFromRelation');

            //now test that the new task exists, and is related to the account.
            $tasks = Task::getAll();
            $this->assertEquals(1, count($tasks));
            $this->assertEquals('myTask', $tasks[0]->name);
            $this->assertEquals(1, $tasks[0]->activityItems->count());
            $activityItem1 = $tasks[0]->activityItems->offsetGet(0);
            $this->assertEquals($account, $activityItem1);

            //test viewing the existing task in a details view
            $this->setGetArray(array('id' => $tasks[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalDetails');

            //test submitting the task on chage
            $this->setGetArray(array('id' => $tasks[0]->id));
            $this->setPostArray(array('Task' => array('name' => 'myTask', 'status' => Task::STATUS_IN_PROGRESS)));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalDetails');

            //Test just going to the copy from relation
            $this->setGetArray(array(   'relationAttributeName' => 'Account', 'relationModelId' => $superAccountId,
                                        'relationModuleId'      => 'accounts', 'id' => $tasks[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalCopyFromRelation');

            $tasks = Task::getAll();
            $this->assertEquals(2, count($tasks));
            $this->assertEquals('myTask', $tasks[1]->name);
            $this->assertEquals(1, $tasks[1]->activityItems->count());

            //test removing a task.
            $this->setGetArray(array('id' => $tasks[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/delete', true);
            //Confirm no more tasks exist.
            $tasks = Task::getAll();
            $this->assertEquals(1, count($tasks));
        }
   }
?>
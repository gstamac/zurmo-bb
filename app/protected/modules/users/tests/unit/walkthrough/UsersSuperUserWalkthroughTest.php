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
     * User Module
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class UsersSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $aUser = UserTestHelper::createBasicUser('aUser');
            $aUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB);
            $saved = $aUser->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            UserTestHelper::createBasicUser('bUser');
            UserTestHelper::createBasicUser('cUser');
            UserTestHelper::createBasicUser('dUser');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('users/default');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/create');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');

            //Access to admin configuration should be allowed.
            $this->runControllerWithNoExceptionsAndGetContent('configuration');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $users = User::getAll();
            $this->assertEquals(5, count($users));
            $aUser = User::getByUsername('auser');
            $bUser = User::getByUsername('buser');
            $cUser = User::getByUsername('cuser');
            $dUser = User::getByUsername('duser');
            $super = User::getByUsername('super');

            $this->setGetArray(array('id' => $super->id));
            //Access to allowed to view Audit Trail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            $this->setGetArray(array('id' => $aUser->id));
            //Access to allowed to view Audit Trail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            $this->setGetArray(array('id' => $bUser->id));
            //Access to allowed to view Audit Trail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            $this->setGetArray(array('id' => $super->id));
            //Access to User Role edit link and control available.
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertContains('User_role_SelectLink', $content);
            $this->assertContains('User_role_name', $content);

            $this->setGetArray(array('id' => $aUser->id));
            //Access to User Role edit link and control available.
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertContains('User_role_SelectLink', $content);
            $this->assertContains('User_role_name', $content);

            $users = User::getAll();
            $this->assertEquals(5, count($users));
            //Save user.
            $this->assertTrue($aUser->id > 0);
            $this->assertEquals('aUserson', $aUser->lastName);
            $this->assertEquals(null, $aUser->officePhone);
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('User' =>
                array('officePhone' => '456765421')));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');
            $users = User::getAll();
            $this->assertEquals(5, count($users));
            $aUser = User::getById($aUser->id);
            $this->assertEquals('456765421', $aUser->officePhone);
            $this->assertEquals('aUserson',  $aUser->lastName);
            //Test having a failed validation on the user during save.
            $this->setGetArray (array('id'      => $aUser->id));
            $this->setPostArray(array('User' => array('lastName' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertContains('Name cannot be blank', $content);
            $users = User::getAll();
            $this->assertEquals(5, count($users));
            //LastName for aUser should still be aUserson.
            //Need to forget aUser, since it has lastName = '' from the setAttributes called in actionEdit.
            //Retrieve aUser and confirm the lastName is still aUserson.
            $aUser->forget();
            $aUser = User::getByUsername('auser');
            $this->assertEquals('aUserson', $aUser->lastName);

            //Load Model Detail View
            $this->setGetArray(array('id' => $aUser->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/details');
            //Load game dashboard view
            $this->setGetArray(array('id' => $aUser->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/gameDashboard');

            //Load Model Security Detail View
            $this->setGetArray(array('id' => $aUser->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/securityDetails');

            //Load Model Security Detail View for super user
            $this->setGetArray(array('id' => $super->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/securityDetails');

            //Load Model MassEdit Views.
            //MassEdit view for single selected ids
            $this->setGetArray(array('selectedIds' => '4,5,6,7', 'selectAll' => '')); // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEdit');
            $this->assertContains('<strong>4</strong>&#160;records selected for updating', $content);

            //MassEdit view for all result selected ids
            $users = User::getAll();
            $this->assertEquals(5, count($users));
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEdit');
            $this->assertContains('<strong>5</strong>&#160;records selected for updating', $content);
            //save Model MassEdit for selected Ids
            //Test that the 4 contacts do not have the office phone number we are populating them with.
            $user1 = User::getById($aUser->id);
            $user2 = User::getById($bUser->id);
            $user3 = User::getById($cUser->id);
            $user4 = User::getById($dUser->id);
            $this->assertNotEquals   ('7788', $user1->officePhone);
            $this->assertNotEquals   ('7788', $user2->officePhone);
            $this->assertNotEquals   ('7788', $user3->officePhone);
            $this->assertNotEquals   ('7788', $user4->officePhone);
            $this->setGetArray(array(
                'selectedIds'  => $aUser->id . ',' . $bUser->id, // Not Coding Standard
                'selectAll'    => '',
                'User_page'    => 1));
            $this->setPostArray(array(
                'User'      => array('officePhone' => '7788'),
                'MassEdit'     => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/massEdit');

            //Test that the 2 contacts have the new office phone number and the other contacts do not.
            $user1 = User::getById($aUser->id);
            $user2 = User::getById($bUser->id);
            $user3 = User::getById($cUser->id);
            $user4 = User::getById($dUser->id);
            $this->assertEquals      ('7788', $user1->officePhone);
            $this->assertEquals      ('7788', $user2->officePhone);
            $this->assertNotEquals   ('7788', $user3->officePhone);
            $this->assertNotEquals   ('7788', $user4->officePhone);

            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll'    => '1',
                'User_page'    => 1));
            $this->setPostArray(array(
                'User'         => array('officePhone' => '1234'),
                'MassEdit'     => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/massEdit');
            //Test that all accounts have the new phone number.
            $user1 = User::getById($aUser->id);
            $user2 = User::getById($bUser->id);
            $user3 = User::getById($cUser->id);
            $user4 = User::getById($dUser->id);
            $this->assertEquals   ('1234', $user1->officePhone);
            $this->assertEquals   ('1234', $user2->officePhone);
            $this->assertEquals   ('1234', $user3->officePhone);
            $this->assertEquals   ('1234', $user4->officePhone);

            //Run Mass Update using progress save.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 1);
            //The page size is smaller than the result set, so it should exit.
            $this->runControllerWithExitExceptionAndGetContent('users/default/massEdit');
            //save Modal MassEdit using progress load for page 2, 3, 4 and 5.
            $this->setGetArray(array('selectAll' => '1', 'User_page' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEditProgressSave');
            $this->assertContains('"value":40', $content);
            $this->setGetArray(array('selectAll' => '1', 'User_page' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEditProgressSave');
            $this->assertContains('"value":60', $content);
            $this->setGetArray(array('selectAll' => '1', 'User_page' => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEditProgressSave');
            $this->assertContains('"value":80', $content);
            $this->setGetArray(array('selectAll' => '1', 'User_page' => 5));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEditProgressSave');
            $this->assertContains('"value":100', $content);
            //Set page size back to old value.
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);

            //Autocomplete for User
            $this->setGetArray(array('term' => 'auser'));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/autoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y', 'modalId' => 'z')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/modalList');

            //Change password view.
            $this->setGetArray(array('id' => $aUser->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/changePassword');

            //Failed change password validation
            $this->setPostArray(array('ajax' => 'edit-form',
                'UserPasswordForm' => array('newPassword' => '', 'newPassword_repeat' => '')));
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/changePassword');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //Successful change password validation
            $this->setPostArray(array('ajax' => 'edit-form',
                'UserPasswordForm' => array('newPassword' => 'aNewPassword', 'newPassword_repeat' => 'aNewPassword')));
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/changePassword');
            $this->assertEquals('[]', $content);

            //Successful saved password change.
            $this->setPostArray(array('save' => 'Save',
                'UserPasswordForm' => array('newPassword' => 'bNewPassword', 'newPassword_repeat' => 'bNewPassword')));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/changePassword');
            //Login using new password successfully.
            $identity = new UserIdentity('auser', 'bNewPassword');
            $authenticated = $identity->authenticate();
            $this->assertEquals(0, $identity->errorCode);
            $this->assertTrue($authenticated);

            //User Configuration UI. Change aUser configuration values.
            //First make sure settings are not what we are setting them too.
            $this->assertNotEquals(9, Yii::app()->pagination->getByUserAndType($aUser, 'listPageSize'));
            $this->assertNotEquals(4, Yii::app()->pagination->getByUserAndType($aUser, 'subListPageSize'));
            //Load up configuration page.
            $this->setGetArray(array('id' => $aUser->id));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/configurationEdit');
            //Post fake save that will fail validation.
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('UserConfigurationForm' =>
                array(
                        'listPageSize' => 0,
                        'subListPageSize' => 4,
                        )));

            $this->runControllerWithNoExceptionsAndGetContent('users/default/configurationEdit');
            //Post fake save that will pass validation.
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('UserConfigurationForm' =>
                array(  'listPageSize' => 9,
                        'subListPageSize' => 4,
                        )));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/configurationEdit');
            $this->assertEquals('User configuration saved successfully.', Yii::app()->user->getFlash('notification'));
            //Check to make sure user configuration is actually changed.
            $this->assertEquals(9, Yii::app()->pagination->getByUserAndType($aUser, 'listPageSize'));
            $this->assertEquals(4, Yii::app()->pagination->getByUserAndType($aUser, 'subListPageSize'));
            //Confirm current user has certain session values
            $this->assertNotEquals(7, Yii::app()->user->getState('listPageSize'));
            $this->assertNotEquals(4, Yii::app()->user->getState('subListPageSize'));

            //Change current user configuration values. (Yii::app()->user->userModel)
            //First make sure settings are not what we are setting them too.
            $this->assertNotEquals(7, Yii::app()->pagination->getForCurrentUserByType('listPageSize'));
            //Load up configuration page.
            $this->setGetArray(array('id' => Yii::app()->user->userModel->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/configurationEdit');
            //Post fake save that will fail validation.
            $this->setGetArray(array('id' => Yii::app()->user->userModel->id));
            $this->setPostArray(array('UserConfigurationForm' =>
                array( 'listPageSize' => 0,
                        'subListPageSize' => 4,
                        )));

            $this->runControllerWithNoExceptionsAndGetContent('users/default/configurationEdit');
            //Post fake save that will pass validation.
            $this->setGetArray(array('id' => Yii::app()->user->userModel->id));
            $this->setPostArray(array('UserConfigurationForm' =>
                array(  'listPageSize' => 7,
                        'subListPageSize' => 4,
                        )));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/configurationEdit');
            $this->assertEquals('User configuration saved successfully.', Yii::app()->user->getFlash('notification'));
            //Check to make sure user configuration is actually changed.
            $this->assertEquals(7, Yii::app()->pagination->getForCurrentUserByType('listPageSize'));
            //Check getState data. since it should be updated for current user.
            $this->assertEquals(7, Yii::app()->user->getState('listPageSize'));
            $this->assertEquals(4, Yii::app()->user->getState('subListPageSize'));

            //User Notification Configuration UI. Change aUser notification configuration values.
            //First make sure settings all default values are true
            $notificationSettings = UserNotificationUtil::getNotificationSettingsByUser($aUser);
            $notificationSettingsNames = UserNotificationUtil::getAllNotificationSettingAttributes();
            foreach ($notificationSettingsNames as $setting)
            {
                list($settingName, $type) = UserNotificationUtil::getSettingNameAndTypeBySuffixedConfigurationAttribute($setting);
                $this->assertTrue((bool)$notificationSettings[$settingName][$type]);
            }
            //Load up notification configuration page.
            $this->setGetArray(array('id' => $aUser->id));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/notificationConfiguration');
            //Post fake save that will pass validation.
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('UserNotificationConfigurationForm' =>
                array(
                    'enableConversationInvitesNotificationInbox' => 0,
                )));

            $this->runControllerWithRedirectExceptionAndGetContent('users/default/notificationConfiguration');
            $this->assertEquals('User notifications configuration saved successfully.', Yii::app()->user->getFlash('notification'));
            //Check to make sure user notification configuration is actually changed.
            $this->assertFalse((bool) UserNotificationUtil::isEnabledByUserAndNotificationNameAndType($aUser, 'enableConversationInvitesNotification', 'inbox'));
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserUserStatusActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $user       = UserTestHelper::createBasicUser('statusCheck');
            $userId     = $user->id;
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(1, $user->isActive);

            //Change the user's status to inactive and confirm the changes in rights and isActive attribute.
            $this->setGetArray(array('id' => $user->id));
            $this->setPostArray(array('User' => array('userStatus'  => UserStatusUtil::INACTIVE)));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');
            $userId     = $user->id;
            $user       = User::getById($userId);
            $this->assertTrue(Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(0, $user->isActive);

            //Now change the user's status back to active.
            $this->setGetArray(array('id' => $user->id));
            $this->setPostArray(array('User' => array('userStatus'  => UserStatusUtil::ACTIVE)));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');
            $userId     = $user->id;
            $user       = User::getById($userId);
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(1, $user->isActive);
        }

        /**
         * @depends testSuperUserUserStatusActions
         */
        public function testSuperUserDefaultPortletControllerActions()
        {
            //Nothing currently to test.
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserDeleteAction()
        {
        }

        /**
         * @depends testSuperUserDeleteAction
         */
        public function testSuperUserCreateAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setPostArray(array('UserPasswordForm' =>
                                array('firstName'          => 'Some',
                                      'lastName'           => 'Body',
                                      'username'           => 'somenewuser',
                                      'newPassword'        => 'myPassword123',
                                      'newPassword_repeat' => 'myPassword123',
                                      'officePhone'        => '456765421',
                                      'userStatus'         => 'Active',
                                      'primaryEmail'       => array('emailAddress' => 'test@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0'),
                                      'secondaryEmail'     => array('emailAddress' => 'test1@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0') )));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/create');

            $user = User::getByUsername('somenewuser');
            $this->assertEquals('Some', $user->firstName);
            $this->assertEquals('Body', $user->lastName);
            $this->assertEquals('test@zurmo.com', $user->primaryEmail->emailAddress);
            $this->assertEquals('test1@zurmo.com', $user->secondaryEmail->emailAddress);
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserEditAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $user = User::getByUsername('somenewuser');
            $this->setGetArray(array('id' => $user->id));
            $this->setPostArray(array('User' =>
                                array(
                                      'title'              => array('value' => ''),
                                      'firstName'          => 'Some',
                                      'lastName'           => 'Body',
                                      'username'           => 'somenewuser',
                                      'jobTitle'           => '',
                                      'officePhone'        => '',
                                      'mobilePhone'        => '',
                                      'department'         => '',
                                      'manager'            => array('id' => $super->id),
                                      'role'               => array(),
                                      'primaryAddress'     => array('street1' => '',
                                                           'street2' => '',
                                                           'city' => '',
                                                           'state' => '',
                                                           'postalCode' => '',
                                                           'country' => ''),
                                      'language'           => array('value' => 'en'),
                                      'locale'             => array('value' => ''),
                                      'timeZone'           => array('value' => 'America/Chicago'),
                                      'currency'           => array('id' => '1'),
                                      'userStatus'         => 'Active',
                                      'primaryEmail'       => array('emailAddress' => 'test@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0'),
                                      'secondaryEmail'     => array('emailAddress' => 'test2@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0') )));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');
            $user = User::getByUsername('somenewuser');
            $this->assertEquals('Some', $user->firstName);
            $this->assertEquals('Body', $user->lastName);
            $this->assertEquals('test@zurmo.com', $user->primaryEmail->emailAddress);
            $this->assertEquals('test2@zurmo.com', $user->secondaryEmail->emailAddress);

            $this->setGetArray(array('id' => $user->id));
            $this->setPostArray(array('User' =>
                                array(
                                      'title'              => array('value' => ''),
                                      'firstName'          => 'Some',
                                      'lastName'           => 'Body',
                                      'username'           => 'somenewuser',
                                      'userStatus'         => 'Active',
                                      'primaryEmail'       => array('emailAddress' => 'test@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0'),
                                      'secondaryEmail'     => array('emailAddress' => 'test@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0') )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertContains('Secondary email address cannot be the same as the primary email address.', $content);

            $this->setPostArray(array('UserPasswordForm' =>
                                array('firstName'          => 'Some1',
                                      'lastName'           => 'Body1',
                                      'username'           => 'somenewuser1',
                                      'newPassword'        => 'myPassword123',
                                      'newPassword_repeat' => 'myPassword123',
                                      'officePhone'        => '456765421',
                                      'userStatus'         => 'Active',
                                      'primaryEmail'       => array('emailAddress' => 'test22@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0'),
                                      'secondaryEmail'     => array('emailAddress' => 'test23@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0') )));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/create');

            $user = User::getByUsername('somenewuser1');
            $this->assertEquals('Some1', $user->firstName);
            $this->assertEquals('Body1', $user->lastName);
            $this->assertEquals('test22@zurmo.com', $user->primaryEmail->emailAddress);
            $this->assertEquals('test23@zurmo.com', $user->secondaryEmail->emailAddress);

            $this->setGetArray(array('id' => $user->id));
            $this->setPostArray(array('User' =>
                                array(
                                      'title'              => array('value' => ''),
                                      'firstName'          => 'Some',
                                      'lastName'           => 'Body',
                                      'username'           => 'somenewuser',
                                      'userStatus'         => 'Active',
                                      'primaryEmail'       => array('emailAddress' => 'test@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0'),
                                      'secondaryEmail'     => array('emailAddress' => 'test22@zurmo.com',
                                                                    'optOut' => '0',
                                                                    'isInvalid' => '0') )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertContains('Email address already exists in system.', $content);
        }

        public function testSuperUserChangeAvatar()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $aUser = User::getByUsername('auser');

            //Super user as access to change every users avatar
            $this->setGetArray(array('id' => $aUser->id));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/changeAvatar');

            //Failed change avatar validation
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('ajax'           => 'edit-form',
                                      'UserAvatarForm' => array('avatarType'               => '3',
                                                                'customAvatarEmailAddress' => ''))
                                );
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/changeAvatar');
            $this->assertContains('You need to choose an email address', $content);

            //Successful change avatar validation
            $this->setGetArray (array('id'      => $aUser->id));
            $this->setPostArray(array('ajax'           => 'edit-form',
                                      'UserAvatarForm' => array('avatarType'               => '1',
                                                                'customAvatarEmailAddress' => ''))
                                );
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/changeAvatar');
            $this->assertContains('[]', $content);

            //Successful save avatar change.
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('save'           => 'Save',
                                      'UserAvatarForm' => array('avatarType'               => '2',
                                                                'customAvatarEmailAddress' => ''))
                                );
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/changeAvatar');
        }

        public function testSuperUserChangeOtherUserEmailSignature()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $aUser = User::getByUsername('auser');
            $this->assertEquals(0, $aUser->emailSignatures->count());
            $this->assertEquals($aUser, $aUser->getEmailSignature()->user);

            //Change email settings
            $this->setPostArray(array('EmailSmtpConfigurationForm' => array(
                                    'host'                              => 'abc',
                                    'port'                              => '565',
                                    'username'                          => 'myuser',
                                    'password'                          => 'apassword',
                                    'security'                          => 'ssl')));
            $this->runControllerWithRedirectExceptionAndGetContent('emailMessages/default/configurationEditOutbound');
            $this->assertEquals('Email configuration saved successfully.',
                                Yii::app()->user->getFlash('notification'));

            //Change aUser email signature
            $this->setGetArray(array('id' => $aUser->id));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/emailConfiguration');
            $this->assertNotContains('abc email signature', $content);
            $this->setPostArray(array('UserEmailConfigurationForm' => array(
                                    'fromName'                          => 'abc',
                                    'fromAddress'                       => 'abc@zurmo.org',
                                    'useCustomOutboundSettings'         => EmailMessageUtil::OUTBOUND_GLOBAL_SETTINGS,
                                    'emailSignatureHtmlContent'         => 'abc email signature')));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/emailConfiguration');
            $this->assertEquals('User email configuration saved successfully.',
                                Yii::app()->user->getFlash('notification'));
            $aUser = User::getByUsername('auser');
            $this->assertEquals(1, $aUser->emailSignatures->count());
            $this->assertEquals('abc email signature', $aUser->emailSignatures[0]->htmlContent);
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/emailConfiguration');
            $this->assertContains('abc email signature', $content);
        }

        /**
         * This would check resolveCanCurrentUserAccessRootUser and resolveAccessingASystemUser
         * in a walkthrough test
         */
        public function testSuperUserChangeAvatarForPermissionsAsRootAndSystemUser()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $aUser = User::getByUsername('auser');
            $aUser->setIsSystemUser();
            //$aUser->setIsRootUser();
            $this->assertTrue($aUser->save());
            unset($aUser);

            $aUser = User::getByUsername('auser');
            $this->assertFalse((bool)$aUser->isRootUser);
            $this->assertTrue((bool)$aUser->isSystemUser);

            //Super user as access to change every users avatar
            $this->setGetArray(array('id' => $aUser->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/changeAvatar');
            $this->assertContains('You have tried to access a page', $content);

            $aUser->setIsNotSystemUser();
            $aUser->setIsRootUser();
            $this->assertTrue($aUser->save());
            unset($aUser);

            $aUser = User::getByUsername('auser');
            $this->assertFalse((bool)$aUser->isSystemUser);
            $this->assertTrue((bool)$aUser->isRootUser);

            //Super user as access to change every users avatar
            $this->setGetArray(array('id' => $aUser->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/changeAvatar');
            $this->assertContains('You have tried to access a page', $content);

            $aUser->setIsNotSystemUser();
            $aUser->setIsNotRootUser();
            $this->assertTrue($aUser->save());
            unset($aUser);

            $aUser = User::getByUsername('auser');
            $this->assertFalse((bool)$aUser->isSystemUser);
            $this->assertFalse((bool)$aUser->isRootUser);

            //Super user as access to change every users avatar
            $this->setGetArray(array('id' => $aUser->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/changeAvatar');
            $this->assertNotContains('You have tried to access a page', $content);
        }

        //TODO: need to clarify with Jason
        public function testCanCurrentUserViewALinkRequiringElevatedAccess()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $aUser = User::getByUsername('auser');
            $this->setGetArray(array('id' => $aUser->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/details');
            $this->assertTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Details',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );
            $this->assertTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Edit',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );
            $this->assertTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Audit Trail',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );
            $this->assertTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Change Password',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );
            $this->assertTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Configuration',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );

            $aUser = User::getByUsername('auser');
            $aUser->setIsRootUser();
            $this->assertTrue($aUser->save());
            unset($aUser);

            $aUser = User::getByUsername('auser');
            $this->assertTrue((bool)$aUser->isRootUser);
            $this->assertFalse((bool)$aUser->isSystemUser);

            $this->setGetArray(array('id' => $aUser->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/gameDashboard');

            //Normal user should only see details of other users
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('bUser');
            $this->setGetArray(array('id' => $aUser->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/details');
            $this->assertTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Details',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );
            $this->assertNotTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Edit',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );
            $this->assertNotTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Audit Trail',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );
            $this->assertNotTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Change Password',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );
            $this->assertNotTag(
                array(
                    'tag'       => 'span',
                    'content'   => 'Configuration',
                    'ancestor'  => array(
                        'id'  => 'UserDetailsAndRelationsView',
                    )
                ), $content
            );
        }

        public function testExplicitLoginPermissions()
        {
            if (Yii::app()->edition != 'Community')
            {
                Yii::app()->googleAppsHelper->isEnabledSignInViaGoogle = false;
            }
            $aUser = User::getByUsername('auser');
            $aUser->setIsSystemUser();
            $aUser->setIsNotRootUser();
            $this->assertTrue($aUser->save());
            unset($aUser);

            $aUser = User::getByUsername('auser');
            $this->assertFalse((bool)$aUser->isRootUser);
            $this->assertTrue((bool)$aUser->isSystemUser);
            $this->setPostArray(array('LoginForm' => array(
                                                        'username' => $aUser->username,
                                                        'password' => 'bNewPassword',
                                                        'rememberMe' => '0')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/login');
            $this->assertContains('Incorrect username or password', $content);

            $aUser->setIsNotSystemUser();
            $this->assertTrue($aUser->save());
            unset($aUser);

            $aUser = User::getByUsername('auser');
            $this->assertFalse((bool)$aUser->isRootUser);
            $this->assertFalse((bool)$aUser->isSystemUser);
            $this->setPostArray(array('LoginForm' => array(
                                                        'username' => $aUser->username,
                                                        'password' => 'bNewPassword',
                                                        'rememberMe' => '0')));
            if (Yii::app()->edition == 'Community')
            {
                $this->runControllerWithRedirectExceptionAndGetContent('zurmo/default/login');
            }
            else
            {
                //Proper handling of license key infrastructure
                $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/login');
            }
        }

        public function testDateAttributeIsSanitizedCorrectly()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $metadata = User::getMetadata();
            if (!in_array('birthday', $metadata['User']['members']))
            {
                $metadata['User']['members'][]  = 'birthday';
            }
            if (!in_array(array('birthday', 'type', 'type' => 'date'), $metadata['User']['rules']))
            {
                $metadata['User']['rules'][]    = array('birthday', 'type', 'type' => 'date');
                $metadata['User']['elements']['birthday'] = 'Date';
            }
            unset($metadata['Person']);
            User::setMetadata($metadata);

            $messageLogger = new MessageLogger();
            RedBeanModelsToTablesAdapter::generateTablesFromModelClassNames(array('User'), $messageLogger);

            UserTestHelper::createBasicUser('dateUser');
            $dateUser = User::getByUsername('dateuser');
            $this->setGetArray(array('id' => $dateUser->id));
            $this->setPostArray(array('User' => array('birthday' => '12/05/2000')));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');
            $dateUser = User::getById($dateUser->id);
            $this->assertEquals('2000-12-05',  $dateUser->birthday);
        }

        public function testAuditEventsModalList()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            AuditEvent::logAuditEvent('UsersModule', UsersModule::AUDIT_EVENT_USER_PASSWORD_CHANGED, $super->username, $super);
            $this->setGetArray(array('id' => $super->id));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');
            $this->assertContains('User Password Changed', $content);
        }

        public function testGetUsersByPartialStringWithReadPermissionsForSecurableItem()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $user = new User();
            $user->username     = 'lion';
            $user->title->value = 'Mr.';
            $user->firstName    = 'Samuel';
            $user->lastName     = 'Simson';
            $user->setPassword('asdfgh');
            $this->assertTrue($user->save());

            // Get list of users by search term
            $timeZoneHelper = new ZurmoTimeZoneHelper();
            $timeZoneHelper->confirmCurrentUsersTimeZone();
            $this->setGetArray(array('term' => 'ahs'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/getUsersByPartialStringWithReadPermissionsForSecurableItem');
            $this->assertEmpty(json_decode($content));

            // Search by partial username
            $this->setGetArray(array('term' => 'lio'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/getUsersByPartialStringWithReadPermissionsForSecurableItem');
            $userData = json_decode($content);
            $this->assertNotEmpty($userData);
            $this->assertEquals(1, count($userData));
            $this->assertEquals($user->id, $userData[0]->id);
            $this->assertEquals(strval($user), $userData[0]->name);
            $this->assertEquals($user->username, $userData[0]->username);
            $this->assertEquals('users', $userData[0]->type);
            $this->assertEquals($user->getAvatarImageUrl(20, true), $userData[0]->avatar);

            // Search by full username
            $this->setGetArray(array('term' => 'lion'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/getUsersByPartialStringWithReadPermissionsForSecurableItem');
            $this->assertNotEmpty(json_decode($content));

            // Now search by partial first name
            $this->setGetArray(array('term' => 'Sam'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/getUsersByPartialStringWithReadPermissionsForSecurableItem');
            $this->assertNotEmpty(json_decode($content));

            // Now search by partial last name
            $this->setGetArray(array('term' => 'Simson'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/getUsersByPartialStringWithReadPermissionsForSecurableItem');
            $this->assertNotEmpty(json_decode($content));

            // Now test with contact(OwnedSecurableItem)
            $contact = ContactTestHelper::createContactByNameForOwner('Simon', $super);
            $this->setGetArray(array('term' => 'Simson', 'relatedModelClassName' => 'Contact', 'relatedModelId' => $contact->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/getUsersByPartialStringWithReadPermissionsForSecurableItem');
            $this->assertEmpty(json_decode($content));

            $user->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $this->assertTrue($user->save());

            $contact->addPermissions($user, Permission::READ);
            $this->assertTrue($contact->save());
            AllPermissionsOptimizationUtil::securableItemGivenReadPermissionsForUser($contact, $user);
            $this->setGetArray(array('term' => 'Simson', 'relatedModelClassName' => 'Contact', 'relatedModelId' => $contact->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/getUsersByPartialStringWithReadPermissionsForSecurableItem');
            $this->assertNotEmpty(json_decode($content));
            $userData = json_decode($content);
            $this->assertNotEmpty($userData);
            $this->assertEquals(1, count($userData));
            $this->assertEquals($user->id, $userData[0]->id);
            $this->assertEquals(strval($user), $userData[0]->name);
            $this->assertEquals($user->username, $userData[0]->username);
            $this->assertEquals('users', $userData[0]->type);
            $this->assertEquals($user->getAvatarImageUrl(20, true), $userData[0]->avatar);
        }
    }
?>
<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /*********************************************************************************
    * Zurmo is a customer relationship management program developed by
    * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
    *
    * Zurmo is free software; you can redistribute it and/or modify it under
    * the terms of the GNU General Public License version 3 as published by the
    * Free Software Foundation with the addition of the following permission added
    * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
    * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
    * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
    *
    * Zurmo is distributed in the hope that it will be useful, but WITHOUT
    * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
    * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
    * details.
    *
    * You should have received a copy of the GNU General Public License along with
    * this program; if not, see http://www.gnu.org/licenses or write to the Free
    * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
    * 02110-1301 USA.
    *
    * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
    * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
    ********************************************************************************/

    /**
    * Designer Module Walkthrough of meetings.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also tests the creation of the customfileds, addition of custom fields to all the layouts.
    * This also tests creation, edit and delete of the meetings based on the custom fields.
    */
    class MeetingsDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();
            //Create a account for testing.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            //Create a opportunity for testing.
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp', $super, $account);

            //Create a three contacts for testing.
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact1', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact2', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact3', $super, $account);

            //Create a meeting for testing.
            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('superMeeting', $super, $account);
        }

        public function testSuperUserMeetingDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Load AttributesList for Meeting module.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for Meeting module.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
             $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'MeetingsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'MeetingsModuleForm' => $this->createModuleEditGoodValidationPostData('meeting new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->setPostArray(array('save' => 'Save',
                'MeetingsModuleForm' => $this->createModuleEditGoodValidationPostData('meeting new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Meeting New Name',  MeetingsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Meeting New Names', MeetingsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('meeting new name',  MeetingsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('meeting new names', MeetingsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForAccountRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForContactRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForOpportunityRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'MeetingEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserMeetingDefaultControllerActions.
         */
        public function testSuperUserCustomFieldsWalkthroughForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('MeetingsModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('MeetingsModule', 'currency');
            $this->createDateCustomFieldByModule                ('MeetingsModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('MeetingsModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('MeetingsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('MeetingsModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('MeetingsModule', 'countrypicklist');
            $this->createDependentDropDownCustomFieldByModule   ('MeetingsModule', 'statepicklist');
            $this->createDependentDropDownCustomFieldByModule   ('MeetingsModule', 'citypicklist');
            $this->createIntegerCustomFieldByModule             ('MeetingsModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('MeetingsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('MeetingsModule', 'tagcloud');
            $this->createCalculatedNumberCustomFieldByModule    ('MeetingsModule', 'calculatednumber');
            $this->createDropDownDependencyCustomFieldByModule  ('MeetingsModule', 'dropdowndependency');
            $this->createPhoneCustomFieldByModule               ('MeetingsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('MeetingsModule', 'radio');
            $this->createTextCustomFieldByModule                ('MeetingsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('MeetingsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('MeetingsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForMeetingsModule.
         */
        public function testSuperUserAddCustomFieldsToLayoutsForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to MeetingEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'MeetingEditAndDetailsView'));
            $layout = MeetingsDesignerWalkthroughHelperUtil::getMeetingEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to UpcomingMeetingsForAccountRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForAccountRelatedListView'));
            $layout = MeetingsDesignerWalkthroughHelperUtil::getMeetingsRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForContactRelatedListView'));
            $layout = MeetingsDesignerWalkthroughHelperUtil::getMeetingsRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            $this->setGetArray(array('moduleClassName' => 'MeetingsModule',
                                     'viewClassName'   => 'UpcomingMeetingsForOpportunityRelatedListView'));
            $layout = MeetingsDesignerWalkthroughHelperUtil::getMeetingsRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForMeetingsModule.
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superMeetingId = self::getModelIdByModelNameAndName ('Meeting', 'superMeeting');
            //Load create, edit, and details views.
            $this->setGetArray(array('id' => $superMeetingId));
            $this->runControllerWithNoExceptionsAndGetContent('meetings/default/edit');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array(   'relationAttributeName'  => 'Account',
                                        'relationModelId'        => $superAccountId,
                                        'relationModuleId'       => 'account',
                                        'redirectUrl'            => 'someRedirection'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('meetings/default/createFromRelation');
            //todo: more permutations from different relations.
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForMeetingsModule.
         */
        public function testCreateAnMeetingAfterTheCustomFieldsArePlacedForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";

            //Get the super user, account, opportunity and contact id.
            $superUserId        = $super->id;
            $superAccount       = Account::getByName('superAccount');
            $superContactId1    = self::getModelIdByModelNameAndName('Contact', 'superContact1 superContact1son');
            $superContactId2    = self::getModelIdByModelNameAndName('Contact', 'superContact2 superContact2son');
            $superOpportunityId = self::getModelIdByModelNameAndName('Opportunity', 'superOpp');
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Create a new meeting based on the custom fields.
            $this->setGetArray(array(   'relationAttributeName'  => 'Account',
                                        'relationModelId'        => $superAccount[0]->id,
                                        'relationModuleId'       => 'account',
                                        'redirectUrl'            => 'someRedirection'));
            $this->setPostArray(array('Meeting' => array(
                                            'name'                              => 'myNewMeeting',
                                            'location'                          => 'Telephone',
                                            'startDateTime'                     => $datetime,
                                            'category'                          => array('value' => 'Meeting'),
                                            'description'                       => 'This is Meeting Description',
                                            'owner'                             => array('id' => $superUserId),
                                            'explicitReadWriteModelPermissions' => array('type' => null),
                                            'checkbox'                          => '1',
                                            'currency'                          => array('value'   => 45,
                                                                                         'currency' => array(
                                                                                         'id' => $baseCurrency->id)),
                                            'date'                              => $date,
                                            'datetime'                          => $datetime,
                                            'decimal'                           => '123',
                                            'picklist'                          => array('value'  => 'a'),
                                            'multiselect'                       => array('values' => array('ff', 'rr')),
                                            'tagcloud'                          => array('values' => array('writing', 'gardening')),
                                            'countrypicklist'                   => array('value'  => 'bbbb'),
                                            'statepicklist'                     => array('value'  => 'bbb1'),
                                            'citypicklist'                      => array('value'  => 'bb1'),
                                            'integer'                           => '12',
                                            'phone'                             => '259-784-2169',
                                            'radio'                             => array('value' => 'd'),
                                            'text'                              => 'This is a test Text',
                                            'textarea'                          => 'This is a test TextArea',
                                            'url'                               => 'http://wwww.abc.com'),
                                      'ActivityItemForm' => array(
                                            'Account'     => array('id'  => $superAccount[0]->id),
                                            'contact'     => array('ids' => $superContactId1 . ',' . $superContactId2), // Not Coding Standard
                                            'Opportunity' => array('id'  => $superOpportunityId))));
            $this->runControllerWithRedirectExceptionAndGetUrl('meetings/default/createFromRelation');

            //Check the details if they are saved properly for the custom fields.
            $meeting = Meeting::getByName('myNewMeeting');

            //Retrieve the permission of the meeting.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Meeting::getById($meeting[0]->id));
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($meeting[0]->name                             , 'myNewMeeting');
            $this->assertEquals($meeting[0]->location                         , 'Telephone');
            $this->assertEquals($meeting[0]->startDateTime                    , $datetimeAssert);
            $this->assertEquals($meeting[0]->category->value                  , 'Meeting');
            $this->assertEquals($meeting[0]->description                      , 'This is Meeting Description');
            $this->assertEquals($meeting[0]->owner->id                        , $superUserId);
            $this->assertEquals($meeting[0]->activityItems->count()           , 4);
            $this->assertEquals(0                                             , count($readWritePermitables));
            $this->assertEquals(0                                             , count($readOnlyPermitables));
            $this->assertEquals($meeting[0]->checkbox                         , '1');
            $this->assertEquals($meeting[0]->currency->value                  , 45);
            $this->assertEquals($meeting[0]->currency->currency->id           , $baseCurrency->id);
            $this->assertEquals($meeting[0]->date                             , $dateAssert);
            $this->assertEquals($meeting[0]->datetime                         , $datetimeAssert);
            $this->assertEquals($meeting[0]->decimal                          , '123');
            $this->assertEquals($meeting[0]->picklist->value                  , 'a');
            $this->assertEquals($meeting[0]->integer                          , 12);
            $this->assertEquals($meeting[0]->phone                            , '259-784-2169');
            $this->assertEquals($meeting[0]->radio->value                     , 'd');
            $this->assertEquals($meeting[0]->text                             , 'This is a test Text');
            $this->assertEquals($meeting[0]->textarea                         , 'This is a test TextArea');
            $this->assertEquals($meeting[0]->url                              , 'http://wwww.abc.com');
            $this->assertEquals($meeting[0]->countrypicklist->value           , 'bbbb');
            $this->assertEquals($meeting[0]->statepicklist->value             , 'bbb1');
            $this->assertEquals($meeting[0]->citypicklist->value              , 'bb1');
            $this->assertContains('ff'                                        , $meeting[0]->multiselect->values);
            $this->assertContains('rr'                                        , $meeting[0]->multiselect->values);
            $this->assertContains('writing'                                   , $meeting[0]->tagcloud->values);
            $this->assertContains('gardening'                                 , $meeting[0]->tagcloud->values);

            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Meeting');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $meeting[0]);
            $this->assertEquals(111                                           , $testCalculatedValue);
        }

        /**
         * @depends testCreateAnMeetingAfterTheCustomFieldsArePlacedForMeetingsModule.
         */
        public function testEditOfTheMeetingForTheTagCloudFieldAfterRemovingAllTagsPlacedForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the meeting Id.
            $meeting = Meeting::getByName('myNewMeeting');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";

            //Get the super user, account, opportunity and contact id.
            $superUserId        = $super->id;
            $superAccount       = Account::getByName('superAccount');
            $superContactId1    = self::getModelIdByModelNameAndName('Contact', 'superContact1 superContact1son');
            $superContactId2    = self::getModelIdByModelNameAndName('Contact', 'superContact2 superContact2son');
            $superContactId3    = self::getModelIdByModelNameAndName('Contact', 'superContact3 superContact3son');
            $superOpportunityId = self::getModelIdByModelNameAndName('Opportunity', 'superOpp');
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
            $activityItemFormContacts         = $superContactId1 . ',' . $superContactId2 . ',' . $superContactId3; // Not Coding Standard

            //Edit the meeting based on the custom fields and the meeting Id.
            $this->setGetArray (array('id' => $meeting[0]->id));
            $this->setPostArray(array('Meeting' => array(
                                'name'                              => 'myEditMeeting',
                                'location'                          => 'LandLine',
                                'startDateTime'                     => $datetime,
                                'endDateTime'                       => $datetime,
                                'category'                          => array('value' => 'Call'),
                                'description'                       => 'This is Edit Meeting Description',
                                'owner'                             => array('id' => $superUserId),
                                'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                                'checkbox'                          => '0',
                                'currency'                          => array('value'   => 40,
                                                                             'currency' => array(
                                                                             'id' => $baseCurrency->id)),
                                'date'                              => $date,
                                'datetime'                          => $datetime,
                                'decimal'                           => '12',
                                'picklist'                          => array('value'  => 'b'),
                                'multiselect'                       => array('values' =>  array('gg', 'hh')),
                                'tagcloud'                          => array('values' =>  array()),
                                'countrypicklist'                   => array('value'  => 'aaaa'),
                                'statepicklist'                     => array('value'  => 'aaa1'),
                                'citypicklist'                      => array('value'  => 'ab1'),
                                'integer'                           => '11',
                                'phone'                             => '259-784-2069',
                                'radio'                             => array('value' => 'e'),
                                'text'                              => 'This is a test Edit Text',
                                'textarea'                          => 'This is a test Edit TextArea',
                                'url'                               => 'http://wwww.abc-edit.com'),
                                'ActivityItemForm' => array(
                                'Account'     => array('id'  => $superAccount[0]->id),
                                'contact'     => array('ids' => $activityItemFormContacts),
                                'Opportunity' => array('id'  => $superOpportunityId))));
            $this->runControllerWithRedirectExceptionAndGetUrl('meetings/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $meeting = Meeting::getByName('myEditMeeting');

            //Retrieve the permission of the meeting.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Meeting::getById($meeting[0]->id));
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($meeting[0]->name                             , 'myEditMeeting');
            $this->assertEquals($meeting[0]->location                         , 'LandLine');
            $this->assertEquals($meeting[0]->startDateTime                    , $datetimeAssert);
            $this->assertEquals($meeting[0]->endDateTime                      , $datetimeAssert);
            $this->assertEquals($meeting[0]->category->value                  , 'Call');
            $this->assertEquals($meeting[0]->description                      , 'This is Edit Meeting Description');
            $this->assertEquals($meeting[0]->owner->id                        , $superUserId);
            $this->assertEquals($meeting[0]->activityItems->count()           , 5);
            $this->assertEquals(1                                             , count($readWritePermitables));
            $this->assertEquals(0                                             , count($readOnlyPermitables));
            $this->assertEquals($meeting[0]->checkbox                         , '0');
            $this->assertEquals($meeting[0]->currency->value                  , 40);
            $this->assertEquals($meeting[0]->currency->currency->id           , $baseCurrency->id);
            $this->assertEquals($meeting[0]->date                             , $dateAssert);
            $this->assertEquals($meeting[0]->datetime                         , $datetimeAssert);
            $this->assertEquals($meeting[0]->decimal                          , '12');
            $this->assertEquals($meeting[0]->picklist->value                  , 'b');
            $this->assertEquals($meeting[0]->integer                          , 11);
            $this->assertEquals($meeting[0]->phone                            , '259-784-2069');
            $this->assertEquals($meeting[0]->radio->value                     , 'e');
            $this->assertEquals($meeting[0]->text                             , 'This is a test Edit Text');
            $this->assertEquals($meeting[0]->textarea                         , 'This is a test Edit TextArea');
            $this->assertEquals($meeting[0]->url                              , 'http://wwww.abc-edit.com');
            $this->assertEquals($meeting[0]->countrypicklist->value           , 'aaaa');
            $this->assertEquals($meeting[0]->statepicklist->value             , 'aaa1');
            $this->assertEquals($meeting[0]->citypicklist->value              , 'ab1');
            $this->assertContains('gg'                                        , $meeting[0]->multiselect->values);
            $this->assertContains('hh'                                        , $meeting[0]->multiselect->values);
            $this->assertNotContains('reading'                                , $meeting[0]->tagcloud->values);
            $this->assertNotContains('writing'                                , $meeting[0]->tagcloud->values);
            $this->assertNotContains('surfing'                                , $meeting[0]->tagcloud->values);
            $this->assertNotContains('gardening'                              , $meeting[0]->tagcloud->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Meeting');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $meeting[0]);
            $this->assertEquals(1                                             , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheMeetingForTheTagCloudFieldAfterRemovingAllTagsPlacedForMeetingsModule.
         */
        public function testEditOfTheMeetingForTheCustomFieldsPlacedForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the meeting Id.
            $meeting = Meeting::getByName('myEditMeeting');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";

            //Get the super user, account, opportunity and contact id.
            $superUserId        = $super->id;
            $superAccount       = Account::getByName('superAccount');
            $superContactId1    = self::getModelIdByModelNameAndName('Contact', 'superContact1 superContact1son');
            $superContactId2    = self::getModelIdByModelNameAndName('Contact', 'superContact2 superContact2son');
            $superContactId3    = self::getModelIdByModelNameAndName('Contact', 'superContact3 superContact3son');
            $superOpportunityId = self::getModelIdByModelNameAndName('Opportunity', 'superOpp');
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
            $activityItemFormContacts         = $superContactId1 . ',' . $superContactId2 . ',' . $superContactId3; // Not Coding Standard

            //Edit the meeting based on the custom fields and the meeting Id.
            $this->setGetArray (array('id' => $meeting[0]->id));
            $this->setPostArray(array('Meeting' => array(
                                'name'                              => 'myEditMeeting',
                                'location'                          => 'LandLine',
                                'startDateTime'                     => $datetime,
                                'endDateTime'                       => $datetime,
                                'category'                          => array('value' => 'Call'),
                                'description'                       => 'This is Edit Meeting Description',
                                'owner'                             => array('id' => $superUserId),
                                'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                                'checkbox'                          => '0',
                                'currency'                          => array('value'   => 40,
                                                                             'currency' => array(
                                                                             'id' => $baseCurrency->id)),
                                'date'                              => $date,
                                'datetime'                          => $datetime,
                                'decimal'                           => '12',
                                'picklist'                          => array('value'  => 'b'),
                                'multiselect'                       => array('values' =>  array('gg', 'hh')),
                                'tagcloud'                          => array('values' =>  array('reading', 'surfing')),
                                'countrypicklist'                   => array('value'  => 'aaaa'),
                                'statepicklist'                     => array('value'  => 'aaa1'),
                                'citypicklist'                      => array('value'  => 'ab1'),
                                'integer'                           => '11',
                                'phone'                             => '259-784-2069',
                                'radio'                             => array('value' => 'e'),
                                'text'                              => 'This is a test Edit Text',
                                'textarea'                          => 'This is a test Edit TextArea',
                                'url'                               => 'http://wwww.abc-edit.com'),
                                'ActivityItemForm' => array(
                                'Account'     => array('id'  => $superAccount[0]->id),
                                'contact'     => array('ids' => $activityItemFormContacts),
                                'Opportunity' => array('id'  => $superOpportunityId))));
            $this->runControllerWithRedirectExceptionAndGetUrl('meetings/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $meeting = Meeting::getByName('myEditMeeting');

            //Retrieve the permission of the meeting.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Meeting::getById($meeting[0]->id));
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($meeting[0]->name                             , 'myEditMeeting');
            $this->assertEquals($meeting[0]->location                         , 'LandLine');
            $this->assertEquals($meeting[0]->startDateTime                    , $datetimeAssert);
            $this->assertEquals($meeting[0]->endDateTime                      , $datetimeAssert);
            $this->assertEquals($meeting[0]->category->value                  , 'Call');
            $this->assertEquals($meeting[0]->description                      , 'This is Edit Meeting Description');
            $this->assertEquals($meeting[0]->owner->id                        , $superUserId);
            $this->assertEquals($meeting[0]->activityItems->count()           , 5);
            $this->assertEquals(1                                             , count($readWritePermitables));
            $this->assertEquals(0                                             , count($readOnlyPermitables));
            $this->assertEquals($meeting[0]->checkbox                         , '0');
            $this->assertEquals($meeting[0]->currency->value                  , 40);
            $this->assertEquals($meeting[0]->currency->currency->id           , $baseCurrency->id);
            $this->assertEquals($meeting[0]->date                             , $dateAssert);
            $this->assertEquals($meeting[0]->datetime                         , $datetimeAssert);
            $this->assertEquals($meeting[0]->decimal                          , '12');
            $this->assertEquals($meeting[0]->picklist->value                  , 'b');
            $this->assertEquals($meeting[0]->integer                          , 11);
            $this->assertEquals($meeting[0]->phone                            , '259-784-2069');
            $this->assertEquals($meeting[0]->radio->value                     , 'e');
            $this->assertEquals($meeting[0]->text                             , 'This is a test Edit Text');
            $this->assertEquals($meeting[0]->textarea                         , 'This is a test Edit TextArea');
            $this->assertEquals($meeting[0]->url                              , 'http://wwww.abc-edit.com');
            $this->assertEquals($meeting[0]->countrypicklist->value           , 'aaaa');
            $this->assertEquals($meeting[0]->statepicklist->value             , 'aaa1');
            $this->assertEquals($meeting[0]->citypicklist->value              , 'ab1');
            $this->assertContains('gg'                                        , $meeting[0]->multiselect->values);
            $this->assertContains('hh'                                        , $meeting[0]->multiselect->values);
            $this->assertContains('reading'                                   , $meeting[0]->tagcloud->values);
            $this->assertContains('surfing'                                   , $meeting[0]->tagcloud->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Meeting');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $meeting[0]);
            $this->assertEquals(1                                             , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheMeetingForTheCustomFieldsPlacedForMeetingsModule.
         */
        public function testDeleteOfTheMeetingForTheCustomFieldsPlacedForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the meeting Id.
            $meeting = Meeting::getByName('myEditMeeting');

            //Set the meeting id so as to delete the meeting.
            $this->setGetArray(array('id' => $meeting[0]->id));
            $this->runControllerWithRedirectExceptionAndGetUrl('meetings/default/delete');

            //Check to confirm that the meeting is deleted.
            $meeting = Meeting::getByName('myEditMeeting');
            $this->assertEquals(0, count($meeting));
        }

        /**
         * @depends testDeleteOfTheMeetingForTheCustomFieldsPlacedForMeetingsModule.
         */
        public function testTypeAheadWorksForTheTagCloudFieldPlacedForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'rea'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected value.
            $this->assertTrue(strpos($content, "reading") > 0);
        }

        /**
         * @depends testTypeAheadWorksForTheTagCloudFieldPlacedForTasksModule.
         */
        public function testLabelLocalizationForTheTagCloudFieldPlacedForMeetingsModule()
        {
            Yii::app()->user->userModel =  User::getByUsername('super');
            $languageHelper = new ZurmoLanguageHelper();
            $languageHelper->load();
            $this->assertEquals('en', $languageHelper->getForCurrentUser());
            Yii::app()->user->userModel->language = 'fr';
            $this->assertTrue(Yii::app()->user->userModel->save());
            $languageHelper->setActive('fr');
            $this->assertEquals('fr', Yii::app()->user->getState('language'));

            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'surf'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected value.
            $this->assertTrue(strpos($content, "surfing fr") > 0);
        }
    }
?>
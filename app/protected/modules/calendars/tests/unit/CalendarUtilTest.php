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

    class CalendarUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ProductTestHelper::createProductByNameForOwner('First Product', User::getByUsername('super'));
            ProductTestHelper::createProductByNameForOwner('Second Product', User::getByUsername('super'));
        }

        public function setup()
        {
            parent::setup();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetStartDate()
        {
            $startDateTime = CalendarUtil::getStartDate(SavedCalendar::DATERANGE_TYPE_MONTH);
            $this->assertEquals(date('Y-m-01'), $startDateTime);
            $startDateTime = CalendarUtil::getStartDate(SavedCalendar::DATERANGE_TYPE_WEEK);
            $this->assertEquals(date('Y-m-d', strtotime('last monday', strtotime('tomorrow'))), $startDateTime);
            $startDateTime = CalendarUtil::getStartDate(SavedCalendar::DATERANGE_TYPE_DAY);
            $this->assertEquals(date('Y-m-d'), $startDateTime);
        }

        public function testGetEndDate()
        {
            $endDateTime = CalendarUtil::getEndDate(SavedCalendar::DATERANGE_TYPE_MONTH);
            $this->assertEquals(date('Y-m-d', strtotime('first day of next month')), $endDateTime);
            $endDateTime = CalendarUtil::getEndDate(SavedCalendar::DATERANGE_TYPE_WEEK);
            $this->assertEquals(date('Y-m-d', strtotime('Monday next week')), $endDateTime);
            $endDateTime = CalendarUtil::getEndDate(SavedCalendar::DATERANGE_TYPE_DAY);
            $this->assertEquals(date('Y-m-d', strtotime('tomorrow')), $endDateTime);
        }

        public function testGetUserSavedCalendars()
        {
            $savedCalendar = CalendarTestHelper::createSavedCalendarByName('Test Cal', '#315AB0');
            $calendars     = CalendarUtil::getUserSavedCalendars(Yii::app()->user->userModel);
            $this->assertCount(1, $calendars);
        }

        public function testGetFullCalendarFormattedDateTimeElement()
        {
            $startDateTime = CalendarUtil::getFullCalendarFormattedDateTimeElement('2014-01-10');
            $this->assertEquals('2014-01-10', date('Y-m-d', strtotime($startDateTime)));
        }

        public function testGetUserSubscribedCalendars()
        {
            $savedCalendar               = CalendarTestHelper::createSavedCalendarByName('Test Cal New', '#c05d91');
            $savedCalendarSubscription   = CalendarTestHelper::createSavedCalendarSubscription('Test Cal New', '#66367b', Yii::app()->user->userModel);
            $calendars                   = CalendarUtil::getUserSubscribedCalendars(Yii::app()->user->userModel);
            $this->assertCount(1, $calendars);
        }

        public function testGetUsedCalendarColorsByUser()
        {
            $colors = CalendarUtil::getAlreadyUsedColorsByUser(Yii::app()->user->userModel);
            $this->assertContains('#66367b', $colors);
            $this->assertContains('#315AB0', $colors);
        }

        public function testMakeCalendarItemsList()
        {
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser(Yii::app()->user->userModel);
            $content = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getMySavedCalendarsAndSelected(),
                                                           'mycalendar[]', 'mycalendar', 'saved');
            $this->assertTrue(strpos($content, 'Test Cal') > 0);
            $content = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected(),
                                                           'sharedcalendar[]', 'sharedcalendar', 'shared');
            $this->assertTrue(strpos($content, 'Test Cal New') > 0);
        }

        public function testProcessUserCalendarsAndMakeDataProviderForCombinedView()
        {
            $savedCalendars = SavedCalendar::getByName('Test Cal');
            $subscribedCalendars = CalendarUtil::getUserSubscribedCalendars(Yii::app()->user->userModel);
            $dp = CalendarUtil::processUserCalendarsAndMakeDataProviderForCombinedView($savedCalendars[0]->id, $subscribedCalendars[0]->savedcalendar->id);
            $calendarItems = $dp->getData();
            $this->assertCount(2, $calendarItems);
            $this->assertEquals('First Product', $calendarItems[0]->getTitle());
            $this->assertEquals('Second Product', $calendarItems[1]->getTitle());
        }

        public function testGetUsersSubscribedForCalendar()
        {
            $user                        = UserTestHelper::createBasicUser('sam');
            $savedCalendarSubscription   = CalendarTestHelper::createSavedCalendarSubscription('Test Cal New', '#66367b', $user);
            $savedCalendar               = SavedCalendar::getByName('Test Cal New');
            $subscribedUsers = CalendarUtil::getUsersSubscribedForCalendar($savedCalendar[0]);
            $this->assertCount(2, $subscribedUsers);
        }

        public function testSetMyCalendarColor()
        {
            $savedCalendar = CalendarTestHelper::createSavedCalendarByName('Color Cal', null);
            CalendarUtil::setMyCalendarColor($savedCalendar);
            $this->assertNotEquals('#66367b', $savedCalendar->color);
            $this->assertNotEquals('#315AB0', $savedCalendar->color);
        }

        public function testSetMySharedCalendarColor()
        {
            $user                      = User::getByUsername('sam');
            $savedCalendarSubscription = CalendarTestHelper::createSavedCalendarSubscription('Color Cal', null, $user);
            CalendarUtil::setSharedCalendarColor($savedCalendarSubscription);
            $this->assertNotEquals('#66367b', $savedCalendarSubscription->color);
        }

        public function testGetModelAttributesForSelectedModule()
        {
            $selectedAttributes = CalendarUtil::getModelAttributesForSelectedModule('ProductsModule');
            $this->assertContains('Created Date Time', $selectedAttributes);
            $this->assertContains('Modified Date Time', $selectedAttributes);
        }
    }
?>
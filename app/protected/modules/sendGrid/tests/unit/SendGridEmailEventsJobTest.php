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

    class SendGridEmailEventsJobTest extends ZurmoBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ZurmoConfigurationUtil::setByModuleName('SendGridModule', 'enableSendgrid', true);
        }

        public function setUp()
        {
            parent::setUp();
            $this->user                 = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        public function testRun()
        {
            $count  = ExternalApiEmailMessageActivity::getCount();
            $this->assertEquals(0, $count);
            $job    = new SendGridTestEmailEventsJob();
            $this->assertTrue($job->run());
            $activities  = ExternalApiEmailMessageActivity::getAll();
            $this->assertEquals(3, count($activities));
            $this->assertEquals("550 No Such User Here", trim($activities[0]->reason));
            $this->assertEquals("Bounced Address", trim($activities[1]->reason));
            $this->assertEquals("Marked as spam", $activities[2]->reason);
        }

        public function testResolveMarkingPersonPrimaryEmailAsInvalid()
        {
            $contact        = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $personId       = $contact->getClassId('Person');
            $contact->primaryEmail->emailAddress = 'test1@zurmo.com';
            $this->assertTrue($contact->save());
            $this->assertNull($contact->primaryEmail->isInvalid);
            $job            = new SendGridEmailEventsJob();
            $job->resolveMarkingPersonPrimaryEmailAsInvalid(EmailMessageActivity::TYPE_SOFT_BOUNCE, $personId);
            $this->assertNull($contact->primaryEmail->isInvalid);

            $contact        = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $personId       = $contact->getClassId('Person');
            $contact->primaryEmail->emailAddress = 'test2@zurmo.com';
            $this->assertTrue($contact->save());
            $this->assertNull($contact->primaryEmail->isInvalid);
            $job            = new SendGridEmailEventsJob();
            $job->resolveMarkingPersonPrimaryEmailAsInvalid(EmailMessageActivity::TYPE_HARD_BOUNCE, $personId);
            $this->assertEquals(1, $contact->primaryEmail->isInvalid);

            $contact        = ContactTestHelper::createContactByNameForOwner('contact 03', $this->user);
            $personId       = $contact->getClassId('Person');
            $this->assertTrue($contact->save());
            $this->assertNull($contact->primaryEmail->isInvalid);
            $job            = new SendGridEmailEventsJob();
            $job->resolveMarkingPersonPrimaryEmailAsInvalid(EmailMessageActivity::TYPE_HARD_BOUNCE, $personId);
            $this->assertNull($contact->primaryEmail->isInvalid);
        }
    }
?>
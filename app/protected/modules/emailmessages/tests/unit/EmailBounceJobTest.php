<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class EmailBounceJobTest extends ZurmoBaseTest
    {
        public static $emailHelperSendEmailThroughTransport;

        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            self::$emailHelperSendEmailThroughTransport = Yii::app()->emailHelper->sendEmailThroughTransport;

            if (EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                Yii::app()->emailHelper->outboundHost     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundHost'];
                Yii::app()->emailHelper->outboundPort     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'];
                Yii::app()->emailHelper->outboundUsername = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundUsername'];
                Yii::app()->emailHelper->outboundPassword = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPassword'];
                Yii::app()->emailHelper->outboundSecurity = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundSecurity'];
                Yii::app()->emailHelper->sendEmailThroughTransport = true;
                Yii::app()->emailHelper->setOutboundSettings();
                Yii::app()->emailHelper->init();
            }
        }

        public static function tearDownAfterClass()
        {
            static::purgeAllMessages();
            Yii::app()->emailHelper->sendEmailThroughTransport = self::$emailHelperSendEmailThroughTransport;
            parent::tearDownAfterClass();
        }

        public function setUp()
        {
            static::purgeAllMessages();
            parent::setUp();
            $this->user                 = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        public function testRunWithNoMessages()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $job    = new EmailBounceJob();
            $this->assertTrue($job->run());
            $activities    = AutoresponderItemActivity::getAll();
            $this->assertEmpty($activities);
        }

        /**
         * @depends testRunWithNoMessages
         */
        public function testRunWithOneNonBounceMessageWithNoCustomHeaders()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $bounce = static::resolveBounceObject();
            $this->assertTrue($bounce->connect());
            Yii::app()->emailHelper->sendRawEmail("Test Email",
                Yii::app()->emailHelper->outboundUsername,
                $bounce->imapUsername,
                'Test email body',
                '<strong>Test</strong> email html body',
                null,
                null,
                null,
                null,
                array()
            );
            sleep(20);
            $messages = $bounce->getMessages();
            $this->assertEquals(1, count($messages));
            $this->assertEquals("Test Email", $messages[0]->subject);
            $this->assertEquals("Test email body", trim($messages[0]->textBody));
            $this->assertEquals("<strong>Test</strong> email html body", trim($messages[0]->htmlBody));
            $this->assertEquals($bounce->imapUsername, $messages[0]->to[0]['email']);
            $this->assertEquals(Yii::app()->emailHelper->outboundUsername, $messages[0]->fromEmail);
            $job    = new EmailBounceJob();
            $this->assertTrue($job->run());
            $activities    = AutoresponderItemActivity::getAll();
            $this->assertEmpty($activities);
            $messages = $bounce->getMessages();
            $this->assertEquals(0, count($messages));
        }

        /**
         * @depends testRunWithOneNonBounceMessageWithNoCustomHeaders
         */
        public function testRunWithOneBounceMessageWithNoCustomHeaders()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $bounce = static::resolveBounceObject();
            $this->assertTrue($bounce->connect());
            $bounceTestEmailAddress =   Yii::app()->params['emailTestAccounts']['bounceTestEmailAddress'];
            $headers                = array('Return-Path' => $bounce->returnPath);
            Yii::app()->emailHelper->sendRawEmail("Test Email"  . date(DATE_RFC822),
                Yii::app()->emailHelper->outboundUsername,
                $bounceTestEmailAddress,
                'Test email body',
                '<strong>Test</strong> email html body',
                null,
                null,
                null,
                null,
                $headers
            );
            sleep(30);
            $messages = $bounce->getMessages();
            $this->assertEquals(1, count($messages));
            $this->assertEquals("Mail delivery failed: returning message to sender", $messages[0]->subject);
            $this->assertTrue(strpos($messages[0]->textBody, 'Test email body') !== false);
            $job    = new EmailBounceJob();
            $this->assertTrue($job->run());
            $activities    = AutoresponderItemActivity::getAll();
            $this->assertEmpty($activities);
            $messages = $bounce->getMessages();
            $this->assertEquals(0, count($messages));
        }

        /**
         * @depends testRunWithOneBounceMessageWithNoCustomHeaders
         */
        public function testRunWithOneBounceMessageWithCustomHeadersWeDoNotCareAbout()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $bounce = static::resolveBounceObject();
            $this->assertTrue($bounce->connect());
            $headers                = array('Return-Path'   => $bounce->returnPath,
                'headerOne'     => '1',
                'headerTwo'     => '2'
                );
            $bounceTestEmailAddress =   Yii::app()->params['emailTestAccounts']['bounceTestEmailAddress'];
            Yii::app()->emailHelper->sendRawEmail("Test Email"  . date(DATE_RFC822),
                Yii::app()->emailHelper->outboundUsername,
                $bounceTestEmailAddress,
                'Test email body',
                '<strong>Test</strong> email html body',
                null,
                null,
                null,
                null,
                $headers
            );
            sleep(30);
            $messages = $bounce->getMessages();
            $this->assertEquals(1, count($messages));
            $this->assertEquals("Mail delivery failed: returning message to sender", $messages[0]->subject);
            $this->assertTrue(strpos($messages[0]->textBody, 'Test email body') !== false);
            $this->assertTrue(strpos($messages[0]->textBody, 'headerOne: 1') !== false);
            $this->assertTrue(strpos($messages[0]->textBody, 'headerTwo: 2') !== false);
            $job    = new EmailBounceJob();
            $this->assertTrue($job->run());
            $activities    = AutoresponderItemActivity::getAll();
            $this->assertEmpty($activities);
            $messages = $bounce->getMessages();
            $this->assertEquals(0, count($messages));
        }

        /**
         * @depends testRunWithOneBounceMessageWithCustomHeadersWeDoNotCareAbout
         */
        public function testRunWithOneBounceMessageWithDesiredCustomHeaders()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $contact    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $personId                   = $contact->getClassId('Person');
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 01');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 01',
                                                                                    'text content',
                                                                                    'html content',
                                                                                    1,
                                                                                    Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    true,
                                                                                    $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 100);
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                $processDateTime,
                                                                                                $autoresponder,
                                                                                                $contact);
            $bounce = static::resolveBounceObject();
            $this->assertTrue($bounce->connect());
            $headers                = array('Return-Path'   => $bounce->returnPath,
                'zurmoItemId'           => $autoresponderItem->id,
                'zurmoItemClass'        => get_class($autoresponderItem),
                'zurmoPersonId'         => $personId,
            );
            $bounceTestEmailAddress =   Yii::app()->params['emailTestAccounts']['bounceTestEmailAddress'];
            Yii::app()->emailHelper->sendRawEmail("Test Email"  . date(DATE_RFC822),
                Yii::app()->emailHelper->outboundUsername,
                $bounceTestEmailAddress,
                'Test email body',
                '<strong>Test</strong> email html body',
                null,
                null,
                null,
                null,
                $headers
            );
            sleep(30);
            $messages = $bounce->getMessages();
            $this->assertEquals(1, count($messages));
            $this->assertEquals("Mail delivery failed: returning message to sender", $messages[0]->subject);
            $this->assertTrue(strpos($messages[0]->textBody, 'Test email body') !== false);
            $this->assertTrue(strpos($messages[0]->textBody, 'zurmoItemId: ' . $autoresponderItem->id) !== false);
            $this->assertTrue(strpos($messages[0]->textBody, 'zurmoItemClass: ' . get_class($autoresponderItem)) !== false);
            $this->assertTrue(strpos($messages[0]->textBody, 'zurmoPersonId: ' . $personId) !== false);
            $job    = new EmailBounceJob();
            $this->assertTrue($job->run());
            $activities    = AutoresponderItemActivity::getAll();
            $this->assertNotEmpty($activities);
            $this->assertCount(1, $activities);
            $activities    = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                AutoresponderItemActivity::TYPE_BOUNCE,
                                                                                $autoresponderItem->id,
                                                                                $personId);
            $this->assertNotEmpty($activities);
            $this->assertCount(1, $activities);
            $messages = $bounce->getMessages();
            $this->assertEquals(0, count($messages));
        }

        /**
         * @depends testRunWithOneBounceMessageWithDesiredCustomHeaders
         */
        public function testRunWithTwoBounceMessagesWithDesiredCustomHeaders()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $contact    = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $personId                   = $contact->getClassId('Person');
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 02');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 02',
                                                                                        'text content',
                                                                                        'html content',
                                                                                        1,
                                                                                        Autoresponder::OPERATION_SUBSCRIBE,
                                                                                        true,
                                                                                        $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 200);
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                $processDateTime,
                                                                                                $autoresponder,
                                                                                                $contact);
            $bounce = static::resolveBounceObject();
            $this->assertTrue($bounce->connect());
            $headers                = array('Return-Path'   => $bounce->returnPath,
                'zurmoItemId'           => $autoresponderItem->id,
                'zurmoItemClass'        => get_class($autoresponderItem),
                'zurmoPersonId'         => $personId,
            );
            $bounceTestEmailAddress =   Yii::app()->params['emailTestAccounts']['bounceTestEmailAddress'];
            Yii::app()->emailHelper->sendRawEmail("Test Email"  . date(DATE_RFC822),
                Yii::app()->emailHelper->outboundUsername,
                $bounceTestEmailAddress,
                'Test email body',
                '<strong>Test</strong> email html body',
                null,
                null,
                null,
                null,
                $headers
            );
            Yii::app()->emailHelper->sendRawEmail("Test Email"  . date(DATE_RFC822),
                Yii::app()->emailHelper->outboundUsername,
                $bounceTestEmailAddress,
                'Test email body',
                '<strong>Test</strong> email html body',
                null,
                null,
                null,
                null,
                $headers
            );
            sleep(30);
            $messages = $bounce->getMessages();
            $this->assertEquals(2, count($messages));
            for ($i = 0; $i < 2; $i++)
            {
                $this->assertEquals("Mail delivery failed: returning message to sender", $messages[$i]->subject);
                $this->assertTrue(strpos($messages[$i]->textBody, 'Test email body') !== false);
                $this->assertTrue(strpos($messages[$i]->textBody, 'zurmoItemId: ' . $autoresponderItem->id) !== false);
                $this->assertTrue(strpos($messages[$i]->textBody, 'zurmoItemClass: ' .
                                                                            get_class($autoresponderItem)) !== false);
                $this->assertTrue(strpos($messages[$i]->textBody, 'zurmoPersonId: ' . $personId) !== false);
            }
            $job    = new EmailBounceJob();
            $this->assertTrue($job->run());
            $activities    = AutoresponderItemActivity::getAll();
            $this->assertNotEmpty($activities);
            $this->assertCount(2, $activities);
            $activities    = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                AutoresponderItemActivity::TYPE_BOUNCE,
                                                                                $autoresponderItem->id,
                                                                                $personId);
            $this->assertNotEmpty($activities);
            $this->assertCount(1, $activities);
            $this->assertEquals(2, $activities[0]->quantity);
            $messages = $bounce->getMessages();
            $this->assertEquals(0, count($messages));
        }

        protected static function purgeAllMessages()
        {
            $bounce = static::resolveBounceObject();
            //static::assertNotNull($bounce);
            //static::assertTrue($bounce->connect());
            //static::assertTrue($bounce->deleteMessages(true));
        }

        protected static function resolveBounceObject()
        {
            $bounce = new ZurmoBounce();
            $bounce->imapHost        = Yii::app()->params['emailTestAccounts']['bounceImapSettings']['imapHost'];
            $bounce->imapUsername    = Yii::app()->params['emailTestAccounts']['bounceImapSettings']['imapUsername'];
            $bounce->imapPassword    = Yii::app()->params['emailTestAccounts']['bounceImapSettings']['imapPassword'];
            $bounce->imapPort        = Yii::app()->params['emailTestAccounts']['bounceImapSettings']['imapPort'];
            $bounce->imapSSL         = Yii::app()->params['emailTestAccounts']['bounceImapSettings']['imapSSL'];
            $bounce->imapFolder      = Yii::app()->params['emailTestAccounts']['bounceImapSettings']['imapFolder'];
            $bounce->returnPath      = Yii::app()->params['emailTestAccounts']['bounceImapSettings']['returnPath'];
            $bounce->setInboundSettings();
            $bounce->init();
            return $bounce;
        }
    }
?>
<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/
    class AutoresponderItemsUtilTest extends ZurmoBaseTest
    {
        // We don't need to add separate tests for tracking scenarios here because we have already gained more than
        //  sufficient coverage in AutoresponderItemActivityUtilTest and EmailMessageActivityUtilTest for those.
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user                 = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        /**
         * @expectedException NotFoundException
         */
        public function testProcessDueAutoresponderItemThrowsExceptionWhenNoContactIsAvailable()
        {
            $autoresponderItem          = new AutoresponderItem();
            AutoresponderItemsUtil::processDueItem($autoresponderItem);
        }

        /**
         * @depends testProcessDueAutoresponderItemThrowsExceptionWhenNoContactIsAvailable
         * @expectedException NotSupportedException
         * @expectedExceptionMessage Provided content contains few invalid merge tags
         */
        public function testProcessDueAutoresponderItemThrowsExceptionWhenContentHasInvalidMergeTags()
        {
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList              = MarketingListTestHelper::populateMarketingListByName('marketingList 01');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 01',
                                                                                    '[[TEXT^CONTENT]]',
                                                                                    '[[HTML^CONTENT]]',
                                                                                    1,
                                                                                    Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    true,
                                                                                    $marketingList,
                                                                                    false);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                $processDateTime,
                                                                                                $autoresponder,
                                                                                                $contact);
            AutoresponderItemsUtil::processDueItem($autoresponderItem);
        }

        /**
         * @depends testProcessDueAutoresponderItemThrowsExceptionWhenContentHasInvalidMergeTags
         */
        public function testProcessDueAutoresponderItemDoesNotThrowExceptionWhenContactHasNoPrimaryEmail()
        {
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 02');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 02',
                                                                                    'text content',
                                                                                    'html content',
                                                                                    1,
                                                                                    Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    false,
                                                                                    $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                $processDateTime,
                                                                                                $autoresponder,
                                                                                                $contact);
            AutoresponderItemsUtil::processDueItem($autoresponderItem);
            $this->assertEquals(1, $autoresponderItem->processed);
            $emailMessage               = $autoresponderItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertNull($emailMessage->subject);
            $this->assertNull($emailMessage->content->textContent);
            $this->assertNull($emailMessage->content->htmlContent);
            $this->assertNull($emailMessage->sender->fromAddress);
            $this->assertNull($emailMessage->sender->fromName);
            $this->assertEquals(0, $emailMessage->recipients->count());
        }

        /**
         * @depends testProcessDueAutoresponderItemDoesNotThrowExceptionWhenContactHasNoPrimaryEmail
         */
        public function testProcessDueAutoresponderItemDoesNotThrowExceptionWhenContactHasPrimaryEmail()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 03', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 03');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 03',
                                                                                    'text content',
                                                                                    'html content',
                                                                                    1,
                                                                                    Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    true,
                                                                                    $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                $processDateTime,
                                                                                                $autoresponder,
                                                                                                $contact);
            AutoresponderItemsUtil::processDueItem($autoresponderItem);
            $this->assertEquals(1, $autoresponderItem->processed);
            $emailMessage               = $autoresponderItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertEquals($autoresponder->subject, $emailMessage->subject);
            $this->assertEquals($autoresponder->textContent, $emailMessage->content->textContent);
            $this->assertTrue(strpos($emailMessage->content->htmlContent, $autoresponder->htmlContent) === 0);
            $userToSendMessagesFrom     = Yii::app()->emailHelper->getUserToSendNotificationsAs();
            $defaultFromAddress         = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
            $defaultFromName            = strval($userToSendMessagesFrom);
            $this->assertEquals($defaultFromAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals($defaultFromName, $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact, $recipients[0]->personOrAccount);
        }

        /**
         * @depends testProcessDueAutoresponderItemDoesNotThrowExceptionWhenContactHasNoPrimaryEmail
         */
        public function testProcessDueAutoresponderItemWithCustomFromAddressAndFromName()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 04', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 04',
                                                                                            'description',
                                                                                            'CustomFromName',
                                                                                            'custom@from.com');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 04',
                                                                                    'text content',
                                                                                    'html content',
                                                                                    1,
                                                                                    Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    false,
                                                                                    $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                $processDateTime,
                                                                                                $autoresponder,
                                                                                                $contact);
            AutoresponderItemsUtil::processDueItem($autoresponderItem);
            $this->assertEquals(1, $autoresponderItem->processed);
            $emailMessage               = $autoresponderItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertEquals($autoresponder->subject, $emailMessage->subject);
            $this->assertEquals($autoresponder->textContent, $emailMessage->content->textContent);
            $this->assertEquals($autoresponder->htmlContent, $emailMessage->content->htmlContent);
            $this->assertEquals($marketingList->fromAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals($marketingList->fromName, $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact, $recipients[0]->personOrAccount);
        }

        /**
         * @depends testProcessDueAutoresponderItemWithCustomFromAddressAndFromName
         */
        public function testProcessDueAutoresponderItemWithValidMergeTags()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 05', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 05',
                                                                                            'description',
                                                                                            'CustomFromName',
                                                                                            'custom@from.com');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 05',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                1,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                true,
                                                                                $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                $processDateTime,
                                                                                                $autoresponder,
                                                                                                $contact);
            AutoresponderItemsUtil::processDueItem($autoresponderItem);
            $this->assertEquals(1, $autoresponderItem->processed);
            $emailMessage               = $autoresponderItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertEquals($autoresponder->subject, $emailMessage->subject);
            $this->assertNotEquals($autoresponder->textContent, $emailMessage->content->textContent);
            $this->assertNotEquals($autoresponder->htmlContent, $emailMessage->content->htmlContent);
            $this->assertEquals('Dr. contact 05 contact 05son', $emailMessage->content->textContent);
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '<b>contact 05son</b>, contact 05') === 0);
            $this->assertEquals($marketingList->fromAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals($marketingList->fromName, $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact, $recipients[0]->personOrAccount);
        }

        /**
         * @depends testProcessDueAutoresponderItemWithValidMergeTags
         */
        public function testProcessDueAutoresponderItemWithAttachments()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 06', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 06',
                                                                                                'description',
                                                                                                'CustomFromName',
                                                                                                'custom@from.com');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 06',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                1,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                true,
                                                                                $marketingList);
            $fileNames              = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            $files                  = array();
            foreach ($fileNames as $index => $fileName)
            {
                $files[$index]['name']      = $fileName;
                $files[$index]['path']      = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files') .
                                                                        DIRECTORY_SEPARATOR . $files[$index]['name'];
                $files[$index]['type']      = ZurmoFileHelper::getMimeType($files[$index]['path']);
                $files[$index]['size']      = filesize($files[$index]['path']);
                $files[$index]['contents']  = file_get_contents($files[$index]['path']);
                $fileContent                = new FileContent();
                $fileContent->content       = $files[$index]['contents'];
                $file                       = new FileModel();
                $file->fileContent          = $fileContent;
                $file->name                 = $files[$index]['name'];
                $file->type                 = $files[$index]['type'];
                $file->size                 = $files[$index]['size'];
                $this->assertTrue($file->save());
                $autoresponder->files->add($file);
            }
            $this->assertTrue($autoresponder->save());
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                    $processDateTime,
                                                                                                    $autoresponder,
                                                                                                    $contact);
            AutoresponderItemsUtil::processDueItem($autoresponderItem);
            $this->assertEquals(1, $autoresponderItem->processed);
            $emailMessage               = $autoresponderItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertEquals($autoresponder->subject, $emailMessage->subject);
            $this->assertNotEquals($autoresponder->textContent, $emailMessage->content->textContent);
            $this->assertNotEquals($autoresponder->htmlContent, $emailMessage->content->htmlContent);
            $this->assertEquals('Dr. contact 06 contact 06son', $emailMessage->content->textContent);
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '<b>contact 06son</b>, contact 06') === 0);
            $this->assertEquals($marketingList->fromAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals($marketingList->fromName, $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact, $recipients[0]->personOrAccount);
            $this->assertNotEmpty($emailMessage->files);
            $this->assertCount(count($files), $emailMessage->files);
            foreach ($files as $index => $file)
            {
                $this->assertEquals($files[$index]['name'], $emailMessage->files[$index]->name);
                $this->assertEquals($files[$index]['type'], $emailMessage->files[$index]->type);
                $this->assertEquals($files[$index]['size'], $emailMessage->files[$index]->size);
                $this->assertEquals($files[$index]['contents'], $emailMessage->files[$index]->fileContent->content);
            }
        }
    }
?>
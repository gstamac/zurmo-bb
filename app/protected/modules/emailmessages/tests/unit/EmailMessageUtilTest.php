<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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

    class EmailMessageUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();

            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');

            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
            SecurityTestHelper::createAccounts();
            SecurityTestHelper::createRoles();

            $billy = User::getByUsername('billy');
            EmailMessageTestHelper::createEmailAccount($billy);
            $billy->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $billy->setRight('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS);
            $billy->setRight('ContactsModule', ContactsModule::RIGHT_DELETE_CONTACTS);

            $everyone = Group::getByName('Everyone');
            $saved = $everyone->save();

            $contact = ContactTestHelper::createContactByNameForOwner('sally', Yii::app()->user->userModel);
            $contact->primaryEmail = new Email();
            $contact->primaryEmail->emailAddress = 'sally@zurmoland.com';
            $contact->addPermissions($billy, Permission::READ);
            $contact->addPermissions($billy, Permission::WRITE);
            $contact->save();

            ReadPermissionsOptimizationUtil::rebuild();

            if (EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                Yii::app()->emailHelper->outboundHost     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundHost'];
                Yii::app()->emailHelper->outboundPort     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'];
                Yii::app()->emailHelper->outboundUsername = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundUsername'];
                Yii::app()->emailHelper->outboundPassword = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPassword'];
                Yii::app()->emailHelper->sendEmailThroughTransport = true;
                Yii::app()->emailHelper->setOutboundSettings();
                Yii::app()->emailHelper->init();

                Yii::app()->imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
                Yii::app()->imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
                Yii::app()->imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
                Yii::app()->imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
                Yii::app()->imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
                Yii::app()->imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];
                Yii::app()->imap->setInboundSettings();
                Yii::app()->imap->init();
            }
        }

        public function testResolveEmailMessageFromPostData()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            //Test with no users/person in recipients
            $emailMessage     = new EmailMessage();
            $emailMessageForm = new CreateEmailMessageForm($emailMessage);
            $postVariableName = get_class($emailMessageForm);
            $postData = array($postVariableName => array ('recipientsData' => array('to'  => 'a@zurmo.com,b@zurmo.com',
                                                                          'cc'  => 'c@zurmo.com,d@zurmo.com',
                                                                          'bcc' => 'e@zurmo.com,f@zurmo.com'),
                                                    'subject' => 'Test Email From Post',
                                                    'content' => array('htmlContent' => 'This is a test email')
                ));
            $emailMessageForm = EmailMessageUtil::resolveEmailMessageFromPostData($postData, $emailMessageForm, $billy);
            //Message should have 6 recipients 2 of each type
            $this->assertEquals('6', count($emailMessageForm->getModel()->recipients));
            $recipients = $emailMessageForm->getModel()->recipients;
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[1]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_CC, $recipients[2]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_CC, $recipients[3]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_BCC, $recipients[4]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_BCC, $recipients[5]->type);
            $this->assertEquals('a@zurmo.com', $recipients[0]->toAddress);
            $this->assertEquals('b@zurmo.com', $recipients[1]->toAddress);
            $this->assertEquals('c@zurmo.com', $recipients[2]->toAddress);
            $this->assertEquals('d@zurmo.com', $recipients[3]->toAddress);
            $this->assertEquals('e@zurmo.com', $recipients[4]->toAddress);
            $this->assertEquals('f@zurmo.com', $recipients[5]->toAddress);
            $this->assertEquals('', $recipients[0]->toName);
            $this->assertEquals('', $recipients[1]->toName);
            $this->assertEquals('', $recipients[2]->toName);
            $this->assertEquals('', $recipients[3]->toName);
            $this->assertEquals('', $recipients[4]->toName);
            $this->assertEquals('', $recipients[5]->toName);
            //Recipients are not personOrAccount
            $this->assertLessThan(0, $recipients[0]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[1]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[2]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[3]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[4]->personOrAccount->id);
            $this->assertLessThan(0, $recipients[5]->personOrAccount->id);
            //The message should go to the default outbox folder
            $this->assertEquals(EmailFolder::getDefaultOutboxName(), $emailMessageForm->folder->name);
            $this->assertEquals(EmailFolder::TYPE_OUTBOX, $emailMessageForm->folder->type);
            unset($emailMessage);
            unset($recipients);
            unset($postData);
            unset($emailMessage);
            unset($emailMessageForm);

            //Test with null in cc/bcc
            $emailMessage     = new EmailMessage();
            $emailMessageForm = new CreateEmailMessageForm($emailMessage);
            $postVariableName = get_class($emailMessageForm);
            $postData = array($postVariableName => array ('recipientsData' => array('to'  => 'a@zurmo.com',
                                                                          'cc'  => null,
                                                                          'bcc' => null),
                                                    'subject' => 'Test Email From Post',
                                                    'content' => array('htmlContent' => 'This is a test email')
                ));
            $emailMessageForm = EmailMessageUtil::resolveEmailMessageFromPostData($postData, $emailMessageForm, $billy);
            $this->assertEquals('1', count($emailMessageForm->getModel()->recipients));
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessageForm->getModel()->recipients[0]->type);
            $this->assertEquals('a@zurmo.com', $emailMessageForm->getModel()->recipients[0]->toAddress);
            unset($emailMessage);
            unset($recipients);
            unset($postData);
            unset($emailMessage);
            unset($emailMessageForm);

            //Test with with contacts in recipients
            $emailMessage     = new EmailMessage();
            $emailMessageForm = new CreateEmailMessageForm($emailMessage);
            $postVariableName = get_class($emailMessageForm);
            $postData = array($postVariableName => array ('recipientsData' => array('to'  => 'sally@zurmoland.com',
                                                                          'cc'  => null,
                                                                          'bcc' => null),
                                                    'subject' => 'Test Email From Post',
                                                    'content' => array('htmlContent' => 'This is a test email')
                ));
            $emailMessageForm = EmailMessageUtil::resolveEmailMessageFromPostData($postData, $emailMessageForm, $billy);
            $this->assertEquals('1', count($emailMessageForm->getModel()->recipients));
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessageForm->getModel()->recipients[0]->type);
            $this->assertEquals('sally@zurmoland.com', $emailMessageForm->getModel()->recipients[0]->toAddress);
            $contacts = Contact::getByName('sally sallyson');
            //TODO: check why this is not working
            //$this->assertEquals($emailMessageForm->getModel()->recipients[0]->personOrAccount->getClassId('Item'), $contacts[0]->getClassId('Item'));
            unset($emailMessage);
            unset($recipients);
            unset($postData);
            unset($emailMessage);
            unset($emailMessageForm);

            //Test with attachments
            $email = new Email();
            $filesIds = array();
            $fileDocx = ZurmoTestHelper::createFileModel('testNote.txt', 'FileModel');
            $filesIds[] = $fileDocx->id;
            $fileTxt = ZurmoTestHelper::createFileModel('testImage.png', 'FileModel');
            $filesIds[] = $fileTxt->id;
            $emailMessage     = new EmailMessage();
            $emailMessageForm = new CreateEmailMessageForm($emailMessage);
            $postVariableName = get_class($emailMessageForm);
            $postData = array($postVariableName => array ('recipientsData' => array('to'  => 'a@zurmo.com',
                                                                          'cc'  => null,
                                                                          'bcc' => null),
                                                    'subject' => 'Test Email From Post',
                                                    'content' => array('htmlContent' => 'This is a test email')
                                             ),
                           'filesIds'     => $filesIds,
                );
            $emailMessageForm = EmailMessageUtil::resolveEmailMessageFromPostData($postData, $emailMessageForm, $billy);
            $this->assertEquals(2, count($emailMessageForm->getModel()->files));
        }

        public function testAttachFilesToMessage()
        {
            $billy = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $filesIds = array();
            $fileTxt = ZurmoTestHelper::createFileModel('testNote.txt', 'FileModel');
            $filesIds[] = $fileTxt->id;
            $filePng = ZurmoTestHelper::createFileModel('testImage.png', 'FileModel');
            $filesIds[] = $filePng->id;
            $fileZip = ZurmoTestHelper::createFileModel('testZip.zip', 'FileModel');
            $filesIds[] = $fileZip->id;
            $filePdf = ZurmoTestHelper::createFileModel('testPDF.pdf', 'FileModel');
            $filesIds[] = $filePdf->id;
            $emailMessage = new EmailMessage();
            EmailMessageUtil::attachFilesToMessage($filesIds, $emailMessage);
            $this->assertEquals('4', count($emailMessage->files));
        }

        public function testAttachRecipientsToMessage()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $emailMessage = new EmailMessage();
            //Attach non personOrAccount recipient
            EmailMessageUtil::attachRecipientsToMessage(array('a@zurmo.com', 'b@zurmo.com', 'c@zurmo.com'), $emailMessage, EmailMessageRecipient::TYPE_TO);
            $this->assertEquals('3', count($emailMessage->recipients));
            $this->assertLessThan(0, $emailMessage->recipients[0]->personOrAccount->id);
            $this->assertLessThan(0, $emailMessage->recipients[1]->personOrAccount->id);
            $this->assertLessThan(0, $emailMessage->recipients[2]->personOrAccount->id);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[0]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[1]->type);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $emailMessage->recipients[2]->type);
            //Attach personOrAccount recipient
            $sally = Contact::getByName('Sally');
            EmailMessageUtil::attachRecipientsToMessage(array('sally@zurmoland.com'), $emailMessage, EmailMessageRecipient::TYPE_BCC);
            $this->assertEquals('4', count($emailMessage->recipients));
            //TODO: Check what is happening with the contact creation
            //$this->assertEquals($emailMessage->recipients[3]->personOrAccount->id, $sally->id);
            $this->assertEquals(EmailMessageRecipient::TYPE_BCC, $emailMessage->recipients[3]->type);
            //Attach an empty email
            EmailMessageUtil::attachRecipientsToMessage(array(''), $emailMessage, EmailMessageRecipient::TYPE_CC);
            $this->assertEquals('4', count($emailMessage->recipients));
        }

        public function testRenderEmailAddressAsMailToOrModalLinkStringContent()
        {
            $billy   = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $emailAddress = "a@zurmo.com";
            $account = new Account();
            $content = EmailMessageUtil::renderEmailAddressAsMailToOrModalLinkStringContent($emailAddress, $account);
            $this->assertEquals('<a href="mailto:a@zurmo.com">a@zurmo.com</a>', $content);
            $billy->setRight('EmailMessagesModule', EmailMessagesModule::RIGHT_ACCESS_CONFIGURATION);
            $billy->setRight('EmailMessagesModule', EmailMessagesModule::RIGHT_ACCESS_EMAIL_MESSAGES);
            $billy->setRight('EmailMessagesModule', EmailMessagesModule::RIGHT_CREATE_EMAIL_MESSAGES);
            $billy->save();
            $content = EmailMessageUtil::renderEmailAddressAsMailToOrModalLinkStringContent($emailAddress, $account);
            $this->assertEquals('<a href="mailto:a@zurmo.com">a@zurmo.com</a>', $content);
            //Only if the model is not Account and User as right he can see the email modal link
            $contact = new Contact();
            $content = EmailMessageUtil::renderEmailAddressAsMailToOrModalLinkStringContent($emailAddress, $contact);
            $this->assertEquals('<a href="#" id="yt0">a@zurmo.com</a>', $content);
        }
    }
?>


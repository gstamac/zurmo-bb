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

     Yii::import('ext.sendgrid.lib.SendGrid.Email');
     Yii::import('ext.sendgrid.lib.Smtpapi.Header');

    /**
     * Class for Zurmo specific sendgrid functionality.
     */
    class ZurmoSendGridMailer extends CComponent
    {
        protected $emailHelper;

        protected $fromUser;

        protected $toAddress;

        protected $fromUserEmailData;

        public function __construct(SendGridEmailHelper $emailHelper, User $userToSendMessagesFrom, $toAddress)
        {
            SendGrid::register_autoloader();
            Smtpapi::register_autoloader();
            $this->emailHelper = $emailHelper;
            $this->fromUser    = $userToSendMessagesFrom;
            $this->toAddress   = $toAddress;
        }

        /**
         * Send a test email from user.  Can use to determine if the SMTP settings are configured correctly.
         * @return EmailMessage
         */
        public function sendTestEmailFromUser()
        {
            $this->fromUserEmailData = array(
                'address'   => $this->emailHelper->resolveFromAddressByUser($this->fromUser),
                'name'      => strval($this->fromUser),
            );
            return $this->sendTestEmail();
        }

        /**
         * Send a test email.
         * @return EmailMessage
         */
        public function sendTestEmail()
        {
            $emailMessage              = EmailMessageHelper::processAndCreateEmailMessage($this->fromUserEmailData, $this->toAddress);
            $validated                 = $emailMessage->validate();
            if ($validated)
            {
                $this->sendImmediately($emailMessage);
            }
            return $emailMessage;
        }

        /**
         * Use this method to send immediately, instead of putting an email in a queue to be processed by a scheduled
         * job.
         * @param EmailMessage $emailMessage
         * @throws NotSupportedException - if the emailMessage does not properly save.
         * @throws FailedToSaveModelException
         * @return null
         */
        public function sendImmediately(EmailMessage $emailMessage)
        {
            if ($emailMessage->folder->type == EmailFolder::TYPE_SENT)
            {
                throw new NotSupportedException();
            }
            $mailer           = $this->getOutboundMailer();
            $this->populateMailer($mailer, $emailMessage);
            $this->sendEmail($mailer, $emailMessage);
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
        }

        public function sendEmail()
        {
            $sendgrid = new SendGrid($this->emailHelper->apiUsername, $this->emailHelper->apiPassword, array("turn_off_ssl_verification" => true));
            $email    = new SendGrid\Email();
            $email->addTo($this->toAddress)->
                   setFrom($from['address'])->
                   setFromName($from['name'])->
                   setSubject('[sendgrid-php-example] Owl named %yourname%')->
                   setText('Owl are you doing?')->
                   setHtml('<strong>%how% are you doing?</strong>')->
                   addSubstitution("%yourname%", array("Mr. Owl"))->
                   addSubstitution("%how%", array("Owl"))->
                   addHeader('X-Sent-Using', 'SendGrid-API')->
                   addHeader('X-Transport', 'web');

            $response = $sendgrid->send($email);
        }
    }
?>
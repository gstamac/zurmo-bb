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

    class SendGridUtil
    {
        /**
         * Send Test Message
         * @param SendGridWebApiConfigurationForm $configurationForm
         * @param string $fromNameToSendMessagesFrom
         * @param string $fromAddressToSendMessagesFrom
         */
        public static function sendTestMessage($configurationForm,
                                               $fromNameToSendMessagesFrom = null,
                                               $fromAddressToSendMessagesFrom = null)
        {
            if ($configurationForm->aTestToAddress != null)
            {
                $sendGridEmailAccount         = new SendGridEmailAccount();
                $sendGridEmailAccount->apiUsername     = $configurationForm->username;
                $sendGridEmailAccount->apiPassword     = ZurmoPasswordSecurityUtil::encrypt($configurationForm->password);
                $isUser = false;
                if ($fromNameToSendMessagesFrom != null && $fromAddressToSendMessagesFrom != null)
                {
                    $isUser                 = true;
                    $from = array(
                        'name'      => $fromNameToSendMessagesFrom,
                        'address'   => $fromAddressToSendMessagesFrom
                    );
                }
                else
                {
                    $user                   = BaseControlUserConfigUtil::getUserToRunAs();
                    $userToSendMessagesFrom = User::getById((int)$user->id);
                    $from = array(
                        'name'      => strval($userToSendMessagesFrom),
                        'address'   => Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom)
                    );
                }
                $emailMessage = EmailMessageHelper::processAndCreateEmailMessage($from, $configurationForm->aTestToAddress);
                $mailer       = new ZurmoSendGridMailer($emailMessage, $sendGridEmailAccount);
                $emailMessage = $mailer->sendTestEmail($isUser);
                $messageContent  = EmailHelper::prepareMessageContent($emailMessage);
            }
            else
            {
                $messageContent = Zurmo::t('EmailMessagesModule', 'A test email address must be entered before you can send a test email.') . "\n";
            }
            return $messageContent;
        }

        /**
         * Register event webhook url script.
         * @param string $id
         * @param string $baseUrl
         */
        public static function registerEventWebhookUrlScript($id, $baseUrl)
        {
            $script = "$('#{$id}').keyup(function()
                                         {
                                            var name = $(this).val();
                                            var url  = '{$baseUrl}?username=' + name;
                                            $('#eventWebhookUrl').html(url);
                                         });
                                        ";
            Yii::app()->clientScript->registerScript('eventWebhookUrlScript', $script);
        }

        /**
         * Render webhook url on form.
         * @param CModel $model
         * @return string
         */
        public static function renderEventWebHookUrlOnForm($model)
        {
            $url   = Yii::app()->createAbsoluteUrl('sendGrid/external/writeLog', array('username' => $model->username));
            $url   = ZurmoHtml::tag('div', array('id' => 'eventWebhookUrl'), $url);
            $label = ZurmoHtml::label(Zurmo::t('SendGridModule', 'Event Webhook Url'), 'eventWebhookUrl');
            return ZurmoHtml::tag('div', array('class' => 'panel'), '<table class="form-fields"><tr><th>' . $label . '</th>'
                                                                    . '<td colspan="1">' . $url . '</td></tr></table>');
        }
    }
?>
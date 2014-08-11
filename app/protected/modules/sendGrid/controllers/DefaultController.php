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

    /**
     * Sendgrid default controller for configuration view.
     */
    class SendGridDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            return array(
                array(
                      ZurmoBaseController::RIGHTS_FILTER_PATH . ' + index, ConfigurationView, configurationEditOutbound',
                      'moduleClassName'   => 'SendGridModule',
                      'rightName'         => SendGridModule::RIGHT_ACCESS_SENDGRID_ADMINISTRATION,
                ),
            );
        }

        public function actionIndex()
        {
            $this->actionConfigurationView();
        }

        /**
         * Admin configuration action for entering the google map api key.
         */
        public function actionConfigurationView()
        {
            $breadCrumbLinks = array(
                Zurmo::t('SendGridModule', 'SendGrid Configuration'),
            );
            $configurationForm                 = new SendGridConfigurationForm();
            $configurationForm->enableSendgrid = (bool)ZurmoConfigurationUtil::getByModuleName('SendGridModule', 'enableSendgrid');
            $postVariableName                  = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $_POST[$postVariableName]['enableSendgrid'] = (bool)$_POST[$postVariableName]['enableSendgrid'];
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    ZurmoConfigurationUtil::setByModuleName('SendGridModule', 'enableSendgrid', $configurationForm->enableSendgrid);
                    Yii::app()->user->setFlash('notification',
                                                Zurmo::t('SendGridModule', 'Sendgrid configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('sendGrid/default/configurationView'));
                }
            }
            $editView = new SendGridConfigurationView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                        makeViewWithBreadcrumbsForCurrentUser($this, $editView, $breadCrumbLinks, 'PluginsBreadCrumbView'));
            echo $view->render();
        }

        public function actionConfigurationEditOutbound()
        {
            $breadCrumbLinks = array(
                Zurmo::t('SendGridModule', 'WebAPI Configuration')
            );
            $configurationForm  = SendGridWebApiConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    SendGridWebApiConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    if (!Yii::app()->user->hasFlash('notification'))
                    {
                        Yii::app()->user->setFlash('notification',
                            Zurmo::t('SendGridModule', 'Sendgrid configuration saved successfully.')
                        );
                    }
                    $this->redirect(Yii::app()->createUrl('sendGrid/default/configurationEditOutbound'));
                }
            }
            $editView = new SendGridConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::makeViewWithBreadcrumbsForCurrentUser(
                    $this, $editView, $breadCrumbLinks, 'SettingsBreadCrumbView'));
            echo $view->render();
        }

        /**
         * Assumes before calling this, the sendgrid settings have been validated in the form.
         * Todo: When new user interface is complete, this will be re-worked to be on page instead of modal.
         */
        public function actionSendTestMessage()
        {
            $configurationForm = SendGridWebApiConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]) || (isset($_POST['UserSendGridConfigurationForm'])))
            {
                if (isset($_POST[$postVariableName]))
                {
                    $configurationForm->setAttributes($_POST[$postVariableName]);
                }
                else
                {
                    /*$configurationForm->host            = $_POST['UserEmailConfigurationForm']['outboundHost'];
                    $configurationForm->port            = $_POST['UserEmailConfigurationForm']['outboundPort'];
                    $configurationForm->username        = $_POST['UserEmailConfigurationForm']['outboundUsername'];
                    $configurationForm->password        = $_POST['UserEmailConfigurationForm']['outboundPassword'];
                    $configurationForm->security        = $_POST['UserEmailConfigurationForm']['outboundSecurity'];
                    $configurationForm->aTestToAddress  = $_POST['UserEmailConfigurationForm']['aTestToAddress'];
                    $fromNameToSendMessagesFrom         = $_POST['UserEmailConfigurationForm']['fromName'];
                    $fromAddressToSendMessagesFrom      = $_POST['UserEmailConfigurationForm']['fromAddress'];*/
                }
                if ($configurationForm->aTestToAddress != null)
                {
                    $emailHelper = new SendGridEmailHelper();
                    $emailHelper->loadDefaultFromAndToAddresses();
                    $emailHelper->apiUsername     = $configurationForm->username;
                    $emailHelper->apiPassword     = $configurationForm->password;
                    if (isset($fromNameToSendMessagesFrom) && isset($fromAddressToSendMessagesFrom))
                    {
                        $from = array(
                            'name'      => $fromNameToSendMessagesFrom,
                            'address'   => $fromAddressToSendMessagesFrom
                        );
                        $emailMessage = ZurmoSendGridMailer::sendTestEmail($emailHelper, $from,
                                                                      $configurationForm->aTestToAddress);
                    }
                    else
                    {
                        $user                   = BaseControlUserConfigUtil::getUserToRunAs();
                        $userToSendMessagesFrom = User::getById((int)$user->id);
                        $emailMessage           = ZurmoSendGridMailer::sendTestEmailFromUser($emailHelper, $userToSendMessagesFrom,
                                                                      $configurationForm->aTestToAddress);
                    }
                    $messageContent  = null;
                    if (!($emailMessage->hasErrors() || $emailMessage->hasSendError()))
                    {
                        $messageContent .= Zurmo::t('EmailMessagesModule', 'Message successfully sent') . "\n";
                    }
                    else
                    {
                        $messageContent .= Zurmo::t('EmailMessagesModule', 'Message failed to send') . "\n";
                        if ($emailMessage->hasSendError())
                        {
                            $messageContent .= $emailMessage->error     . "\n";
                        }
                        else
                        {
                            //todo: refactor to use ZurmoHtml::errorSummary after this supports that method
                            //todo: supports nested messages better.
                            $errors = $emailMessage->getErrors();
                            foreach ($errors as $attributeNameWithErrors)
                            {
                                foreach ($attributeNameWithErrors as $attributeError)
                                {
                                    if (is_array($attributeError))
                                    {
                                        foreach ($attributeError as $nestedAttributeError)
                                        {
                                            $messageContent .= reset($nestedAttributeError) . "\n";
                                        }
                                    }
                                    else
                                    {
                                        $messageContent .= reset($attributeError) . "\n";
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    $messageContent = Zurmo::t('EmailMessagesModule', 'A test email address must be entered before you can send a test email.') . "\n";
                }
                Yii::app()->getClientScript()->setToAjaxMode();
                $messageView = new TestConnectionView($messageContent);
                $view = new ModalView($this, $messageView);
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>
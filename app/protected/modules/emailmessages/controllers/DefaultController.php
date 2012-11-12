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

    /**
     * Controller for managing configuration actions for email messages
     */
    class EmailMessagesDefaultController extends ZurmoBaseController
    {
        const USER_EMAIL_CONFIGURATION_FILTER_PATH =
              'application.modules.emailMessages.controllers.filters.UserEmailConfigurationCheckControllerFilter';

        public function filters()
        {
            $moduleClassName = get_class($this->getModule());
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + configurationEdit, configurationEditOutbound, configurationEditArchiving',
                    'moduleClassName' => $moduleClassName,
                    'rightName'       => EmailMessagesModule::RIGHT_ACCESS_CONFIGURATION,
               ),
               array(
                    self::getRightsFilterPath() . ' + createEmailMessage',
                    'moduleClassName' => $moduleClassName,
                    'rightName' => $moduleClassName::getCreateRight(),
                ),
                array(self::USER_EMAIL_CONFIGURATION_FILTER_PATH . ' + createEmailMessage',
                     'controller' => $this,
                )
            );
        }

        public function actionDetails($id, $redirectUrl = null)
        {
            $emailMessage          = EmailMessage::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailMessage);
            $detailsView           = new EmailMessageDetailsView($this->getId(), $this->getModule()->getId(), $emailMessage);
            $view              = new EmailMessagesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsView));
            echo $view->render();
        }

        public function actionConfigurationEdit()
        {
            $view = new ConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                                  makeStandardViewForCurrentUser($this, new EmailConfigurationListView()));
            echo $view->render();
        }

        public function actionConfigurationEditOutbound()
        {
            $configurationForm = EmailSmtpConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    EmailSmtpConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Email configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new EmailSmtpConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionConfigurationEditArchiving()
        {
            $configurationForm = EmailArchivingConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    EmailArchivingConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Email configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new EmailArchivingConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        /**
         * Assumes before calling this, the outbound settings have been validated in the form.
         * Todo: When new user interface is complete, this will be re-worked to be on page instead of modal.
         */
        public function actionSendTestMessage()
        {
            $configurationForm = EmailSmtpConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]) || (isset($_POST['UserEmailConfigurationForm'])))
            {
                if (isset($_POST[$postVariableName]))
                {
                    $configurationForm->setAttributes($_POST[$postVariableName]);
                }
                else
                {
                    $configurationForm->host            = $_POST['UserEmailConfigurationForm']['outboundHost'];
                    $configurationForm->port            = $_POST['UserEmailConfigurationForm']['outboundPort'];
                    $configurationForm->username        = $_POST['UserEmailConfigurationForm']['outboundUsername'];
                    $configurationForm->password        = $_POST['UserEmailConfigurationForm']['outboundPassword'];
                    $configurationForm->security        = $_POST['UserEmailConfigurationForm']['outboundSecurity'];
                    $configurationForm->aTestToAddress  = $_POST['UserEmailConfigurationForm']['aTestToAddress'];
                }
                if ($configurationForm->aTestToAddress != null)
                {
                    $emailHelper = new EmailHelper;
                    $emailHelper->outboundHost     = $configurationForm->host;
                    $emailHelper->outboundPort     = $configurationForm->port;
                    $emailHelper->outboundUsername = $configurationForm->username;
                    $emailHelper->outboundPassword = $configurationForm->password;
                    $emailHelper->outboundSecurity = $configurationForm->security;
                    $userToSendMessagesFrom        = User::getById((int)$configurationForm->userIdOfUserToSendNotificationsAs);

                    $emailMessage = EmailMessageHelper::sendTestEmail($emailHelper, $userToSendMessagesFrom,
                                                                      $configurationForm->aTestToAddress);
                    $messageContent  = null;
                    if (!$emailMessage->hasSendError())
                    {
                        $messageContent .= Yii::t('Default', 'Message successfully sent') . "\n";
                    }
                    else
                    {
                        $messageContent .= Yii::t('Default', 'Message failed to send') . "\n";
                        $messageContent .= $emailMessage->error     . "\n";
                    }
                }
                else
                {
                    $messageContent = Yii::t('Default', 'A test email address must be entered before you can send a test email.') . "\n";
                }
                Yii::app()->getClientScript()->setToAjaxMode();
                $messageView = new TestEmailMessageView($messageContent);
                $view = new ModalView($this, $messageView);
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
        * Assumes before calling this, the inbound settings have been validated in the form.
        */
        public function actionTestImapConnection()
        {
            $configurationForm = EmailArchivingConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);

                $imap = new ZurmoImap();

                $imap->imapHost     = $configurationForm->imapHost;
                $imap->imapUsername = $configurationForm->imapUsername;
                $imap->imapPassword = $configurationForm->imapPassword;
                $imap->imapPort     = $configurationForm->imapPort;
                $imap->imapSSL      = $configurationForm->imapSSL;
                $imap->imapFolder   = $configurationForm->imapFolder;

                try
                {
                    $connect = $imap->connect();
                }
                catch (Exception $e)
                {
                    $connect = false;
                    $messageContent = Yii::t('Default', 'Could not connect to IMAP server.') . "\n";
                }

                if (isset($connect) && $connect == true)
                {
                    $messageContent = Yii::t('Default', 'Successfully connected to IMAP server.') . "\n";
                }
                else
                {
                    $messageContent = Yii::t('Default', 'Could not connect to IMAP server.') . "\n";
                }

                Yii::app()->getClientScript()->setToAjaxMode();
                $messageView = new TestImapConnectionMessageView($messageContent);
                $view = new ModalView($this,
                                      $messageView,
                                      'modalContainer',
                                      Yii::t('Default', 'Test Message Results')
                );
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function actionMatchingList()
        {
            $userCanAccessContacts = RightsUtil::canUserAccessModule('ContactsModule', Yii::app()->user->userModel);
            $userCanAccessLeads    = RightsUtil::canUserAccessModule('LeadsModule', Yii::app()->user->userModel);
            EmailMessagesControllerSecurityUtil::
                resolveCanUserProperlyMatchMessage($userCanAccessContacts, $userCanAccessLeads);
            $pageSize         = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                'listPageSize', get_class($this->getModule()));
            $emailMessage     = new EmailMessage(false);
            $searchAttributes = array();
            $metadataAdapter  = new ArchivedEmailMatchingSearchDataProviderMetadataAdapter(
                $emailMessage,
                Yii::app()->user->userModel->id,
                $searchAttributes
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter->getAdaptedMetadata(),
                'EmailMessage',
                'RedBeanModelDataProvider',
                'createdDateTime',
                true,
                $pageSize
            );
            $titleBarAndListView = new TitleBarAndListView(
                                        $this->getId(),
                                        $this->getModule()->getId(),
                                        $emailMessage,
                                        'EmailMessage',
                                        $dataProvider,
                                        'ArchivedEmailMatchingListView',
                                        Yii::t('Default', 'Unmatched Archived Emails'),
                                        array(),
                                        false);
            $view = new EmailMessagesPageView(ZurmoDefaultViewUtil::
                                              makeStandardViewForCurrentUser($this, $titleBarAndListView));
            echo $view->render();
        }

        public function actionCompleteMatch($id)
        {
            //!!!todo security checks?? think about it
            $emailMessage          = EmailMessage::getById((int)$id);
            $userCanAccessContacts = RightsUtil::canUserAccessModule('ContactsModule', Yii::app()->user->userModel);
            $userCanAccessLeads    = RightsUtil::canUserAccessModule('LeadsModule', Yii::app()->user->userModel);
            if (!$userCanAccessContacts && !$userCanAccessLeads)
            {
                throw new NotSupportedException();
            }
            $selectForm            = self::makeSelectForm($userCanAccessLeads, $userCanAccessContacts);

            if (isset($_POST[get_class($selectForm)]))
            {
                if (isset($_POST['ajax']) && $_POST['ajax'] === 'select-contact-form-' . $id)
                {
                    $selectForm->setAttributes($_POST[get_class($selectForm)][$id]);
                    $selectForm->validate();
                    $errorData = array();
                    foreach ($selectForm->getErrors() as $attribute => $errors)
                    {
                            $errorData[ZurmoHtml::activeId($selectForm, $attribute)] = $errors;
                    }
                    echo CJSON::encode($errorData);
                    Yii::app()->end(0, false);
                }
                else
                {
                    $selectForm->setAttributes($_POST[get_class($selectForm)][$id]);
                    $contact = Contact::getById((int)$selectForm->contactId);
                    ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($emailMessage, $contact);
                    ArchivedEmailMatchingUtil::resolveEmailAddressToContactIfEmailRelationAvailable($emailMessage, $contact);
                    $emailMessage->folder = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox,
                                                                         EmailFolder::TYPE_ARCHIVED_UNMATCHED);
                    if (!$emailMessage->save())
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            else
            {
                static::attemptToMatchAndSaveLeadOrContact($emailMessage, 'Contact', (int)$id);
                static::attemptToMatchAndSaveLeadOrContact($emailMessage, 'Lead', (int)$id);
            }
        }

        protected static function attemptToMatchAndSaveLeadOrContact($emailMessage, $type, $emailMessageId)
        {
            assert('$type == "Contact" || $type == "Lead"');
            assert('is_int($emailMessageId)');
            if (isset($_POST[$type]))
            {
                if (isset($_POST['ajax']) && $_POST['ajax'] === strtolower($type) . '-inline-create-form-' . $emailMessageId)
                {
                    $contact = new Contact();
                    $contact->setAttributes($_POST[$type][$emailMessageId]);
                    $contact->validate();
                    $errorData = array();
                    foreach ($contact->getErrors() as $attribute => $errors)
                    {
                            $errorData[ZurmoHtml::activeId($contact, $attribute)] = $errors;
                    }
                    echo CJSON::encode($errorData);
                    Yii::app()->end(0, false);
                }
                else
                {
                    $contact = new Contact();
                    $contact->setAttributes($_POST[$type][$emailMessageId]);
                    if (!$contact->save())
                    {
                        throw new FailedToSaveModelException();
                    }
                    ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($emailMessage, $contact);
                    $emailMessage->folder = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox,
                                                                         EmailFolder::TYPE_ARCHIVED_UNMATCHED);
                    if (!$emailMessage->save())
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
        }

        public function actionCreateEmailMessage($toAddress = null, $relatedId = null, $relatedModelClassName = null)
        {
            $postData         = PostUtil::getData();
            $getData          = GetUtil::getData();
            $emailMessage     = new EmailMessage();
            $postVariableName = get_class($emailMessage);
            if (isset($postData[$postVariableName]))
            {
                if($relatedId != null && $relatedModelClassName != null && $toAddress != null)
                {
                    $personOrAccount                    = $relatedModelClassName::getById((int)$relatedId);
                    $messageRecipient                   = new EmailMessageRecipient();
                    $messageRecipient->toName           = strval($personOrAccount);
                    $messageRecipient->toAddress        = $toAddress;
                    $messageRecipient->type             = EmailMessageRecipient::TYPE_TO;
                    $messageRecipient->personOrAccount  = $personOrAccount;
                    $emailMessage->recipients->add($messageRecipient);
                }
                EmailMessageUtil::resolveEmailMessageFromPostData($postData, $emailMessage, Yii::app()->user->userModel);
                unset($_POST[ $postVariableName]['recipients']);
                $this->actionValidateCreateEmailMessage($postData, $emailMessage);
                $this->attemptToSaveModelFromPost($emailMessage, null, false);
            }
            else
            {
                EmailMessageUtil::resolveSignatureToEmailMessage($emailMessage, Yii::app()->user->userModel);
                EmailMessageUtil::resolvePersonOrAccountToEmailMessage($emailMessage, Yii::app()->user->userModel,
                                                                       $toAddress, $relatedId, $relatedModelClassName);
                $createEmailMessageModalEditView = new CreateEmailMessageModalEditView(
                    $this->getId(),
                    $this->getModule()->getId(),
                    $emailMessage);
                $view = new ModalView($this, $createEmailMessageModalEditView);
                Yii::app()->getClientScript()->setToAjaxMode();
                echo $view->render();
            }
        }

        /**
         * Override to process the security on the email message to match a related model if present.
         * (non-PHPdoc)
         * @see ZurmoBaseController::actionAfterSuccessfulModelSave()
         */
        protected function actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams = null)
        {
            assert('$model instanceof EmailMessage');
            $relatedId             = ArrayUtil::getArrayValue(GetUtil::getData(), 'relatedId');
            $relatedModelClassName = ArrayUtil::getArrayValue(GetUtil::getData(), 'relatedModelClassName');
            if ($relatedId != null &&
                $relatedModelClassName != null &&
                is_subclass_of($relatedModelClassName, 'OwnedSecurableItem'))
            {
                $relatedModel                      = $relatedModelClassName::getById((int)$relatedId);
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($relatedModel);
                ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($model,
                                                       $explicitReadWriteModelPermissions);
            }
            parent::actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
        }

        protected function actionValidateCreateEmailMessage($postData, EmailMessage $emailMessage)
        {
            if (isset($postData['ajax']) && $postData['ajax'] == 'edit-form')
            {
                $emailMessage->setAttributes($postData[get_class($emailMessage)]);
                if ($emailMessage->validate())
                {
                    Yii::app()->end(false);
                }
                else
                {
                    $errorData = array();
                    foreach ($emailMessage->getErrors() as $attribute => $errors)
                    {
                            $errorData[ZurmoHtml::activeId($emailMessage, $attribute)] = $errors;
                    }
                    echo CJSON::encode($errorData);
                }
                Yii::app()->end(false);
            }
        }

        /**
         * Given a partial name or e-mail address, search for all Users, Leads or Contacts
         * JSON encode the resulting array of contacts.
         */
        public function actionAutoCompleteForMultiSelectAutoComplete($term)
        {
            $pageSize               = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                'autoCompleteListPageSize', get_class($this->getModule()));
            $usersByFullName        = UserSearch::getUsersByPartialFullName($term, $pageSize);
            $usersByEmailAddress    = UserSearch::getUsersByEmailAddress($term, 'contains');
            $contacts               = ContactSearch::getContactsByPartialFullNameOrAnyEmailAddress($term, $pageSize, null, 'contains');
            $autoCompleteResults    = array();
            foreach ($usersByEmailAddress as $user)
            {
                $autoCompleteResults[] = array(
                    'id'   => strval($user->primaryEmail),
                    'name' => strval($user) . ' (' . $user->primaryEmail . ')',
                );
            }
            foreach ($usersByFullName as $user)
            {
                $autoCompleteResults[] = array(
                    'id'   => strval($user->primaryEmail),
                    'name' => strval($user) . ' (' . $user->primaryEmail . ')',
                );
            }
            foreach ($contacts as $contact)
            {
                $autoCompleteResults[] = array(
                    'id'   => strval($contact->primaryEmail),
                    'name' => strval($contact) . ' (' . $contact->primaryEmail . ')',
                );
            }
            $emailValidator = new CEmailValidator();
            if (count($autoCompleteResults) == 0 && $emailValidator->validateValue($term))
            {
                $autoCompleteResults[] = array('id' => $term, 'name' => $term);
            }
            echo CJSON::encode($autoCompleteResults);
        }

        protected static function makeSelectForm($userCanAccessLeads, $userCanAccessContacts)
        {
            if ($userCanAccessLeads && $userCanAccessContacts)
            {
                $selectForm = new AnyContactSelectForm();
            }
            elseif (!$userCanAccessLeads && $userCanAccessContacts)
            {
                $selectForm = new ContactSelectForm();
            }
            else
            {
                $selectForm = new LeadSelectForm();
            }
            return $selectForm;
        }
    }
?>
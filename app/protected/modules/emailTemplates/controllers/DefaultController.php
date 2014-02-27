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

    class EmailTemplatesDefaultController extends ZurmoModuleController
    {
        const ZERO_MODELS_FOR_WORKFLOW_CHECK_FILTER_PATH =
            'application.modules.emailTemplates.controllers.filters.EmailTemplatesForWorkflowZeroModelsCheckControllerFilter';

        const ZERO_MODELS_FOR_CONTACT_CHECK_FILTER_PATH =
            'application.modules.emailTemplates.controllers.filters.EmailTemplatesForMarketingZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('Core', 'Templates');
            return array($title);
        }

        public static function getDetailsAndEditForWorkflowBreadcrumbLinks()
        {
            return array(Zurmo::t('Core', 'Templates') => array('default/listForWorkflow'));
        }

        public static function getDetailsAndEditForMarketingBreadcrumbLinks()
        {
            return array(Zurmo::t('Core', 'Templates') => array('default/listForMarketing'));
        }

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        static::ZERO_MODELS_FOR_CONTACT_CHECK_FILTER_PATH . ' + listForMarketing, index',
                        'controller'                    => $this,
                        'activeActionElementType'       => EmailTemplatesForMarketingMenuActionElement::getType(),
                        'breadCrumbLinks'               => static::getListBreadcrumbLinks(),
                        'stateMetadataAdapterClassName' => 'EmailTemplatesForMarketingStateMetadataAdapter'
                    ),
                    array(
                        static::ZERO_MODELS_FOR_WORKFLOW_CHECK_FILTER_PATH . ' + listForWorkflow',
                        'controller'                    => $this,
                        'activeActionElementType'       => EmailTemplatesForWorkflowMenuActionElement::getType(),
                        'breadCrumbLinks'               => static::getListBreadcrumbLinks(),
                        'stateMetadataAdapterClassName' => 'EmailTemplatesForWorkflowStateMetadataAdapter'
                    ),
                )
            );
        }

        public function actionIndex()
        {
            $this->actionListForMarketing();
        }

        // TODO: @Shoaibi: Critical999: Refactor list actions
        public function actionListForMarketing()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                            'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = EmailTemplatesForMarketingMenuActionElement::getType();
            $emailTemplate                  = new EmailTemplate(false);
            $searchForm                     = new EmailTemplatesSearchForm($emailTemplate);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize,
                                              'EmailTemplatesForMarketingStateMetadataAdapter',
                                              'EmailTemplatesSearchView');
            $breadCrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView($searchForm, $dataProvider);
                $view = new EmailTemplatesPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForMarketingListsSearchAndListView', null, $activeActionElementType);
                $view      = new EmailTemplatesPageView(MarketingDefaultViewUtil::
                             makeViewWithBreadcrumbsForCurrentUser($this, $mixedView, $breadCrumbLinks, 'MarketingBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionListForWorkflow()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = EmailTemplatesForWorkflowMenuActionElement::getType();
            $emailTemplate                  = new EmailTemplate(false);
            $searchForm                     = new EmailTemplatesSearchForm($emailTemplate);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize,
                                              'EmailTemplatesForWorkflowStateMetadataAdapter',
                                              'EmailTemplatesSearchView');
            $breadCrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView($searchForm, $dataProvider);
                $view = new EmailTemplatesPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForWorkflowsSearchAndListView', null, $activeActionElementType);
                $view      = new EmailTemplatesPageView(WorkflowDefaultAdminViewUtil::
                             makeViewWithBreadcrumbsForCurrentUser($this, $mixedView, $breadCrumbLinks, 'WorkflowBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionSelectBuiltType($type)
        {
            assert('is_int($type) || is_string($type)');
            $type               = intval($type);
            $viewUtil           = static::getViewUtilByType($type);
            $breadCrumbView     = static::getBreadCrumbViewByType($type);
            $breadCrumbLinks    = static::getBreadCrumbLinksByType($type);
            $breadCrumbLinks[]  = Zurmo::t('EmailTemplatesModule', 'Select Email Template Type');
            $view               = new EmailTemplatesPageView($viewUtil::makeViewWithBreadcrumbsForCurrentUser(
                                                                                    $this,
                                                                                    new EmailTemplateWizardTypesGridView(),
                                                                                    $breadCrumbLinks,
                                                                                    $breadCrumbView));
            echo $view->render();
        }

        public function actionCreate($type, $builtType = null)
        {
            assert('is_int($type) || is_string($type)');
            $type                       = intval($type);
            if ($builtType == null)
            {
                $this->actionSelectBuiltType($type);
                Yii::app()->end(0, false);
            }
            assert('is_int($builtType) || is_string($builtType)');
            $builtType                  = intval($builtType);
            $viewUtil                   = static::getViewUtilByType($type);
            $breadCrumbView             = static::getBreadCrumbViewByType($type);
            $breadCrumbLinks            = static::getBreadCrumbLinksByType($type);
            $emailTemplate              = new EmailTemplate();
            $emailTemplate->type        = $type;
            $emailTemplate->builtType   = $builtType;
            $progressBarAndStepsView    = EmailTemplateWizardViewFactory::makeStepsAndProgressBarViewFromEmailTemplate($emailTemplate);
            if ($emailTemplate->isContactTemplate())
            {
                $emailTemplate->modelClassName = 'Contact';
            }

            // TODO: @Shoaibi: Critical99: port this code for edit, how?
            // TODO: @Shoaibi: Critical99: Edit hides the "select a base template part"
            if ($builtType == EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY ||
                    $builtType == EmailTemplate::BUILT_TYPE_PASTED_HTML)
            {
                $emailTemplate->isDraft     = false;
                $breadCrumbLinks[]          = Zurmo::t('Core', 'Create');
            }
            $wizardView                 = EmailTemplateWizardViewFactory::makeViewFromEmailTemplate($emailTemplate);
            $view                       = new EmailTemplatesPageView($viewUtil::makeTwoViewsWithBreadcrumbsForCurrentUser(
                                                                                                $this,
                                                                                                $progressBarAndStepsView,
                                                                                                $wizardView,
                                                                                                $breadCrumbLinks,
                                                                                                $breadCrumbView));
            echo $view->render();
        }

        public function actionSave($builtType)
        {
            // TODO: @Shoaibi/@Jason: Critical: No data sanitization?
            $postData                   = PostUtil::getData();
            $emailTemplate              = null;
            $this->resolveEmailTemplateByPostData($postData, $emailTemplate, $builtType);

            $emailTemplateToWizardFormAdapter   = new EmailTemplateToWizardFormAdapter($emailTemplate);
            $model                              =  $emailTemplateToWizardFormAdapter->makeFormByBuiltType();
            if (isset($postData['ajax']) && $postData['ajax'] === 'edit-form')
            {
                $this->actionValidate($postData, $model);
            }
            if ($emailTemplate->save())
            {
                echo CJSON::encode(array('id' => $emailTemplate->id, 'redirectToList' => false));
                Yii::app()->end(0, false);
            }
            else
            {
                throw new FailedToSaveModelException();
            }
        }

        public function actionCreateOld($type)
        {
            $type = (int)$type;
            $emailTemplate       = new EmailTemplate();
            $emailTemplate->type = $type;
            $editAndDetailsView  = $this->makeEditAndDetailsView($this->attemptToSaveModelFromPost($emailTemplate), 'Edit');
            if ($emailTemplate->isWorkflowTemplate())
            {
                $breadCrumbLinks    = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
                $breadCrumbLinks[]  = Zurmo::t('Core', 'Create');
                $view               = new EmailTemplatesPageView(WorkflowDefaultAdminViewUtil::
                    makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                        $breadCrumbLinks, 'WorkflowBreadCrumbView'));
            }
            elseif ($emailTemplate->isContactTemplate())
            {
                $emailTemplate->modelClassName = 'Contact';
                $breadCrumbLinks    = static::getDetailsAndEditForMarketingBreadcrumbLinks();
                $breadCrumbLinks[]  = Zurmo::t('Core', 'Create');
                $view               = new EmailTemplatesPageView(MarketingDefaultViewUtil::
                    makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                        $breadCrumbLinks, 'MarketingBreadCrumbView'));
            }
            else
            {
                throw new NotSupportedException();
            }
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($emailTemplate);

            $editAndDetailsView = $this->makeEditAndDetailsView($this->attemptToSaveModelFromPost($emailTemplate, $redirectUrl), 'Edit');
            if ($emailTemplate->isWorkflowTemplate())
            {
                $breadCrumbLinks    = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
                $breadCrumbLinks[]  = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view               = new EmailTemplatesPageView(WorkflowDefaultAdminViewUtil::
                                      makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                      $breadCrumbLinks, 'WorkflowBreadCrumbView'));
            }
            elseif ($emailTemplate->isContactTemplate())
            {
                $breadCrumbLinks    = static::getDetailsAndEditForMarketingBreadcrumbLinks();
                $breadCrumbLinks[]  = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view               = new EmailTemplatesPageView(MarketingDefaultViewUtil::
                                      makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                      $breadCrumbLinks, 'MarketingBreadCrumbView'));
            }
            else
            {
                throw new NotSupportedException();
            }
            echo $view->render();
        }

        public function actionDetails($id, $renderJson = false, $includeFilesInJson = false, $contactId = null)
        {
            $contactId     = (int) $contactId;
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailTemplate);
            if ($renderJson)
            {
                header('Content-type: application/json');
                if ($contactId != null)
                {
                    $contact     = Contact::getById($contactId);
                    $textContent = $emailTemplate->textContent;
                    $htmlContent = $emailTemplate->htmlContent;
                    AutoresponderAndCampaignItemsUtil::resolveContentForMergeTags($textContent, $htmlContent, $contact);
                    $unsubscribePlaceholder         = UnsubscribeAndManageSubscriptionsPlaceholderUtil::
                                                            UNSUBSCRIBE_URL_PLACEHOLDER;
                    $manageSubscriptionsPlaceholder = UnsubscribeAndManageSubscriptionsPlaceholderUtil::
                                                            MANAGE_SUBSCRIPTIONS_URL_PLACEHOLDER;
                    $textContent = str_replace(array($unsubscribePlaceholder, $manageSubscriptionsPlaceholder),
                                               null, $textContent);
                    $htmlContent = str_replace(array($unsubscribePlaceholder, $manageSubscriptionsPlaceholder),
                                               null, $htmlContent);
                    $emailTemplate->textContent = $textContent;
                    $emailTemplate->htmlContent = $htmlContent;
                }
                $emailTemplate = $this->resolveEmailTemplateAsJson($emailTemplate, $includeFilesInJson);
                echo $emailTemplate;
                Yii::app()->end(0, false);
            }
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($emailTemplate),
                                        'EmailTemplatesModule'), $emailTemplate);
            $detailsView              = new EmailTemplateEditAndDetailsView('Details', $this->getId(),
                                                                            $this->getModule()->getId(), $emailTemplate);

            if ($emailTemplate->isWorkflowTemplate())
            {
                $breadCrumbLinks          = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
                $breadCrumbLinks[]        = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view                     = new EmailTemplatesPageView(WorkflowDefaultAdminViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser($this, $detailsView,
                                            $breadCrumbLinks, 'WorkflowBreadCrumbView'));
            }
            elseif ($emailTemplate->isContactTemplate())
            {
                $breadCrumbLinks          = static::getDetailsAndEditForMarketingBreadcrumbLinks();
                $breadCrumbLinks[]        = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view                     = new EmailTemplatesPageView(MarketingDefaultViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser($this, $detailsView,
                                            $breadCrumbLinks, 'MarketingBreadCrumbView'));
            }
            else
            {
                throw new NotSupportedException();
            }
            echo $view->render();
        }

        protected function resolveEmailTemplateAsJson(EmailTemplate $emailTemplate, $includeFilesInJson)
        {
            $emailTemplateDataUtil          = new ModelToArrayAdapter($emailTemplate);
            $emailTemplateData              = $emailTemplateDataUtil->getData();
            if ($includeFilesInJson)
            {
                $emailTemplateData['filesIds']  = array();
                foreach ($emailTemplate->files as $file)
                {
                    $emailTemplateData['filesIds'][] = $file->id;
                }
            }
            $emailTemplateJson = CJSON::encode($emailTemplateData);
            return $emailTemplateJson;
        }

        protected static function getSearchFormClassName()
        {
            return 'EmailTemplatesSearchForm';
        }

        public function actionDelete($id)
        {
            $emailTemplate      = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($emailTemplate);
            $redirectUrl        = null;
            if ($emailTemplate->isWorkflowTemplate())
            {
                $redirectUrl = $this->getId() . '/listForWorkflow';
            }
            elseif ($emailTemplate->isContactTemplate())
            {
                $redirectUrl        = $this->getId() . '/listForMarketing';
            }
            $emailTemplate->delete();

            if (isset($redirectUrl))
            {
                $this->redirect(array($redirectUrl));
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function actionMergeTagGuide()
        {
            Yii::app()->getClientScript()->setToAjaxMode();
            $view = new ModalView($this, new MergeTagGuideView());
            echo $view->render();
        }

        public function actionGetHtmlContent($id, $className)
        {
            assert('is_string($className)');
            $modelId = (int) $id;
            $model = $className::getById($modelId);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
            echo $model->htmlContent;
        }

        /**
         * @param null $uniqueId
         * @param null $nodeId
         * @param string $moduleClassName
         */
        public function actionRelationsAndAttributesTreeForMergeTags($uniqueId = null, $nodeId = null, $moduleClassName = 'ContactsModule')
        {
            $type     = Report::TYPE_ROWS_AND_COLUMNS;
            $treeType = ComponentForReportForm::TYPE_FILTERS;
            $report   = new Report();
            $report->setModuleClassName($moduleClassName);
            $report->setType($type);
            if ($nodeId != null)
            {
                $reportToTreeAdapter = new MergeTagsReportRelationsAndAttributesToTreeAdapter($report, $treeType, $uniqueId);
                echo ZurmoTreeView::saveDataAsJson($reportToTreeAdapter->getData($nodeId));
                Yii::app()->end(0, false);
            }
            $view        = new ReportRelationsAndAttributesForMergeTagsTreeView($type, $treeType, 'edit-form', $uniqueId);
            $content     = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        protected static function getZurmoControllerUtil()
        {
            return new EmailTemplateZurmoControllerUtil();
        }

        protected static function getBreadCrumbViewByType($type)
        {
            $breadCrumbView   = 'MarketingBreadCrumbView';
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $breadCrumbView = 'WorkflowBreadCrumbView';
            }
            return $breadCrumbView;
        }

        protected static function getViewUtilByType($type)
        {
            $viewUtil = 'MarketingDefaultViewUtil';
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $viewUtil = 'WorkflowDefaultAdminViewUtil';
            }
            return $viewUtil;
        }

        protected static function getBreadCrumbLinksByType($type)
        {
            $breadCrumbLinks    = static::getDetailsAndEditForMarketingBreadcrumbLinks();
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $breadCrumbLinks    = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
            }
            return $breadCrumbLinks;
        }

        protected function resolveEmailTemplateByPostData(array $postData, & $emailTemplate, $builtType)
        {
            $formName   = EmailTemplateToWizardFormAdapter::getFormClassNameByBuiltType($builtType);
            $id         = $postData[$formName][GeneralDataForEmailTemplateWizardView::HIDDEN_ID];
            if ($id <= 0)
            {
                $this->resolveCanCurrentUserAccessEmailTemplates();
                $emailTemplate               = new EmailTemplate();
            }
            else
            {
                $emailTemplate              = EmailTemplate::getById(intval($id));
            }
            DataToEmailTemplateUtil::resolveEmailTemplateByWizardPostData($emailTemplate, $postData,
                EmailTemplateToWizardFormAdapter::getFormClassNameByBuiltType($builtType));
        }

        protected function resolveCanCurrentUserAccessEmailTemplates()
        {
            if (!RightsUtil::doesUserHaveAllowByRightName('EmailTemplatesModule',
                                                            EmailTemplatesModule::RIGHT_CREATE_EMAIL_TEMPLATES,
                                                            Yii::app()->user->userModel))
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            return true;
        }

        protected function actionValidate($postData, EmailTemplateWizardForm $model)
        {
            if (isset($postData['validationScenario']) && $postData['validationScenario'] != null)
            {
                $model->setScenario($postData['validationScenario']);
            }
            else
            {
                throw new NotSupportedException();
            }
            $errorData = array();
            $validated = $model->validate();
            if ($validated === false)
            {
                foreach ($model->getErrors() as $attribute => $errors)
                {
                    $errorData[ZurmoHtml::activeId($model, $attribute)] = $errors;
                }
            }
            echo CJSON::encode($errorData);
            Yii::app()->end(0, false);
        }

        public function actionRenderCanvas($id = null)
        {
            Yii::app()->clientScript->setToAjaxMode();
            // it would be empty for the first time during create so we just end the request here.
            if (empty($id))
            {
                Yii::app()->end(0, false);
            }
            assert('is_int($id) || is_string($id)');
            $content = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($id, true);
            Yii::app()->clientScript->render($content);
            echo $content;
        }

        public function actionRenderPreview($id = null)
        {
            Yii::app()->clientScript->setToAjaxMode();
            if (isset($id))
            {
                $content = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($id, false);
                Yii::app()->clientScript->render($content);
                echo $content;
                Yii::app()->end(0, false);
            }
            // serializedData['dom'] = json_encoded_dom
            $serializedDataArray    = Yii::app()->request->getPost('serializedData');
            if (!Yii::app()->request->isPostRequest || $serializedDataArray === null)
            {
                Yii::app()->end(0, false);
            }
            $content = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlBySerializedData($serializedDataArray, false);
            Yii::app()->clientScript->render($content);
            echo $content;
        }

        public function actionRenderElementEditable()
        {
            $this->actionRenderElement(true);
        }

        public function actionRenderElementNonEditable()
        {
            $this->actionRenderElement(false);
        }

        protected function actionRenderElement($editable = false)
        {
            Yii::app()->clientScript->setToAjaxMode();
            $className          = Yii::app()->request->getPost('className');
            $id                 = Yii::app()->request->getPost('id');
            $properties         = Yii::app()->request->getPost('properties');
            $content            = Yii::app()->request->getPost('content');
            $renderForCanvas    = Yii::app()->request->getPost('renderForCanvas', !$editable);
            $wrapElementInRow   = Yii::app()->request->getPost('wrapElementInRow', BuilderElementRenderUtil::DO_NOT_WRAP_IN_ROW);

            // at bare minimum we should have classname. Without these it does not make sense.
            if (!Yii::app()->request->isPostRequest || !isset($className))
            {
                Yii::app()->end(0, false);
            }
            if ($editable)
            {
                $content = BuilderElementRenderUtil::renderEditable($className, $renderForCanvas, $id, $properties, $content);
            }
            else
            {
                $content = BuilderElementRenderUtil::renderNonEditable($className, $renderForCanvas, $wrapElementInRow,
                                                                        $id, $properties, $content);
            }
            Yii::app()->clientScript->render($content);
            echo $content;
        }

        public function actionRenderBaseTemplateOptions($elementClassName, $elementModelClassName, $elementAttributeName, $elementFormClassName, array $elementParams = array())
        {
            $element                    = new $elementClassName(new $elementModelClassName(), $elementAttributeName,
                                                                new $elementFormClassName(), $elementParams);
            echo $element->render();
        }

        public function actionSendTestEmail($id = null, $contactId = null)
        {
            $emailTemplate  = EmailTemplate::getById(intval($id));
            $contact        = Contact::getById(intval($contactId));
            $this->resolveEmailMessage($emailTemplate, $contact);
        }

        protected function resolveEmailMessage(EmailTemplate $emailTemplate, Contact $contact)
        {
            // TODO: @Shoaibi: Critical: Refactor this and AutoresponderAndCampaignItemsUtil
            $emailMessage                       = new EmailMessage();
            $emailMessage->subject              = $emailTemplate->subject;
            $emailContent                       = new EmailMessageContent();
            $emailContent->textContent          = $emailTemplate->textContent;
            $emailContent->htmlContent          = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateModel($emailTemplate);
            $emailMessage->content              = $emailContent;
            $emailMessage->sender               = static::resolveSender();
            static::resolveRecipient($emailMessage, $contact);
            $box                                = EmailBox::resolveAndGetByName(EmailBox::USER_DEFAULT_NAME);
            $emailMessage->folder               = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            Yii::app()->emailHelper->sendImmediately($emailMessage);
            $emailMessage->owner                = $emailTemplate->owner;
            $explicitReadWriteModelPermissions  = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailTemplate);
            ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($emailMessage,
                                                                                    $explicitReadWriteModelPermissions);
            if (!$emailMessage->save())
            {
                throw new FailedToSaveModelException("Unable to save EmailMessage");
            }
        }

        protected static function resolveSender()
        {
            $sender                         = new EmailMessageSender();
            $sender->fromAddress            = Yii::app()->emailHelper->resolveFromAddressByUser(Yii::app()->user->userModel);
            $sender->fromName               = strval(Yii::app()->user->userModel);
            return $sender;
        }

        protected static function resolveRecipient(EmailMessage $emailMessage, Contact $contact)
        {
            if ($contact->primaryEmail->emailAddress != null)
            {
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = $contact->primaryEmail->emailAddress;
                $recipient->toName          = strval($contact);
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personsOrAccounts->add($contact);
                $emailMessage->recipients->add($recipient);
            }
        }
    }
?>
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

    class CalendarsDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            $modelClassName   = $this->getModule()->getPrimaryModelName();
            $viewClassName    = $modelClassName . 'EditAndDetailsView';
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                   ),
                    array(
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                   ),
               )
            );
        }

        /**
         * Redirect to combined details view for calendar.
         * @param int $id
         */
        public function actionDetails($id = null)
        {
            $urlParams = array($this->getId() . '/combinedDetails');
            $this->redirect($urlParams);
        }

        /**
         * Create the calendar.
         */
        public function actionCreate()
        {
            $savedCalendar                  = new SavedCalendar();
            $savedCalendar->moduleClassName = 'MeetingsModule';
            $attributes                     = CalendarUtil::getModelAttributesForSelectedModule($savedCalendar->moduleClassName);
            $attributeKeys                  = array_keys($attributes);
            $savedCalendar->startAttributeName = $attributeKeys[0];
            $this->attemptToValidateAjaxFromPost($savedCalendar, 'SavedCalendar');
            $editAndDetailsView = $this->makeEditAndDetailsView(
                                            $this->resolveReportDataAndSaveCalendar($savedCalendar), 'Edit');
            $view               = new CalendarsPageView(ZurmoDefaultViewUtil::
                                                        makeStandardViewForCurrentUser($this, $editAndDetailsView));
            echo $view->render();
        }

        /**
         * Edit the calendar.
         * @param int $id
         * @param string $redirectUrl
         */
        public function actionEdit($id, $redirectUrl = null)
        {
            $savedCalendar = SavedCalendar::getById(intval($id));
            $this->attemptToValidateAjaxFromPost($savedCalendar, 'SavedCalendar');
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedCalendar);
            $this->processEdit($savedCalendar, $redirectUrl);
        }

        /**
         * Process edit of the calendar.
         * @param SavedCalendar $calendar
         * @param string $redirectUrl
         */
        protected function processEdit(SavedCalendar $calendar, $redirectUrl = null)
        {
            $view = new CalendarsPageView(ZurmoDefaultViewUtil::
                            makeStandardViewForCurrentUser($this,
                            $this->makeEditAndDetailsView(
                                $this->resolveReportDataAndSaveCalendar($calendar), 'Edit')));
            echo $view->render();
        }

        /**
         * Combined details for the calendar.
         */
        public function actionCombinedDetails()
        {
            $dataProvider               = CalendarUtil::getCalendarItemsDataProvider();
            $interactiveCalendarView    = new CombinedCalendarView($dataProvider, $this->getId(), $this->getModule()->getId());
            $view                       = new CalendarsPageView(ZurmoDefaultViewUtil::
                                                  makeStandardViewForCurrentUser($this,$interactiveCalendarView));
            echo $view->render();
        }

        public function actionRelationsAndAttributesTree($type, $treeType, $id = null, $nodeId = null)
        {
            $report        = $this->resolveReportBySavedCalendarPostData($type, $id);
            if ($nodeId != null)
            {
                $reportToTreeAdapter = new ReportRelationsAndAttributesToTreeAdapter($report, $treeType);
                echo ZurmoTreeView::saveDataAsJson($reportToTreeAdapter->getData($nodeId));
                Yii::app()->end(0, false);
            }
            $view        = new ReportRelationsAndAttributesForSavedCalendarTreeView($type, $treeType, 'edit-form');
            $content     = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        //todO: refactor to reuse same code in controller for reporting? do if it makes sense after done.
        public function actionAddAttributeFromTree($type, $treeType, $nodeId, $rowNumber,
                                                   $trackableStructurePosition = false, $id = null)
        {
            $report                             = $this->resolveReportBySavedCalendarPostData($type, $id);
            $nodeIdWithoutTreeType              = ReportRelationsAndAttributesToTreeAdapter::
                                                     removeTreeTypeFromNodeId($nodeId, $treeType);
            $moduleClassName                    = $report->getModuleClassName();
            $modelClassName                     = $moduleClassName::getPrimaryModelName();
            $form                               = new WizardActiveForm();
            $form->id                           = 'edit-form';
            $form->enableAjaxValidation         = true; //ensures error validation populates correctly

            $wizardFormClassName                = ReportToWizardFormAdapter::getFormClassNameByType($report->getType());
            $model                              = ComponentForReportFormFactory::makeByComponentType($moduleClassName,
                                                      $modelClassName, $report->getType(), $treeType);
            $form->modelClassNameForError       = $wizardFormClassName;
            $attribute                          = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $model->attributeIndexOrDerivedType = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $inputPrefixData                    = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveInputPrefixData($wizardFormClassName,
                                                      $treeType, (int)$rowNumber);
            $adapter                            = new ReportAttributeForSavedCalendarToElementAdapter($inputPrefixData, $model,
                                                      $form, $treeType);
            $view                               = new AttributeRowForReportComponentView($adapter,
                                                      (int)$rowNumber, $inputPrefixData, $attribute,
                                                      (bool)$trackableStructurePosition, true, $treeType);
            $content               = $view->render();
            $form->renderAddAttributeErrorSettingsScript($view::getFormId());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        protected function resolveReportBySavedCalendarPostData($type, $id = null)
        {
            $postData = PostUtil::getData();
            if ($id == null)
            {
                $report = new Report();
                $report->setType($type);
            }
            else
            {
                $savedCalendar              = SavedCalendar::getById(intval($id));
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedCalendar);
                $report                     = SavedCalendarToReportAdapter::makeReportBySavedCalendar($savedCalendar);
            }
            if(isset($postData['SavedCalendar']) && isset($postData['SavedCalendar']['moduleClassName']))
            {
                $report->setModuleClassName($postData['SavedCalendar']['moduleClassName']);
            }
//            else
//            {
//                throw new NotSupportedException();
//            }
            DataToReportUtil::resolveReportByWizardPostData($report, $postData,
                ReportToWizardFormAdapter::getFormClassNameByType($type));
            return $report;
        }

        protected function attemptToValidateAjaxFromPost($model, $postVariableName)
        {
            //todo: refactor - this is the same as used by note controller for saving inline.
            if (isset($_POST['ajax']) && $_POST['ajax'] == 'edit-form')
            {
                $readyToUsePostData            = ExplicitReadWriteModelPermissionsUtil::
                                                         removeIfExistsFromPostData($_POST[get_class($model)]);
                $sanitizedPostData             = PostUtil::
                                                 sanitizePostByDesignerTypeForSavingModel($model, $readyToUsePostData);
                $sanitizedOwnerPostData        = PostUtil::
                                                 sanitizePostDataToJustHavingElementForSavingModel($sanitizedPostData, 'owner');
                $sanitizedPostDataWithoutOwner = PostUtil::removeElementFromPostDataForSavingModel($sanitizedPostData, 'owner');
                $model->setAttributes($sanitizedPostDataWithoutOwner);
                if ($model->validate())
                {
                    $modelToStringValue = strval($model);
                    if ($sanitizedOwnerPostData != null)
                    {
                        $model->setAttributes($sanitizedOwnerPostData);
                    }
                    if ($model instanceof OwnedSecurableItem)
                    {
                        $model->validate(array('owner'));
                    }
                }
                $errorData = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($model);
                echo CJSON::encode($errorData);
                Yii::app()->end(0, false);
            }
        }

        protected function resolveReportDataAndSaveCalendar(SavedCalendar $savedCalendar)
        {
            if (isset($_POST['SavedCalendar']))
            {
                $postData   = PostUtil::getData();
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedCalendar);
                $this->attemptToSaveModelFromPost($savedCalendar, null, false);
                $report               = SavedCalendarToReportAdapter::makeReportBySavedCalendar($savedCalendar);
                $wizardFormClassName  = ReportToWizardFormAdapter::getFormClassNameByType($report->getType());
                if (!isset($postData[$wizardFormClassName]))
                {
                    throw new NotSupportedException();
                }
                else
                {
                    //This would do the filter and filter structure validation
                    $reportToWizardFormAdapter = new ReportToWizardFormAdapter($report);
                    $model                     =  $reportToWizardFormAdapter->makeFormByType();
                    if (isset($postData['ajax']) && $postData['ajax'] === 'edit-form')
                    {
                        $postData[$wizardFormClassName]['validationScenario'] = $wizardFormClassName::FILTERS_VALIDATION_SCENARIO;
                        ReportUtil::validateReportWizardForm($postData, $model);
                    }
                }
                DataToReportUtil::resolveFilters($postData[$wizardFormClassName], $report, true);
                $filtersData          = ArrayUtil::getArrayValue($postData[$wizardFormClassName],
                                                                        ComponentForReportForm::TYPE_FILTERS);
                $sanitizedFiltersData = DataToReportUtil::sanitizeFiltersData($report->getModuleClassName(),
                                                                              $report->getType(),
                                                                              $filtersData);
                $data   = array(ComponentForReportForm::TYPE_FILTERS => $sanitizedFiltersData,
                                        'filtersStructure' => $report->getFiltersStructure());
                $savedCalendar->serializedData = serialize($data);
                $savedCalendar->save();
            }
            return $savedCalendar;
        }

        /**
         * Get events for the selected calendars.
         */
        public function actionGetEvents($selectedMyCalendarIds = null,
                                        $selectedSharedCalendarIds = null,
                                        $startDate = null,
                                        $endDate = null,
                                        $dateRangeType = null)
        {
            ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel,
                                                               'CalendarsModule',
                                                               'myCalendarStartDate', $startDate);
            ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel,
                                                               'CalendarsModule',
                                                               'myCalendarEndDate', $endDate);
            ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel,
                                                               'CalendarsModule',
                                                               'myCalendarDateRangeType', $dateRangeType);
            $dataProvider               = CalendarUtil::processUserCalendarsAndMakeDataProviderForCombinedView($selectedMyCalendarIds,
                                                                                                               $selectedSharedCalendarIds,
                                                                                                               $dateRangeType,
                                                                                                               $startDate,
                                                                                                               $endDate);
            $items                      = CalendarUtil::getFullCalendarItems($dataProvider);
            echo CJSON::encode($items);
        }

        /**
         * Deletes a calendar.
         * @param string $id
         */
        public function actionDelete($id)
        {
            $savedCalendar = SavedCalendar::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($savedCalendar);
            $savedCalendar->delete();
            $dataProvider                        = CalendarUtil::getCalendarItemsDataProvider();
            $savedCalendarSubscriptions          = $dataProvider->getSavedCalendarSubscriptions();
            $content                             = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getMySavedCalendarsAndSelected(),
                                                                                       'mycalendar[]', 'mycalendar', 'saved');
            echo $content;
        }

        /**
         * Renders modal list for the shared calendars for the user.
         */
        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromSharedCalendarsModalListLinkProvider(
                                            CalendarUtil::getModalContainerId(),
                                            'shared-calendars-list'
                                        );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider, 'SharedCalendersStateMetadataAdapter');
        }

        /**
         * Add subscription for calendar.
         * @param int $id
         */
        public function actionAddSubsriptionForCalendar($id)
        {
            $savedCalendar                       = SavedCalendar::getById(intval($id));
            $user                                = Yii::app()->user->userModel;
            $savedCalendarSubscription           = new SavedCalendarSubscription();
            $savedCalendarSubscription->user     = $user;
            $savedCalendarSubscription->savedcalendar = $savedCalendar;
            $savedCalendarSubscription->save();
            $dataProvider                        = CalendarUtil::getCalendarItemsDataProvider();
            $savedCalendarSubscriptions          = $dataProvider->getSavedCalendarSubscriptions();
            $content                             = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected(),
                                                                                       'sharedcalendar[]', 'sharedcalendar', 'shared');
            echo $content;
        }

        /**
         * Remove the subscription for the calendar.
         * @param int $id
         */
        public function actionUnsubscribe($id)
        {
            $savedCalendarSubscription = SavedCalendarSubscription::getById(intval($id));
            $savedCalendarSubscription->delete();
            $dataProvider                        = CalendarUtil::getCalendarItemsDataProvider();
            $savedCalendarSubscriptions          = $dataProvider->getSavedCalendarSubscriptions();
            $content                             = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected(),
                                                                                       'sharedcalendar[]', 'sharedcalendar', 'shared');
            echo $content;
        }

        /**
         * Get date time attributes.
         * @param string $moduleName
         * @param string $attribute
         */
        public function actionGetDateTimeAttributes($moduleName, $attribute)
        {
            assert('is_string($attribute)');
            assert('is_string($moduleName)');
            $data = CalendarUtil::getModelAttributesForSelectedModule($moduleName);
            $htmlOptions = array();
            if($attribute == 'endAttributeName')
            {
                $htmlOptions['empty'] = Zurmo::t('Core', '(None)');
            }
            echo ZurmoHtml::listOptions('', $data, $htmlOptions);
        }
    }
?>
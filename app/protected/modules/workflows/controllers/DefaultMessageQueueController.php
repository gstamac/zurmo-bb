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

    /**
     * Default controller for WorkflowMessageInQueue actions
      */
    class WorkflowsDefaultMessageQueueController extends ZurmoBaseController
    {
        const ZERO_MODELS_CHECK_FILTER_PATH = 'application.modules.workflows.controllers.filters.WorkflowZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('WorkflowsModule', 'Message Queue');
            return array($title);
        }

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        static::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                        'activeActionElementType' => 'WorkflowMessageInQueuesLink',
                        'breadcrumbLinks'         => static::getListBreadcrumbLinks(),
                    ),
                )
            );
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = 'WorkflowMessageInQueuesLink';
            $model                          = new WorkflowMessageInQueue(false);
            $searchForm                     = new WorkflowMessageInQueuesSearchForm($model);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize, null,
                                              'WorkflowMessageInQueuesSearchView');
            $breadcrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider,
                    'WorkflowMessageInQueuesListView'
                );
                $view = new WorkflowsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForWorkflowsSearchAndListView', 'WorkflowMessageInQueues', $activeActionElementType);
                $view = new WorkflowsPageView(ZurmoDefaultAdminViewUtil::
                                              makeViewWithBreadcrumbsForCurrentUser(
                                              $this, $mixedView, $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            echo $view->render();
        }
    }
?>

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

    class MarketingListsDefaultController extends ZurmoModuleController
    {
        // TODO: @Shoaibi: Low: Rewrite unit tests for all models, controllers, utils, adapters, etc
        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                                        'listPageSize', get_class($this->getModule()));
            $marketingList                  = new MarketingList(false);
            $searchForm                     = new MarketingListsSearchForm($marketingList);
            $listAttributesSelector         = new ListAttributesSelector('MarketingListsListView',
                                                                                        get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'MarketingListsSearchView'
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new MarketingListsPageView($mixedView);
            }
            else
            {
                $mixedView = new ActionBarAndListView(
                                                        $this->getId(),
                                                        $this->getModule()->getId(),
                                                        $marketingList,
                                                        'MarketingLists',
                                                        $dataProvider,
                                                        array(),
                                                        'MarketingListsActionBarForListView'
                                                    );
                $view = new MarketingListsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $mixedView));
            }
            echo $view->render();
        }

        public function actionCreate()
        {
           $editView = new MarketingListEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost(new MarketingList()),
                                                 Zurmo::t('Default', 'Create Marketing List'));
            $view = new MarketingListsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        /*
        public function actionDetails($id)
        {
            $marketingList = static::getModelAndCatchNotFoundAndDisplayError('MarketingList', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($marketingList);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                                      array(strval($marketingList), 'MarketingListsModule'), $marketingList);
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $marketingListMember            = new MarketingListMember(false);
            $searchForm                     = new MarketingListMembersSearchForm($marketingListMember);
         /*   $dataProvider                   = $this->resolveSearchDataProvider(
                                              $searchForm,
                                              $pageSize,
                                              null,
                                              'MarketingListMembersSearchView'
                                              );/
            $searchAttributes = array();
            $metadataAdapter  = new MarketingListsMembersSearchDataProviderMetadataAdapter(
                $marketingListMember,
                Yii::app()->user->userModel->id,
                $searchAttributes,
                $id
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter->getAdaptedMetadata(),
                'MarketingListMember',
                'RedBeanModelDataProvider',
                'latestDateTime',
                true,
                $pageSize
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView($searchForm, $dataProvider);
                $view = new MarketingListsPageView($mixedView);
            }
            else
            {
                $breadcrumbLinks = array(StringUtil::getChoppedStringContent(strval($marketingList), 25));
                $mixedView       = $this->makeActionBarDetailsSearchAndListView($marketingList, $searchForm, $dataProvider);
                $view            = new MarketingListsPageView(ZurmoDefaultViewUtil::
                                       makeViewWithBreadcrumbsForCurrentUser($this, $mixedView, $breadcrumbLinks,
                                                                             'MarketingListBreadCrumbView'));
            }
            echo $view->render();
        }

        protected function makeActionBarDetailsSearchAndListView(MarketingList $marketingList,
                                                                 MarketingListMembersSearchForm $searchModel, $dataProvider)
        {
            $listModel = $searchModel->getModel();
            return new MarketingListMembersActionBarDetailsSearchAndListView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $marketingList,
                        $searchModel,
                        $listModel,
                        $dataProvider,
                        GetUtil::resolveSelectedIdsFromGet());
        }
        */

        public function actionDetails($id)
        {
            // TODO: @Shoaibi: Low: what about ajax?
            $marketingList = static::getModelAndCatchNotFoundAndDisplayError('MarketingList', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($marketingList);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                                            array(strval($marketingList), 'MarketingListsModule'), $marketingList);
            $breadCrumbView             = StickySearchUtil::resolveBreadCrumbViewForDetailsControllerAction($this,
                                                                            'MarketingListsSearchView', $marketingList);
            $detailsAndRelationsView    = $this->makeDetailsAndRelationsView($marketingList, 'MarketingListsModule',
                                                                                'MarketingListDetailsAndRelationsView',
                                                                                Yii::app()->request->getRequestUri(),
                                                                                $breadCrumbView);
            $view                       = new MarketingListsPageView(ZurmoDefaultViewUtil::
                                                makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
            echo $view->render();
        }


        public function actionEdit($id, $redirectUrl = null)
        {
            $marketingList = MarketingList::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($marketingList);
            $editView = new MarketingListEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost($marketingList),
                                                 strval($marketingList));
            $view     = new MarketingListsPageView(ZurmoDefaultViewUtil::
                                                  makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionDelete($id)
        {
            $marketingList = static::getModelAndCatchNotFoundAndDisplayError('MarketingList', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($marketingList);
            $marketingList->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            echo ModalSearchListControllerUtil::setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider,
                                                'ContactsStateMetadataAdapter');
        }
        protected static function getSearchFormClassName()
        {
            return 'MarketingListsSearchForm';
        }

    }
?>

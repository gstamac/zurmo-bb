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

    class ZurmoPaginationHelperTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('sally');
        }

        public function testZurmoPaginationHelper()
        {
            $pager = new ZurmoPaginationHelper();
            $pager->setListPageSize(11);
            $pager->setSubListPageSize(12);
            $pager->setModalListPageSize(13);
            $pager->setMassEditProgressPageSize(14);
            $pager->setAutoCompleteListPageSize(15);
            $pager->setImportPageSize(16);
            $pager->setDashboardListPageSize(17);
            $pager->setMassDeleteProgressPageSize(18);
            $pager->setReportResultsListPageSize(19);
            $pager->setReportResultsSubListPageSize(20);
            $pager->setMassSubscribeProgressPageSize(21);
            $pager->setKanbanBoardPageSize(22);
            $pager->setTaskKanbanBoardPageSize(23);

            //Retrieve settings for different current users.
            Yii::app()->user->userModel =  User::getByUsername('super');
            Yii::app()->user->clearStates();
            $this->assertEquals(11, $pager->resolveActiveForCurrentUserByType('listPageSize'));
            Yii::app()->user->userModel =  User::getByUsername('billy');
            Yii::app()->user->clearStates();
            $this->assertEquals(11, $pager->resolveActiveForCurrentUserByType('listPageSize'));
            $this->assertEquals(12, $pager->resolveActiveForCurrentUserByType('subListPageSize'));
            $this->assertEquals(13, $pager->resolveActiveForCurrentUserByType('modalListPageSize'));
            $this->assertEquals(14, $pager->resolveActiveForCurrentUserByType('massEditProgressPageSize'));
            $this->assertEquals(15, $pager->resolveActiveForCurrentUserByType('autoCompleteListPageSize'));
            $this->assertEquals(16, $pager->resolveActiveForCurrentUserByType('importPageSize'));
            $this->assertEquals(17, $pager->resolveActiveForCurrentUserByType('dashboardListPageSize'));
            $this->assertEquals(18, $pager->resolveActiveForCurrentUserByType('massDeleteProgressPageSize'));
            $this->assertEquals(19, $pager->resolveActiveForCurrentUserByType('reportResultsListPageSize'));
            $this->assertEquals(20, $pager->resolveActiveForCurrentUserByType('reportResultsSubListPageSize'));
            $this->assertEquals(21, $pager->resolveActiveForCurrentUserByType('massSubscribeProgressPageSize'));
            $this->assertEquals(22, $pager->resolveActiveForCurrentUserByType('kanbanBoardPageSize'));
            $this->assertEquals(23, $pager->resolveActiveForCurrentUserByType('taskKanbanBoardPageSize'));

            //Retrieve settings for different specific users
            $sally = User::getByUsername('sally');
            $billy = User::getByUsername('billy');
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $sally;
            Yii::app()->user->clearStates();
            $this->assertEquals(11, $pager->getByUserAndType($super, 'listPageSize'));
            $this->assertEquals(11, $pager->getByUserAndType($billy, 'listPageSize'));
            $this->assertEquals(12, $pager->getByUserAndType($super, 'subListPageSize'));
            $this->assertEquals(12, $pager->getByUserAndType($billy, 'subListPageSize'));
            $this->assertEquals(13, $pager->getByUserAndType($super, 'modalListPageSize'));
            $this->assertEquals(13, $pager->getByUserAndType($billy, 'modalListPageSize'));
            $this->assertEquals(14, $pager->getByUserAndType($super, 'massEditProgressPageSize'));
            $this->assertEquals(14, $pager->getByUserAndType($billy, 'massEditProgressPageSize'));
            $this->assertEquals(15, $pager->getByUserAndType($super, 'autoCompleteListPageSize'));
            $this->assertEquals(15, $pager->getByUserAndType($billy, 'autoCompleteListPageSize'));
            $this->assertEquals(16, $pager->getByUserAndType($super, 'importPageSize'));
            $this->assertEquals(16, $pager->getByUserAndType($billy, 'importPageSize'));
            $this->assertEquals(17, $pager->getByUserAndType($super, 'dashboardListPageSize'));
            $this->assertEquals(17, $pager->getByUserAndType($billy, 'dashboardListPageSize'));
            $this->assertEquals(18, $pager->getByUserAndType($super, 'massDeleteProgressPageSize'));
            $this->assertEquals(18, $pager->getByUserAndType($billy, 'massDeleteProgressPageSize'));
            $this->assertEquals(19, $pager->getByUserAndType($super, 'reportResultsListPageSize'));
            $this->assertEquals(19, $pager->getByUserAndType($billy, 'reportResultsListPageSize'));
            $this->assertEquals(20, $pager->getByUserAndType($super, 'reportResultsSubListPageSize'));
            $this->assertEquals(20, $pager->getByUserAndType($billy, 'reportResultsSubListPageSize'));
            $this->assertEquals(21, $pager->getByUserAndType($super, 'massSubscribeProgressPageSize'));
            $this->assertEquals(21, $pager->getByUserAndType($billy, 'massSubscribeProgressPageSize'));
            $this->assertEquals(22, $pager->getByUserAndType($super, 'kanbanBoardPageSize'));
            $this->assertEquals(22, $pager->getByUserAndType($billy, 'kanbanBoardPageSize'));
            $this->assertEquals(23, $pager->getByUserAndType($super, 'taskKanbanBoardPageSize'));
            $this->assertEquals(23, $pager->getByUserAndType($billy, 'taskKanbanBoardPageSize'));

            $pager->setByUserAndType($billy, 'listPageSize',                 88);
            $pager->setByUserAndType($billy, 'subListPageSize',              89);
            $pager->setByUserAndType($billy, 'modalListPageSize',            90);
            $pager->setByUserAndType($billy, 'massEditProgressPageSize',     91);
            $pager->setByUserAndType($billy, 'autoCompleteListPageSize',     92);
            $pager->setByUserAndType($billy, 'importPageSize',               93);
            $pager->setByUserAndType($billy, 'dashboardListPageSize',        94);
            $pager->setByUserAndType($billy, 'massDeleteProgressPageSize',   95);
            $pager->setByUserAndType($billy, 'reportResultsListPageSize',    96);
            $pager->setByUserAndType($billy, 'reportResultsSubListPageSize', 97);
            $pager->setByUserAndType($billy, 'massSubscribeProgressPageSize', 98);
            $pager->setByUserAndType($billy, 'kanbanBoardPageSize',          99);
            $pager->setByUserAndType($billy, 'taskKanbanBoardPageSize',      100);

            $this->assertEquals(88, $pager->getByUserAndType($billy, 'listPageSize'));
            $this->assertEquals(89, $pager->getByUserAndType($billy, 'subListPageSize'));
            $this->assertEquals(90, $pager->getByUserAndType($billy, 'modalListPageSize'));
            $this->assertEquals(91, $pager->getByUserAndType($billy, 'massEditProgressPageSize'));
            $this->assertEquals(92, $pager->getByUserAndType($billy, 'autoCompleteListPageSize'));
            $this->assertEquals(93, $pager->getByUserAndType($billy, 'importPageSize'));
            $this->assertEquals(94, $pager->getByUserAndType($billy, 'dashboardListPageSize'));
            $this->assertEquals(95, $pager->getByUserAndType($billy, 'massDeleteProgressPageSize'));
            $this->assertEquals(96, $pager->getByUserAndType($billy, 'reportResultsListPageSize'));
            $this->assertEquals(97, $pager->getByUserAndType($billy, 'reportResultsSubListPageSize'));
            $this->assertEquals(98, $pager->getByUserAndType($billy, 'massSubscribeProgressPageSize'));
            $this->assertEquals(99, $pager->getByUserAndType($billy, 'kanbanBoardPageSize'));
            $this->assertEquals(100, $pager->getByUserAndType($billy, 'taskKanbanBoardPageSize'));
        }

        public function testSetGetGlobalValueByType()
        {
            $pager = new ZurmoPaginationHelper();
            $pager->setListPageSize(11);
            $pager->setSubListPageSize(12);
            $pager->setModalListPageSize(13);
            $pager->setMassEditProgressPageSize(14);
            $pager->setAutoCompleteListPageSize(15);
            $pager->setImportPageSize(16);
            $pager->setDashboardListPageSize(17);
            $pager->setMassDeleteProgressPageSize(18);
            $pager->setReportResultsListPageSize(19);
            $pager->setReportResultsSubListPageSize(20);
            $pager->setMassSubscribeProgressPageSize(21);
            $pager->setKanbanBoardPageSize(22);
            $pager->setTaskKanbanBoardPageSize(23);
            $this->assertEquals         (11, $pager->getGlobalValueByType('listPageSize'));
            $this->assertEquals         (12, $pager->getGlobalValueByType('subListPageSize'));
            $this->assertEquals         (13, $pager->getGlobalValueByType('modalListPageSize'));
            $this->assertEquals         (14, $pager->getGlobalValueByType('massEditProgressPageSize'));
            $this->assertEquals         (15, $pager->getGlobalValueByType('autoCompleteListPageSize'));
            $this->assertEquals         (16, $pager->getGlobalValueByType('importPageSize'));
            $this->assertEquals         (17, $pager->getGlobalValueByType('dashboardListPageSize'));
            $this->assertEquals         (18, $pager->getGlobalValueByType('massDeleteProgressPageSize'));
            $this->assertEquals         (19, $pager->getGlobalValueByType('reportResultsListPageSize'));
            $this->assertEquals         (20, $pager->getGlobalValueByType('reportResultsSubListPageSize'));
            $this->assertEquals         (21, $pager->getGlobalValueByType('massSubscribeProgressPageSize'));
            $this->assertEquals         (22, $pager->getGlobalValueByType('kanbanBoardPageSize'));
            $this->assertEquals         (23, $pager->getGlobalValueByType('taskKanbanBoardPageSize'));
            $pager->setGlobalValueByType('listPageSize',                 88);
            $pager->setGlobalValueByType('subListPageSize',              89);
            $pager->setGlobalValueByType('modalListPageSize',            90);
            $pager->setGlobalValueByType('massEditProgressPageSize',     91);
            $pager->setGlobalValueByType('autoCompleteListPageSize',     92);
            $pager->setGlobalValueByType('importPageSize',               93);
            $pager->setGlobalValueByType('dashboardListPageSize',        94);
            $pager->setGlobalValueByType('massDeleteProgressPageSize',   95);
            $pager->setGlobalValueByType('reportResultsListPageSize',    96);
            $pager->setGlobalValueByType('reportResultsSubListPageSize', 97);
            $pager->setGlobalValueByType('massSubscribeProgressPageSize', 98);
            $pager->setGlobalValueByType('kanbanBoardPageSize',          99);
            $pager->setGlobalValueByType('taskKanbanBoardPageSize',      100);
            $this->assertEquals         (88, $pager->getGlobalValueByType('listPageSize'));
            $this->assertEquals         (89, $pager->getGlobalValueByType('subListPageSize'));
            $this->assertEquals         (90, $pager->getGlobalValueByType('modalListPageSize'));
            $this->assertEquals         (91, $pager->getGlobalValueByType('massEditProgressPageSize'));
            $this->assertEquals         (92, $pager->getGlobalValueByType('autoCompleteListPageSize'));
            $this->assertEquals         (93, $pager->getGlobalValueByType('importPageSize'));
            $this->assertEquals         (94, $pager->getGlobalValueByType('dashboardListPageSize'));
            $this->assertEquals         (95, $pager->getGlobalValueByType('massDeleteProgressPageSize'));
            $this->assertEquals         (96, $pager->getGlobalValueByType('reportResultsListPageSize'));
            $this->assertEquals         (97, $pager->getGlobalValueByType('reportResultsSubListPageSize'));
            $this->assertEquals         (98, $pager->getGlobalValueByType('massSubscribeProgressPageSize'));
            $this->assertEquals         (99, $pager->getGlobalValueByType('kanbanBoardPageSize'));
            $this->assertEquals         (100, $pager->getGlobalValueByType('taskKanbanBoardPageSize'));
        }

        public function testSetForCurrentUserByType()
        {
            $sally = User::getByUsername('sally');
            Yii::app()->user->userModel = $sally;
            $pager = new ZurmoPaginationHelper();
            $pager->setForCurrentUserByType('subListPageSize', 44);
            Yii::app()->user->getState('subListPageSizeZurmoModule', 44);
            $this->assertEquals(44, $pager->getByUserAndType($sally, 'subListPageSize'));
        }
    }
?>
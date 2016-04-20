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

    /**
     * Tasks Module Walkthrough spefically testing the kanban board list and updating the task
     * status when you would drag a card from one column to another
     */
    class TasksSuperUserKanbanBoardWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $task;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            self::$task = TaskTestHelper::createTaskByNameForOwner('Main Task', $super);

            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
        }

        public function testSuperUserKanbanBoardListAction()
        {
            $super      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->setGetArray(array('kanbanBoard' => '1'));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default');
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/index');

            $content    = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/list');
            $this->assertContains('anyMixedAttributes', $content);
            $this->assertNotContains('<a id="select-list-attributes-link" href="#">Columns</a>', $content);
            $this->assertContains('<a id="hide-completed-search-link" href="#">Hide Completed</a>', $content);

            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('kanbanBoard' => '1'));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/list');
            $this->assertContains('anyMixedAttributes', $content);
            $this->assertContains('<a id="hide-completed-search-link" href="#">Hide Completed</a>', $content);
            $this->assertNotContains('<a id="select-list-attributes-link" href="#">Columns</a>', $content);
            $this->resetGetArray();

            //Now explicity declare grid and it should be missing
            $this->setGetArray(array('kanbanBoard' => ''));
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $content    = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/list');
            $this->assertContains('anyMixedAttributes', $content);
            $this->assertContains('<a id="hide-completed-search-link" href="#">Hide Completed</a>', $content);
            $this->assertContains('<a id="select-list-attributes-link" href="#">Columns</a>', $content);
        }

        /**
         * @depends testSuperUserKanbanBoardListAction
         */
        public function testUpdateAttributeValueAction()
        {
            $super      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->assertEquals(Task::STATUS_NEW, self::$task->status);

            $this->setGetArray(array('items' => array(self::$task->id), 'type' => '3'));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/updateStatusOnDragInKanbanView');
            $id         = self::$task->id;
            self::$task->forget();
            self::$task = Task::getById($id);
            $this->assertEquals(Task::STATUS_IN_PROGRESS, self::$task->status);
        }

        /**
         * @depends testUpdateAttributeValueAction
         */
        public function testStickySearchActions()
        {
            $super      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            StickySearchUtil::clearDataByKey('TasksKanbanSearchView');
            $value      = StickySearchUtil::getDataByKey('TasksKanbanSearchView');
            $this->assertNull($value);

            $task1      = TaskTestHelper::createTaskByNameForOwner('Task 1', $super);
            $task2      = TaskTestHelper::createTaskByNameForOwner('Task 2', $super);
            // Asserting the kanban board contains both tasks before search
            $this->resetPostArray();
            $this->setGetArray(array(
                'kanbanBoard' => '1',
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/list');
            $this->assertContains('<a id="hide-completed-search-link" href="#">Hide Completed</a>', $content);
            $matcher    = array(
                'tag' => 'h4',
                //Multiple ancestors
                'ancestor' => array('tag' => 'li', 'id' => 'items_' . $task1->id, 'tag' => 'ul', 'id' => 'task-sortable-rows-1'),
                'content' => 'Task 1'
            );
            $this->assertTag($matcher, $content);
            $matcher    = array(
                'tag' => 'h4',
                //Multiple ancestors
                'ancestor' => array('tag' => 'li', 'id' => 'items_' . $task2->id, 'tag' => 'ul', 'id' => 'task-sortable-rows-1'),
                'content' => 'Task 2'
            );
            $this->assertTag($matcher, $content);
            $this->setGetArray(array(
                'formModelClassName' => 'TasksSearchForm',
                'modelClassName' => 'Task',
                'viewClassName' => 'TasksKanbanSearchView',
            ));
            $this->setPostArray(array('TasksSearchForm' =>
                                        array('dynamicClauses' => array(array('attributeIndexOrDerivedType' => 'name',
                                                                        'name' => 'Task 1',
                                                                        'structurePosition' => '1',
                                                                        )),
                                            'anyMixedAttributesScope' => array('All'),
                                            'dynamicStructure' => '1',
                                        ),
                                     'ajax' => 'search-form'));

            $content    = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/validateDynamicSearch', true);
            // Asserting the kanban board contains only one task after search is done
            $this->resetPostArray();
            $this->setGetArray(array(
                'kanbanBoard' => '1',
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/list');
            $this->assertContains('<a id="hide-completed-search-link" href="#">Hide Completed</a>', $content);
            $matcher = array(
                'tag' => 'h4',
                //Multiple ancestors
                'ancestor' => array('tag' => 'li', 'id' => 'items_' . $task1->id, 'tag' => 'ul', 'id' => 'task-sortable-rows-1'),
                'content' => 'Task 1'
            );
            $this->assertTag($matcher, $content);
            $matcher    = array(
                'tag' => 'h4',
                //Multiple ancestors
                'ancestor' => array('tag' => 'li', 'id' => 'items_' . $task2->id, 'tag' => 'ul', 'id' => 'task-sortable-rows-1'),
                'content' => 'Task 2'
            );
            $this->assertNotTag($matcher, $content);
            $data       = StickySearchUtil::getDataByKey('TasksKanbanSearchView');
            $compareData = array(
                'dynamicClauses' => array(array('attributeIndexOrDerivedType' => 'name',
                                                                        'name' => 'Task 1',
                                                                        'structurePosition' => '1',
                                                                        )),
                'dynamicStructure'                   => '1',
                'anyMixedAttributesScope'            => null,
                SearchForm::SELECTED_LIST_ATTRIBUTES => null,
                'savedSearchId'                      => null,
            );
            $this->assertEquals($compareData, $data);
        }
    }
?>
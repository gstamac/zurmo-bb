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

    Yii::import('application.modules.projects.controllers.DefaultController', true); 
    class ProjectsDemoController extends ProjectsDefaultController
    {
        /**
         * Special method to load projects for functional test.
         */
        public function actionLoadProjectsSampler()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }

            for ($i = 1; $i <= 8; $i++)
            {
                $projectName     = $i . ' Test Project';
                $project         = new Project();
                $project->name   = $projectName;
                $project->status = Project::STATUS_ACTIVE;
                $project->owner  = Yii::app()->user->userModel;
                $saved           = $project->save();
                assert('$saved');
                if (!$saved)
                {
                    throw new NotSupportedException();
                }
                self::addDemoTasks($project);
                sleep(2);
            }
        }
        
        /**
         * Add demo tasks for the project
         * @param type $project
         */
        protected static function addDemoTasks($project)
        {
            for ($i = 1; $i <= 5; $i++)
            {
                $taskName   = $i . " Test Task";
                $task       = new Task();
                $task->name = $taskName;
                
                switch ($i) 
                {
                    case 1:
                        $task->status = Task::STATUS_NEW;
                        break;
                    case 2:
                        $task->status = Task::STATUS_IN_PROGRESS;
                        break;
                    case 3:
                        $task->status = Task::STATUS_AWAITING_ACCEPTANCE;
                        break;
                    case 4:
                        $task->status = Task::STATUS_REJECTED;
                        break;
                    default:
                        $task->status = Task::STATUS_COMPLETED;
                        $task->completedDateTime    = '0000-00-00 00:00:00';
                }
                
                $task->requestedByUser      = Yii::app()->user->userModel;
                $task->owner                = Yii::app()->user->userModel;
                $task->project              = $project; 
                $task->save();
            }
        }
    }
?>
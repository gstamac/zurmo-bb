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

    Yii::import('application.modules.workflows.controllers.DefaultController', true);

    /**
     * Controller for running demo actions that are used for development work.
     */
    class WorkflowsDemoController extends WorkflowsDefaultController
    {
        /**
         * Special method to load ByTimeWorkflowInQueue models
         */
        public function actionLoadByTimeWorkflowInQueue()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            $model                          = new Account();
            $model->name                    = 'test account';
            $saved                          = $model->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'Test for demo Time Queue model';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_BY_TIME;
            $savedWorkflow->isActive        = false;
            $savedWorkflow->order           = 1;
            $savedWorkflow->serializedData  = serialize(array(
                ComponentForWorkflowForm::TYPE_TRIGGERS        => array(),
                ComponentForWorkflowForm::TYPE_ACTIONS         => array(),
                ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES  => array(),
            ));
            $saved                          = $savedWorkflow->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            for ($i = 1; $i < 6; $i++)
            {
                $byTimeWorkflowInQueue                  = new ByTimeWorkflowInQueue();
                $byTimeWorkflowInQueue->modelClassName  = get_class($model);
                $byTimeWorkflowInQueue->modelItem       = $model;
                $byTimeWorkflowInQueue->processDateTime = '2007-02-0' . $i . ' 00:00:00';
                $byTimeWorkflowInQueue->savedWorkflow   = $savedWorkflow;
                $saved = $byTimeWorkflowInQueue->save();
                if (!$saved)
                {
                    throw new NotSupportedException();
                }
            }
        }

        /**
         * Special method to load ByTimeWorkflowInQueue models
         */
        public function actionLoadWorkflowMessageInQueue()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            $model                          = new Account();
            $model->name                    = 'test account';
            $saved                          = $model->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'Test for demo Message Queue model';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_BY_TIME;
            $savedWorkflow->isActive        = false;
            $savedWorkflow->order           = 1;
            $savedWorkflow->serializedData  = serialize(array(
                ComponentForWorkflowForm::TYPE_TRIGGERS        => array(),
                ComponentForWorkflowForm::TYPE_ACTIONS         => array(),
                ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES  => array(),
            ));
            $saved                          = $savedWorkflow->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            for ($i = 1; $i < 6; $i++)
            {
                $workflowMessageInQueue                  = new WorkflowMessageInQueue();
                $workflowMessageInQueue->processDateTime = '2014-03-0' . $i . ' 00:00:00';
                $workflowMessageInQueue->savedWorkflow   = $savedWorkflow;
                $workflowMessageInQueue->modelClassName  = get_class($model);
                $workflowMessageInQueue->modelItem       = $model;
                $workflowMessageInQueue->serializedData  = serialize(array());
                $workflowMessageInQueue->triggeredByUser = Yii::app()->user->userModel;
                $saved = $workflowMessageInQueue->save();
                if (!$saved)
                {
                    throw new NotSupportedException();
                }
            }
        }
    }
?>

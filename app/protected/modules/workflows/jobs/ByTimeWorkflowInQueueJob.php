<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * A job for processing expired By-Time workflow objects
     */
    class ByTimeWorkflowInQueueJob extends BaseJob
    {
        /**
         * @var int
         */
        protected static $pageSize = 200;

        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('WorkflowsModule', 'Process by-time workflow rules');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'ByTimeWorkflowInQueue';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('JobsManagerModule', 'Every 15 minutes');
        }

        /**
         * @see BaseJob::run()
         */
        public function run()
        {
            try
            {
                $originalUser               = Yii::app()->user->userModel;
                Yii::app()->user->userModel = BaseJobControlUserConfigUtil::getUserToRunAs();
                foreach (ByTimeWorkflowInQueue::getModelsToProcess(self::$pageSize) as $byTimeWorkflowInQueue)
                {
                    try
                    {
                        $model = $this->resolveModel($byTimeWorkflowInQueue);
                        $this->resolveSavedWorkflowIsValid($byTimeWorkflowInQueue);
                        $this->processByTimeWorkflowInQueue($byTimeWorkflowInQueue, $model);
                    }
                    catch (NotFoundException $e)
                    {
                        WorkflowUtil::handleProcessingException($e,
                            'application.modules.workflows.jobs.ByTimeWorkflowInQueueJob.run');
                    }
                    $byTimeWorkflowInQueue->delete();
                }
                Yii::app()->user->userModel = $originalUser;
                return true;
            }
            catch (MissingASuperAdministratorException $e)
            {
                //skip running workflow, since no super administrators are available.
                $this->errorMessage = Zurmo::t('WorkflowsModule', 'Could not process since no super administrators were found');
                return false;
            }
        }

        /**
         * @param ByTimeWorkflowInQueue $byTimeWorkflowInQueue
         * @return RedBeanModel
         */
        protected function resolveModel(ByTimeWorkflowInQueue $byTimeWorkflowInQueue)
        {
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($byTimeWorkflowInQueue->modelClassName);
            return $byTimeWorkflowInQueue->modelItem->castDown(array($modelDerivationPathToItem));
        }

        /**
         * @param ByTimeWorkflowInQueue $byTimeWorkflowInQueue
         * @throws NotFoundException
         */
        protected function resolveSavedWorkflowIsValid(ByTimeWorkflowInQueue $byTimeWorkflowInQueue)
        {
            if ($byTimeWorkflowInQueue->savedWorkflow->id < 0)
            {
                throw new NotFoundException();
            }
        }

        /**
         * @param ByTimeWorkflowInQueue $byTimeWorkflowInQueue
         * @param RedBeanModel $model
         * @throws FailedToSaveModelException
         */
        protected function processByTimeWorkflowInQueue(ByTimeWorkflowInQueue $byTimeWorkflowInQueue, RedBeanModel $model)
        {
            $workflow = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($byTimeWorkflowInQueue->savedWorkflow);
            if (!$workflow->getIsActive())
            {
                return;
            }
            $workflow->setTimeTriggerRequireChangeToProcessToFalse();
            if (WorkflowTriggersUtil::areTriggersTrueOnByTimeWorkflowQueueJob($workflow, $model))
            {
                WorkflowActionsUtil::processOnByTimeWorkflowInQueueJob($workflow, $model, Yii::app()->user->userModel);
                WorkflowEmailMessagesUtil::processAfterSave($workflow, $model, Yii::app()->user->userModel);
                if ($model->isModified())
                {
                    $saved = $model->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
        }
    }
?>
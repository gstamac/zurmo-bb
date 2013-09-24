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
     * Column adapter for account for product list in portlet
     */
    class ProjectFeedListViewColumnAdapter extends TextListViewColumnAdapter
    {
        /**
         * Get message templates
         * @return array
         */
        public static function getMessageTemplates()
        {
            $projectNameTemplate = '<strong><i>{projectname}</i></strong>';
            $userNameTemplate = '<strong>{username}</strong>';
            $taskNameTemplate = '<strong>{taskname}</strong>';
            return array(
                ProjectAuditEvent::PROJECT_CREATED  => $projectNameTemplate  . ' ' .
                                                        Zurmo::t('ProjectsModule', ' is added by user')  . ' ' .
                                                        $userNameTemplate,
                ProjectAuditEvent::PROJECT_ARCHIVED =>  $projectNameTemplate  . ' ' .
                                                        Zurmo::t('ProjectsModule', ' is archived by user')  . ' ' .
                                                        $userNameTemplate,
                ProjectAuditEvent::TASK_COMPLETED   => $projectNameTemplate  . ' ' .
                                                        $taskNameTemplate  . ' ' .
                                                        Zurmo::t('ProjectsModule', 'is marked as completed by user')  . ' ' .
                                                        $userNameTemplate,
            );
        }

        public function renderGridViewData()
        {
            return array(
                    'name'  => $this->attribute,
                    'value' => 'ProjectFeedListViewColumnAdapter::getFeedInformationForDashboard($data)',
                    'type'  => 'raw'
                );
        }

        /**
         * Get feed information if projects for user
         * @param array $data
         * @return string
         */
        public static function getFeedInformationForDashboard($data)
        {
            $project = Project::getById($data->project->id);
            $projectName = ZurmoHtml::link($project->name, Yii::app()->createUrl('projects/default/details', array('id' => $project->id)));
            $content = null;
            $user    = User::getById($data->user->id);
            $unserializedData  = unserialize($data->serializedData);
            $messageTemplates  = self::getMessageTemplates();
            switch($data->eventName)
            {
                case ProjectAuditEvent::PROJECT_CREATED:
                    $content .= str_replace(array('{projectname}', '{username}'),
                                            array($projectName, $user->getFullName()),
                                            $messageTemplates[ProjectAuditEvent::PROJECT_CREATED]);
                    break;

                case ProjectAuditEvent::PROJECT_ARCHIVED:
                    $content .= str_replace(array('{projectname}', '{username}'),
                                            array($projectName, $user->getFullName()),
                                            $messageTemplates[ProjectAuditEvent::PROJECT_ARCHIVED]);
                    break;

                case ProjectAuditEvent::TASK_COMPLETED:
                    $content .= str_replace(array('{projectname}', '{taskname}', '{username}'),
                                            array($projectName, $unserializedData, $user->getFullName()),
                                            $messageTemplates[ProjectAuditEvent::PROJECT_ARCHIVED]);
                    break;
            }

            $content .=  '<small>' . Zurmo::t('ProjectsModule', ' about ' ) .
                         ProjectAuditEvent::getTimeDifference($data->dateTime) .
                         Zurmo::t('ProjectsModule', ' ago');
            return $content;
        }

        /**
         * Resolve project name with link to details url
         * @param Project $project
         * @return string
         */
        protected function resolveProjectName($project)
        {
            return ZurmoHtml::link($project->name, Yii::app()->createUrl('projects/default/details', array('id' => $project->id)));
        }
    }
?>
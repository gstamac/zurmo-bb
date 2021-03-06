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
     * Helper class for working with projects notification
     */
    class ProjectsNotificationUtil extends NotificationsUtil
    {
        /**
         * Submit project notification message
         * @param Project $project
         * @param string $action
         * @param Task $task
         * @param null|User $relatedUser, the user associated with the project notification.
         */
        public static function submitProjectNotificationMessage(Project $project, $action, Task $task = null,
                                                                User $relatedUser = null)
        {
            assert('is_string($action)');
            $message = static::getNotificationMessageByAction($project, $action, $task, $relatedUser);
            $notificationRulesClassName = static::resolveNotificationRulesClassByAction($action);
            $rule = new $notificationRulesClassName();
            $peopleToSendNotification = static::resolvePeopleToSendNotification($project, $action);
            foreach ($peopleToSendNotification as $person)
            {
                $rule->addUser($person);
            }
            $rule->setModel($project);
            $rule->setAdditionalModel($task);
            $rule->setAllowDuplicates(true);
            static::processProjectNotification($message, $rule, $action);
        }

        /**
         * Process project notification
         * @param NotificationMessage $message
         * @param ProjectNotificationRules $rule
         * @param string $action
         */
        protected static function processProjectNotification(NotificationMessage $message, ProjectNotificationRules $rule, $action)
        {
            assert('is_string($action)');
            $users = $rule->getUsers();
            //This scenario would be there when there is only one subscriber. In that case users would
            //be zero
            if (count($users) == 0)
            {
                return;
            }
            $notifications = static::resolveAndGetNotifications($message, $rule);
            if (static::resolveShouldSendEmailIfCritical())
            {
                foreach ($notifications as $notification)
                {
                    static::sendProjectEmail($notification, $rule, $action);
                }
            }
        }

        /**
         * Gets notification message by action
         * @param Project $project
         * @param $action
         * @param Task $task
         * @param User $relatedUser
         * @return NotificationMessage
         */
        protected static function getNotificationMessageByAction(Project $project, $action, Task $task = null,
                                                                 User $relatedUser = null)
        {
            assert('is_string($action)');
            $message                     = new NotificationMessage();
            $messageContent              = static::getEmailMessageContent($project, $action, $task, $relatedUser);
            $url                         = Yii::app()->createAbsoluteUrl('projects/default/details/',
                                           array('id' => $project->id));
            $message->textContent        = $messageContent . "\n";
            $message->textContent       .= ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url, array('target' => '_blank'));
            $message->htmlContent        = $messageContent . "<br/>";
            $message->htmlContent       .= ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url, array('target' => '_blank'));
            return $message;
        }

        /**
         * Gets notification subscribers
         * @param Project $project
         * @param $action
         * @return array
         */
        public static function resolvePeopleToSendNotification(Project $project, $action)
        {
            assert('is_string($action)');
            $peopleToSendNotification = array();
            if ($action == ProjectAuditEvent::PROJECT_CREATED ||
                    $action == ProjectAuditEvent::TASK_ADDED ||
                    $action == ProjectAuditEvent::PROJECT_ARCHIVED)
            {
                $peopleToSendNotification[] = $project->owner;
            }
            return $peopleToSendNotification;
        }

        /**
         * Gets email message for the notification
         * @param Project $project
         * @param $action
         * @param Task $task
         * @param User $relatedUser
         * @return string
         */
        public static function getEmailMessageContent(Project $project, $action, Task $task = null, User $relatedUser = null)
        {
            assert('is_string($action)');
            if ($action == ProjectAuditEvent::PROJECT_CREATED)
            {
                return Zurmo::t('ProjectsModule', "The project, '{project}', is now owned by you.",
                                               array('{project}'   => strval($project)));
            }
            elseif ($action == ProjectAuditEvent::TASK_ADDED)
            {
                return Zurmo::t('ProjectsModule', "New task, {task}, was created for project, '{project}'. Created by {user}",
                                               array('{task}' => strval($task),
                                                     '{project}' => strval($project),
                                                     '{user}' => strval($relatedUser)));
            }
            elseif ($action == ProjectAuditEvent::PROJECT_ARCHIVED)
            {
                return Zurmo::t('ProjectsModule', "The project, '{project}', is now archived.",
                                               array('{project}'   => strval($project)));
            }
        }

        /**
         * Send task email
         * @param Notification $notification
         * @param ProjectNotificationRules $rule
         * @param string $action
         */
        protected static function sendProjectEmail(Notification $notification, ProjectNotificationRules $rule, $action)
        {
            assert('is_string($action)');
            $notificationSettingName = static::resolveNotificationSettingNameFromType($rule->getType());
            if ($notification->owner->primaryEmail->emailAddress !== null &&
                UserNotificationUtil::isEnabledByUserAndNotificationNameAndType($notification->owner,
                                                                                $notificationSettingName, 'email'))
            {
                $emailMessage               = static::makeEmailMessage();
                $emailMessage->subject      = static::getEmailSubject($notification, $rule);
                $emailMessage->content      = static::makeEmailContent($notification);
                $emailMessage->sender       = static::makeSender();
                $emailMessage->recipients->add(static::makeRecipient($notification));
                $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                try
                {
                    Yii::app()->emailHelper->sendImmediately($emailMessage);
                }
                catch (CException $e)
                {
                    //Not sure what to do yet when catching an exception here. Currently ignoring gracefully.
                }
            }
        }

        /**
         * Resolve the notification rules class name by action name
         * @param string $action
         * @return string
         */
        protected static function resolveNotificationRulesClassByAction($action)
        {
            assert('is_string($action)');
            switch ($action)
            {
                case ProjectAuditEvent::PROJECT_CREATED:
                    return 'NewProjectNotificationRules';
                    break;
                case ProjectAuditEvent::TASK_ADDED:
                    return 'ProjectTaskAddedNotificationRules';
                    break;
                case ProjectAuditEvent::PROJECT_ARCHIVED:
                    return 'ArchivedProjectNotificationRules';
                    break;
                default:
                    throw new NotFoundException();
            }
        }
    }
?>
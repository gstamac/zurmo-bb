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

    /**
     * User interface element for selecting multiple groups and users using type ahead element
     *
     */
    class MultipleGroupsAndUsersElement extends MultiSelectRelatedModelsAutoCompleteElement
    {
        protected function getFormName()
        {
            return null;
        }

        protected function getUnqualifiedNameForIdField()
        {
            return '[GroupsAndUsers][ids]';
        }

        protected function getUnqualifiedIdForIdField()
        {
            return '_GroupsAndUsers_ids';
        }

        protected function assertModelType()
        {
            assert('$this->model instanceof RedBeanModel');
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Zurmo::t('ZurmoModule', 'Groups And Users'));
        }

        public static function getDisplayName()
        {
            return Zurmo::t('ZurmoModule', 'Groups And Users');
        }

        protected function getWidgetSourceUrl()
        {
            return  Yii::app()->createUrl('home/default/autoCompleteGroupsAndUsers');
        }

        protected function getWidgetHintText()
        {
            return Zurmo::t('ZurmoModule', 'Type a group name or user email address',
                                            LabelUtil::getTranslationParamsForAllModules());
        }

        protected function getRelatedRecords()
        {
            return array();
        }

        protected function getRelationName()
        {
            return null;
        }

        protected function getEditableInputId($attributeName = null, $relationAttributeName = null)
        {
            $inputPrefix = $this->resolveInputIdPrefix();
            return $inputPrefix . $this->getUnqualifiedIdForIdField();
        }

        protected function getEditableInputName($attributeName = null, $relationAttributeName = null)
        {
            $inputPrefix = $this->resolveInputNamePrefix();
            return $inputPrefix . $this->getUnqualifiedNameForIdField();
        }

        protected function getExistingIdsAndLabels()
        {
            $existingRecords  = array();
            $pushedDashboards = ZurmoConfigurationUtil::getByModuleName('HomeModule',
                                HomeDefaultController::PUSHED_DASHBOARDS_KEY);
            if ($pushedDashboards != null)
            {
                $pushedDashboards = unserialize($pushedDashboards);
                $dashboardId      = Yii::app()->getRequest()->getQuery('id');
                if (isset($pushedDashboards[$dashboardId]))
                {
                    $groupIds = explode(',', $pushedDashboards[$dashboardId]['groups']);
                    $userIds  = explode(',', $pushedDashboards[$dashboardId]['users']);
                    foreach ($groupIds as $groupId)
                    {
                        $existingRecords[] = array('id'   => HomeDefaultController::GROUP_PREFIX . $groupId,
                                                   'name' => strval(Group::getById(intval($groupId))));
                    }
                    foreach ($userIds as $userId)
                    {
                        $user = User::getById(intval($userId));
                        $existingRecords[] = array('id'   => HomeDefaultController::USER_PREFIX . $groupId,
                                                   'name' => MultipleContactsForMeetingElement::
                                                             renderHtmlContentLabelFromUserAndKeyword($user, null));
                    }
                }
            }
            return $existingRecords;
        }
    }
?>
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

    /**
     * Display the mission status with the action button when applicable.
     */
    class MissionStatusElement extends Element implements DerivedElementInterface
    {
        protected function renderEditable()
        {
            throw NotSupportedException();
        }

        protected function renderControlEditable()
        {
            throw NotSupportedException();
        }

        /**
         * Render the full name as a non-editable display
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            assert('$this->attribute == "null"');
            assert('$this->model instanceof Mission');
            return self::renderStatusTextAndActionArea($this->model);
        }

        public static function renderStatusTextAndActionArea(Mission $mission)
        {
            $statusChangeDivId = 'MissionStatusChangeArea';
            $statusText        = self::renderStatusTextContent($mission);
            $statusAction      = self::renderStatusActionContent($mission, $statusChangeDivId);
            $content = $statusText;
            if($statusAction != null)
            {
                $content . ' ' . $statusAction;
            }
            return ZurmoHtml::tag('div', array('id' => $statusChangeDivId), $content);
        }

        public static function renderStatusTextContent(Mission $mission)
        {
            if($mission->status == Mission::STATUS_OPEN)
            {
                return Yii::t('Default', 'Available');
            }
            elseif($mission->status == Mission::STATUS_TAKEN)
            {
                return Yii::t('Default', 'In Progress');
            }
            elseif($mission->status == Mission::STATUS_COMPLETED)
            {
                return Yii::t('Default', 'Awaiting Acceptance');
            }
            elseif($mission->status == Mission::STATUS_REJECTED)
            {
                return Yii::t('Default', 'Rejected');
            }
            elseif($mission->status == Mission::STATUS_ACCEPTED)
            {
                return Yii::t('Default', 'Accepted');
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public static function renderStatusActionContent(Mission $mission, $updateDivId)
        {
            assert('is_string($updateDivId');
            if($mission->status == Mission::STATUS_OPEN &&
               !$mission->owner->isSame(Yii::app()->user->userModel))
            {
                return self::renderAjaxStatusActionChangeLink(Mission::STATUS_TAKEN, $mission->id,
                                                              Yii::t('Default', 'Start'), $updateDivId);
            }
            elseif($mission->status == Mission::STATUS_TAKEN &&
                   $mission->takenByUser->isSame(Yii::app()->user->userModel))
            {
                return self::renderAjaxStatusActionChangeLink(Mission::STATUS_COMPLETED, $mission->id,
                                                              Yii::t('Default', 'Complete'), $updateDivId);
            }
            elseif($mission->status == Mission::STATUS_COMPLETED &&
                   $mission->owner->isSame(Yii::app()->user->userModel))
            {
                $content  = self::renderAjaxStatusActionChangeLink(      Mission::STATUS_ACCEPTED, $mission->id,
                                                                         Yii::t('Default', 'Accept'), $updateDivId);
                $content .= ' ' . self::renderAjaxStatusActionChangeLink(Mission::STATUS_REJECTED, $mission->id,
                                                                         Yii::t('Default', 'Reject'), $updateDivId);
                return $content;
            }
            elseif($mission->status == Mission::STATUS_REJECTED &&
                   $mission->takenByUser->isSame(Yii::app()->user->userModel))
            {
                return self::renderAjaxStatusActionChangeLink(Mission::STATUS_COMPLETED, $mission->id,
                                                              Yii::t('Default', 'Complete'), $updateDivId);
            }
        }

        protected static function renderAjaxStatusActionChangeLink($newStatus, $missionId, $label, $updateDivId)
        {
            assert('is_int($newStatus)');
            assert('is_int($missionId)');
            assert('is_string($label)');
            assert('is_string($updateDivId');
            $url     =   Yii::app()->createUrl('missions/default/ajaxChangeStatus',
                                               array('status' => $newStatus, 'id' => $missionId));
            return       ZurmoHtml::ajaxLink($label, $url,
                         array('type' => 'GET',
                               'success'    => 'function(data){$("#' . $updateDivId . '").replaceWith(data)}'),
                         array('class'      => 'mission-change-status-link',
                                'namespace' => 'update'));
        }

        protected function renderLabel()
        {
            return Yii::t('Default', 'Status');
        }

        public static function getDisplayName()
        {
            return Yii::t('Default', 'Status');
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                'status',
            );
        }
    }
?>
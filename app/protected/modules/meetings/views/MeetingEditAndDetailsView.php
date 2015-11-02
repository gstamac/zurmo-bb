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

    class MeetingEditAndDetailsView extends SecuredEditAndDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'SaveButton',  'renderType' => 'Edit'),
                            array('type' => 'CancelLink', 'renderType' => 'Edit'),
                            array('type' => 'MeetingDeleteLink'),
                            array('type' => 'EditLink',    'renderType' => 'Details'),
                            array('type' => 'AuditEventsModalListLink', 'renderType' => 'Details'),

                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'ActivityItemsExcludingContacts',
                        'MultipleContactsForMeeting',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'processedForLatestActivity',
                        'latestDateTime',
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'location', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'startDateTime', 'type' => 'DateTime'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'endDateTime', 'type' => 'DateTime'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'category', 'type' => 'DropDown', 'addBlank' => true),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName'   => 'null',
                                                      'type'            => 'MultipleContactsForMeeting',
                                                ),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'ActivityItemsExcludingContacts'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'description', 'type' => 'TextArea'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                         ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function getNewModelTitleLabel()
        {
            return Zurmo::t('MeetingsModule', 'Create MeetingsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules());
        }

        protected function renderAfterFormLayout($form)
        {
            $content = parent::renderAfterFormLayout($form);
            $this->registerSetMeetingEndDateTimeScript($form);
            return $content;
        }

        protected function registerSetMeetingEndDateTimeScript($form)
        {
            $url     =   Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/getMeetingEndDateTimeBasedOnStartDateTime');
            $meetingStartDateTimeId        = Element::resolveInputIdPrefixIntoString(array(get_class($this->model), 'startDateTime'));
            $meetingEndDateTimeId          = Element::resolveInputIdPrefixIntoString(array(get_class($this->model), 'endDateTime'));

            // Fill endDateTime only for new meetings
            if ($this->model->id <= 0)
            {
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript('SetMeetingEndDateTime', "
                    $('#" . $meetingStartDateTimeId . "').change(function()
                    {
                        $.ajax(
                        {
                            url : '" . $url . "',
                            type : 'GET',
                            data : {
                                startDateTime : $('#" . $meetingStartDateTimeId . "').val()
                            },
                            dataType: 'json',
                            success : function(data)
                            {
                                if (data['endDateTime'] && data['endDateTime'].length > 0)
                                {
                                    $('#" . $meetingEndDateTimeId . "').val(data['endDateTime']);
                                }
                            },
                            error : function()
                            {
                            }
                        }
                        );
                    });
                ");
                // End Not Coding Standard
            }
        }
        
        public static function getDesignerRulesType()
        {
            return 'DetailsViewOnlyForUserOwnerEditAndDetailsView';
        }
    }
?>
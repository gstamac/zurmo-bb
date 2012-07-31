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

    class HomeDashboardView extends DashboardView
    {
        public function isUniqueToAPage()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'CreateDashboardLink',
                                'htmlOptions' => array('class' => 'icon-create')
                                ),
                            array('type' => 'EditDashboardLink',
                                'htmlOptions' => array('class' => 'icon-edit')
                                ),
                            array('type' => 'AddPortletAjaxLink',
                                'uniqueLayoutId' => 'eval:$this->uniqueLayoutId',
                                'ajaxOptions' => 'eval:static::resolveAjaxOptionsForAddPortlet()',
                                'htmlOptions' => array('id' => 'AddPortletLink',
                                    'class' => 'icon-add'
                                )
                            ),
                        ),
                    ),
                    'columns' => array(
                        array(
                            'rows' => array(
                               array(
                                    'type' => 'MyUpcomingMeetingsCalendar',
                                ),
                                array(
                                    'type' => 'AllLatestActivtiesForPortlet',
                                ),
                                array(
                                    'type' => 'TasksMyList',
                                ),
                            )
                        ),
                        array(
                            'rows' => array(
                                array(
                                    'type' => 'OpportunitiesByStageChart',
                                ),
                                array(
                                    'type' => 'OpportunitiesBySourceChart',
                                ),
                                array(
                                    'type' => 'MyMissionsForPortlet',
                                ),
                                array(
                                    'type' => 'RssReader',
                                ),
                            )
                        )
                    )
                )
            );
            return $metadata;
        }

        protected static function resolveAjaxOptionsForAddPortlet()
        {
            $title = Yii::t('Default', 'Add Portlet');
            return ModalView::getAjaxOptionsForModalLink($title);
        }
    }
?>

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

    class TasksKanbanSearchView extends TasksSearchView
    {
        protected function renderConfigSaveAjax($formName)
        {
            $gridId = 'overall-tasks-kanban-view';
            return "var inputId = '" . static::getSavedSearchListDropDown() . "';
                    if (data.id != undefined)
                    {
                        var existingSearchFound = false;
                        $('#' + inputId + ' > option').each(function()
                        {
                           if (this.value == data.id)
                           {
                               $('#' + inputId + ' option[value=\'' + this.value + '\']').text(data.name);
                               existingSearchFound = true;
                           }
                        });
                        if (!existingSearchFound)
                        {
                            $('#' + inputId).removeClass('ignore-style');
                            $('#' + inputId)
                                .append($('<option></option>')
                                .attr('value', data.id)
                                .text(data.name))
                            //$('#' + inputId).val(data.id); Do not select new saved search since it is not sticky at this point.
                            $('#" . get_class($this->model) . "_savedSearchId').val(data.id);
                        }
                    }
                    $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading');
                    $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading-ajax-submit');
                    $('#" . $formName . "').find('.attachLoadingTarget').removeClass('attachLoadingTarget');" .

                    "$(this).closest('form').find('.search-view-1').hide();
                    $('.select-list-attributes-view').hide();
                    $('#" . $formName . "').find('.attachLoading:first').removeClass('loading');
                    $('#" . $formName . "').find('.attachLoading:first').removeClass('loading-ajax-submit');
                    $('#" . $gridId . "-selectedIds').val(null);
                    $.fn.yiiGridView.update('" . $gridId . "',
                    {
                        data: '" . $this->listModelClassName . "_page=&" . // Not Coding Standard
                        $this->listModelClassName . "_sort=" .
                        $this->getExtraQueryPartForSearchFormScriptSubmitFunction() ."' // Not Coding Standard
                     }
                    );
                    $('#" . $this->getClearingSearchInputId() . "').val('');
                    ";
        }

        protected function renderSavedSearchList()
        {
            $savedSearches = SavedSearch::getByOwnerAndViewClassName(Yii::app()->user->userModel, get_class($this));
            $idOrName      = static::getSavedSearchListDropDown();
            $htmlOptions   = array('id' => $idOrName, 'empty' => Zurmo::t('ZurmoModule', 'Load a saved search'));
            if (KanbanUtil::isKanbanRequest())
            {
                $params['kanbanBoard'] = 1;
                $this->model->loadSavedSearchUrl = Yii::app()->createUrl('tasks/default/list/', $params);
            }
            if (count($savedSearches) == 0)
            {
                $htmlOptions['style'] = "display:none;";
                $htmlOptions['class'] = 'ignore-style';
                $idOrName      = static::getSavedSearchListDropDown();
                $htmlOptions   = array('id' => $idOrName, 'empty' => Zurmo::t('ZurmoModule', 'Load a saved search'));
                $content       = ZurmoHtml::dropDownList($idOrName,
                                                     $this->model->savedSearchId,
                                                     self::resolveSavedSearchesToIdAndLabels($savedSearches),
                                                     $htmlOptions);
                $this->renderSavedSearchDropDownOnChangeScript($idOrName, $this->model->loadSavedSearchUrl);
                return $content;
            }
            $content       = ZurmoHtml::dropDownList($idOrName,
                                                 $this->model->savedSearchId,
                                                 self::resolveSavedSearchesToIdAndLabels($savedSearches),
                                                 $htmlOptions);
            $this->renderSavedSearchDropDownOnChangeScript($idOrName, $this->model->loadSavedSearchUrl);
            return $content;
        }

        /**
         * @return string
         */
        protected function getKanbanBoardOptionsLinkContent()
        {
            return null;
        }
    }
?>
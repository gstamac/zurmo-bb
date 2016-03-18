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

    class TasksSearchView extends SavedDynamicSearchView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'locked' => true,
                            'title'  => 'Basic Search',
                            'rows'   => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'anyMixedAttributes',
                                                      'type' => 'AnyMixedAttributesSearch', 'wide' => true),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                        array(
                            'advancedSearchType' => static::ADVANCED_SEARCH_TYPE_DYNAMIC,
                            'rows'   => array(),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public static function getModelForMetadataClassName()
        {
            return 'TasksSearchForm';
        }
        
        protected function renderFormBottomPanelExtraLinks()
        {
            $content = parent::renderFormBottomPanelExtraLinks();
            $content .= $this->renderHideOlderCompletedItemsInputContent();
            return $content;
        }
        
        protected function renderHideOlderCompletedItemsInputContent()
        {
            $modelClassName = $this->listModelClassName;
            $model = new $modelClassName;
            if ($this->showAdvancedSearch && $model instanceof Task)
            {
                $content = ZurmoHtml::link(Zurmo::t('Core', 'Hide Completed'), '#', 
                                                    array('id' => 'hide-completed-search-link' . $this->gridIdSuffix));
                $content .= $this->renderTooltipContentForHideOlderCompletedItems();
                return $content;
            }
        }

        protected function renderTooltipContentForHideOlderCompletedItems()
        {
            $title       = 'Hide items which have been completed for more than 30 days.';
            $content     = ZurmoHtml::tag('span',
                                          array('id'    => 'HideOlderCompletedItems',
                                                'class' => 'tooltip',
                                                'title' => $title,
                                               ),
                                          '?');
            $qtip        = new ZurmoTip();
            $qtip->addQTip("#HideOlderCompletedItems");
            return $content;
        }
        
        protected function agetExtraRenderFormBottomPanelScriptPart()
        {
            $script = parent::agetExtraRenderFormBottomPanelScriptPart();
            $modelClassName = $this->listModelClassName;
            $model = new $modelClassName;
            if ($this->showAdvancedSearch && $model instanceof Task)
            {
                // Begin Not Coding Standard
                $script .= "
                    $('#hide-completed-search-link" . $this->gridIdSuffix . "').unbind('click');
                    $('#hide-completed-search-link" . $this->gridIdSuffix . "').bind('click',  function(event){
                        $(this).closest('form').find('.search-view-1').show();
                        if (!hasHideCompletedItemsFieldAlreadySelected())
                        {
                            var rowCounter = $('#rowCounter-search-form').val();
                            $('#addExtraAdvancedSearchRowButton-" . $this->getSearchFormId() . "').click();
                            var checkExist = setInterval(function() {
                                if ($('#" . get_class($this->model) . "_dynamicClauses_' + rowCounter + '_attributeIndexOrDerivedType').length) {
                                    $('#" . get_class($this->model) . "_dynamicClauses_' + rowCounter + '_attributeIndexOrDerivedType').val('hideOlderCompletedItems').change();
                                    clearInterval(checkExist);
                                    var checkExist2 = setInterval(function() {
                                        if ($('#" . get_class($this->model) . "_dynamicClauses_' + rowCounter + '_hideOlderCompletedItems').length) {
                                            $('#" . get_class($this->model) . "_dynamicClauses_' + rowCounter + '_hideOlderCompletedItems').click();
                                            $('#" . get_class($this->model) . "_dynamicClauses_' + rowCounter + '_hideOlderCompletedItems').parent().addClass('c_on');

                                            clearInterval(checkExist2);
                                        }
                                    }, 20)
                                }
                            }, 20);
                        }
                        return false;
                    }
                    );
                    function hasHideCompletedItemsFieldAlreadySelected()
                    {
                        var hasHideCompletedItemsField = false;
                        $('#hide-completed-search-link').closest('form').find('.attribute-dropdown').each(function() {
                          console.log($(this).val());
                            if ($(this).val() == 'hideOlderCompletedItems')
                            {
                              hasHideCompletedItemsField = true
                              return false; // To break each loop
                            }
                        })
                        return hasHideCompletedItemsField;
                    }
                ";
                // End Not Coding Standard
            }
            return $script;
        }
    }
?>
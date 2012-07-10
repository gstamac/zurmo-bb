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
     * Supports dyanmic advanced search.  This is where the user can decide the fields to filter on.
     */
    abstract class DynamicSearchView extends SearchView
    {
        const ADVANCED_SEARCH_TYPE_STATIC  = 'Static';

        const ADVANCED_SEARCH_TYPE_DYNAMIC = 'Dynamic';

        public static function getDesignerRulesType()
        {
            return 'DynamicSearchView';
        }

        /**
         * Constructs a detail view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($model, $listModelClassName, $gridIdSuffix = null, $hideAllSearchPanelsToStart = false)
        {
            assert('$model instanceof DynamicSearchForm');
            parent::__construct($model, $listModelClassName, $gridIdSuffix, $hideAllSearchPanelsToStart);
        }

        protected function getClientOptions()
        {
            return array(
                        'validateOnSubmit'  => true,
                        'validateOnChange'  => false,
                        'beforeValidate'    => 'js:beforeValidateAction',
                        'afterValidate'     => 'js:afterDynamicSearchValidateAjaxAction',
                        'afterValidateAjax' => $this->renderConfigSaveAjax($this->getSearchFormId()),
                    );
        }

        protected function getEnableAjaxValidationValue()
        {
            return true;
        }

        protected function getExtraRenderForCancelSearchLinkScript()
        {
            return "$(this).closest('form').find('.search-view-1').find('.dynamic-search-row').each(function(){
                        $(this).remove();
                    });
                    $('#" . $this->getRowCounterInputId() . "').val(0);
                    $('#" . $this->getStructureInputId() . "').val('');
                    $('.search-view-1').hide();
            ";
        }

        protected function getFormActionUrl()
        {
            return Yii::app()->createUrl('zurmo/default/validateDynamicSearch',
                                            array('viewClassName'       => get_class($this),
                                                  'modelClassName'     => get_class($this->model->getModel()),
                                                  'formModelClassName' => get_class($this->model)));
        }

        protected function getRowCounterInputId()
        {
            return 'rowCounter-' . $this->getSearchFormId();
        }

        protected function getStructureInputId()
        {
            return get_class($this->model) . '_' . DynamicSearchForm::DYNAMIC_STRUCTURE_NAME;
        }

        protected function getStructureInputName()
        {
            return get_class($this->model) . '[' . DynamicSearchForm::DYNAMIC_STRUCTURE_NAME . ']';
        }

        protected function renderConfigSaveAjax($formName)
        {
            return     "$('.search-view-1').hide();
                        $('#" . $formName . "').find('.attachLoading:first').removeClass('loading');
                        $('#" . $formName . "').find('.attachLoading:first').removeClass('loading-ajax-submit');
                        $('#" . $this->gridId . $this->gridIdSuffix . "-selectedIds').val(null);
                        $.fn.yiiGridView.update('" . $this->gridId . $this->gridIdSuffix . "',
                        {
                            data: $('#" . $formName . "').serialize() + '&" . $this->listModelClassName . "_page=&" . // Not Coding Standard
                            $this->listModelClassName . "_sort=" .
                            $this->getExtraQueryPartForSearchFormScriptSubmitFunction() ."' // Not Coding Standard
                         }
                        );";
        }

        /**
         * Override to do nothing since the validation and ajax is controlled via @see renderConfigSaveAjax
         * (non-PHPdoc)
         * @see SearchView::renderAdvancedSearchScripts()
         */
        protected function renderAdvancedSearchScripts()
        {
        }

        protected function renderAdvancedSearchForFormLayout($panel, $maxCellsPerRow, $form = null)
        {
            if(isset($panel['advancedSearchType']) &&
               $panel['advancedSearchType'] == self::ADVANCED_SEARCH_TYPE_DYNAMIC)
            {
                return $this->renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow, $form);
            }
            else
            {
                return $this->renderStaticSearchRows($panel, $maxCellsPerRow, $form);
            }
        }

       //todo: we could have getMetadata be changed to resolveMetadata, non-static. that way saved
       //todo: search we can pull in and show rows by default.
       //todo: we have to deal with saved search but this might require an override in DynamicSearchView...
        protected function renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow,  $form)
        {
            assert('$form != null');
            $content  = $form->errorSummary($this->model);
            $content .= $this->renderDynamicClausesValidationHelperContent($form);
            $rowCount = 0;
            if(($panel['rows']) > 0)
            {
                foreach ($panel['rows'] as $row)
                {
                    $content .= '<div>';
                    foreach ($row['cells'] as $cell)
                    {
                        if (!empty($cell['elements']))
                        {
                            foreach ($cell['elements'] as $elementInformation)
                            {
                                $elementclassname          = $elementInformation['type'] . 'Element';
                                $element                   = new $elementclassname($this->model,
                                                                                   $elementInformation['attributeName'],
                                                                                   $form,
                                                                                   array_slice($elementInformation, 2));
                                $element->editableTemplate = '{content}{error}';
                                $content .= $element->render();
                            }
                        }
                    }
                    $content .= '</div>';
                    $rowCount ++;
                }
            }
            $content .= $this->renderAddExtraRowContent($rowCount);
            $content .= $this->renderDynamicSearchStructureContent($form);
           return $content;
        }

        protected function renderAddExtraRowContent($rowCount)
        {
            assert('is_int($rowCount)');
            $idInputHtmlOptions  = array('id' => $this->getRowCounterInputId());
            $hiddenInputName     = 'rowCounter';
            $ajaxOnChangeUrl     = Yii::app()->createUrl("zurmo/default/dynamicSearchAddExtraRow",
                                   array('viewClassName'      => get_class($this),
                                         'modelClassName'     => get_class($this->model->getModel()),
                                         'formModelClassName' => get_class($this->model),
                                         'suffix'             => $this->getSearchFormId()));
            $content             = ZurmoHtml::hiddenField($hiddenInputName, $rowCount, $idInputHtmlOptions);
            // Begin Not Coding Standard
            $content            .= ZurmoHtml::ajaxLink(Yii::t('Default', 'Add Field'), $ajaxOnChangeUrl,
                                    array('type' => 'GET',
                                          'data' => 'js:\'rowNumber=\' + $(\'#rowCounter-' . $this->getSearchFormId(). '\').val()',
                                          'success' => 'js:function(data){
                                            $(\'#' . $this->getRowCounterInputId(). '\').val(parseInt($(\'#' . $this->getRowCounterInputId() . '\').val()) + 1)
                                            $(\'#addExtraAdvancedSearchRowButton-' . $this->getSearchFormId() . '\').parent().before(data);
                                            rebuildDynamicSearchRowNumbersAndStructureInput("' . $this->getSearchFormId() . '");
                                          }'),
                                    array('id' => 'addExtraAdvancedSearchRowButton-' . $this->getSearchFormId(), 'namespace' => 'add'));
            // End Not Coding Standard
            return CHtml::tag('div', array(), $content);
        }

        protected function renderAfterFormLayout($form)
        {
           parent::renderAfterFormLayout($form);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets')) . '/dynamicSearchViewUtils.js');
            Yii::app()->clientScript->registerScript('showStructurePanels' . $this->getSearchFormId(), "
                $('#show-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').click( function()
                    {
                        $('#show-dynamic-search-structure-div-"      . $this->getSearchFormId() . "').show();
                        $('#show-dynamic-search-structure-div-link-" . $this->getSearchFormId() . "').hide();
                        return false;
                    }
                );");
        }

        /**
         * This is a trick to properly validate this form. Eventually refactor.  Used to support error summary correctly.
         */
        protected function renderDynamicClausesValidationHelperContent($form)
        {
            $htmlOptions = array('id'   => get_class($this->model) . '_dynamicClauses',
                                 'name' => 'dynamicClausesValidationHelper');
            $content  = '<div style="display:none;">';
            $content .= $form->hiddenField($this->model, 'dynamicClauses', $htmlOptions);
            $content .= $form->error($this->model, 'dynamicClauses', $htmlOptions);
            $content .= '</div>';
            return $content;
        }

        protected function renderDynamicSearchStructureContent($form)
        {
            if($this->shouldHideDynamicSearchStructureByDefault())
            {
                $style1 = '';
                $style2 = 'display:none;';
            }
            else
            {
                $style1 = 'display:none;';
                $style2 = '';
            }
            $content  = CHtml::link(Yii::t('Default', 'More Options'), '#',
                            array('id'    => 'show-dynamic-search-structure-div-link-' . $this->getSearchFormId() . '',
                                  'style' => $style1));
            $content .= CHtml::tag('div',
                            array('id'    => 'show-dynamic-search-structure-div-' . $this->getSearchFormId(),
                                  'style' => $style2), $this->renderStructureInputContent($form));
            return $content;
        }

        protected function renderStructureInputContent($form)
        {
            $idInputHtmlOptions  = array('id'    => $this->getStructureInputId(),
                                         'name'  => $this->getStructureInputName(),
                                         'class' => 'dynamic-search-structure-input');
            $content             = $form->textField($this->model, 'dynamicStructure', $idInputHtmlOptions);
            $content            .= $form->error($this->model, 'dynamicStructure');
            return $content;
        }

        protected function shouldHideDynamicSearchStructureByDefault()
        {
            //todo: expand once we have saved search
            return true;
        }
    }
?>
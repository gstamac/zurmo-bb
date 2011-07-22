<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Displays the editable dropdown interface
     * which is used by the Designer module
     * to allow values in a drop down list to be
     * modified, added, or removed.
     */
    class EditableDropDownCollectionElement extends Element
    {
        protected $collectionCountData;

        protected function renderEditable()
        {
            return $this->renderControlEditable();
        }

        protected function renderControlEditable()
        {
            assert('in_array("CollectionAttributeFormInterface", class_implements($this->model))');
            assert('$this->model->{$this->attribute} == null || is_array($this->model->{$this->attribute})');
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("EditableDropDownSortable");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.JuiSortable', array(
                'itemTemplate' => $this->renderItemTemplate(),
                'items' => $this->getItems(),
                'options' => array(
                    'placeholder' => 'ui-state-highlight',
                    'stop'      => 'js:function(event, ui){' .
                                        $this->renderScriptCallToRebuildSelectInputFromInputs() . '}',
                ),
                'baseInputNameForSortableCollection' => get_class($this->model) . '[' . $this->attribute . ']',
                'htmlOptions' =>
                array(
                    'id'    => $this->attribute . '_ul',
                    'class' => 'sortable',
                )
            ));
            $cClipWidget->endClip();
            $this->registerScripts();
            $content  = $this->renderError();
            $content .= $this->renderMultipleAttributesUsingCollectionContent();
            $content .= $this->renderRemovalNoticeContent();
            $content .= '<br/>';
            $content .= $cClipWidget->getController()->clips['EditableDropDownSortable'];
            $content .= $this->renderAddInputAndAddButton();

            return $content;
        }

        protected function getItems()
        {
            $items = array();
            $dropDownCountData = $this->getCollectionCountData();
            foreach ($this->getDropDownArray() as $order => $name)
            {
                if (isset($dropDownCountData[$name]))
                {
                    $removalContent = null;
                }
                else
                {
                    $removalContent = $this->renderRemoveLink();
                }
                $items[$order] = array('content' => $name, 'removalContent' => $removalContent);
            }
            return $items;
        }

        protected function canAllItemsBeRemoved()
        {
            $dropDownCountData = $this->getCollectionCountData();
            foreach ($this->getDropDownArray() as $order => $name)
            {
                if (isset($dropDownCountData[$name]))
                {
                    return false;
                }
            }
            return true;
        }

        protected function getCollectionCountData()
        {
            if ($this->collectionCountData == null)
            {
                $this->collectionCountData = $this->model->getCollectionCountData();
            }
            return $this->collectionCountData;
        }

        protected function renderItemTemplate()
        {
            return '<li class="ui-state-default" id="editableDropDown_{id}">
                        <span class="ui-icon ui-icon-arrowthick-2-n-s">&#160;</span>
                        <input name = "' . $this->getNameForInputField() .
                        '" id = "' . $this->getIdForInputField('{id}') .
                        '" type = "text" value = "{content}" size="50"/>
                        <input name = "' . $this->getNameForExistingValueHiddenField() . '" type = "hidden" value = "{content}"/>
                        &#160;&#160;{removalContent}</li>';
        }

        protected function renderRemoveLink()
        {
            return CHtml::link(Yii::t('Default', 'Remove'), '#', array('class' => 'remove-sortable-item-link'));
        }

        protected function renderAddInputAndAddButton()
        {
            $content  = '<table>';
            $content .= '<colgroup><col style="width:50%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr>';
            $content .= '<td>';
            $content .= CHtml::textField( $this->attribute . '_AddInput', '', array('size' => 50));
            $content .= '&#160;&#160;';
            $content .= CHtml::button(yii::t('Default', 'Add Item'), array('id' => $this->attribute . '_AddInputButton'));
            $content .= '<div id="' . $this->attribute . '_AddInput_em_" class="errorMessage" style="display:none"></div>';
            $content .= '</td>';
            $content .= '</tr>';
            $content .= '</tbody>';
            $content .= '</table>';

            return $content;
        }

        protected function registerScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.elements.assets') . '/SelectInputUtils.js'
                    ),
                CClientScript::POS_END
            );
            $inputIdPrefix = get_class($this->model) . '_' . $this->attribute . '_';
            Yii::app()->clientScript->registerScript('editableDropDown', "
                " . $this->renderItemsOnChangeScript() . "
                $('.remove-sortable-item-link').live('click', function()
                    {
                        $(this).parent().remove();
                            " . $this->renderScriptCallToRebuildSelectInputFromInputs() . "
                        return false;
                    }
                );
                $('#" . $this->attribute . "_AddInputButton').click(function()
                    {
                        if ($('#" . $this->attribute . "_AddInput').val()=='')
                        {
                            $('#" . $this->attribute . "_AddInput').addClass($.fn.yiiactiveform.getSettings('#edit-form').errorCssClass);
                            $('#" . $this->attribute . "_AddInput_em_').html('" . Yii::t('Default', 'You must enter a value') . "').show();
                            return false;
                        }
                        else
                        {
                            $('#" . $this->attribute . "_AddInput').removeClass($.fn.yiiactiveform.getSettings('#edit-form').errorCssClass);
                            $('#" . $this->attribute . "_AddInput_em_').html('').hide();
                        }
                        var currenInputCollectionLength = $('input[name=\"" . $this->getNameForInputField() . "\"]').length;
                        $('<li class=\"ui-state-default\" id=\"{id}\">' +
                        '<span class=\"ui-icon ui-icon-arrowthick-2-n-s\">&#160;</span>' +
                        '<input name=\"" . $this->getNameForInputField() . "\" id=\"" . $inputIdPrefix .
                        "' + ($('input[name=\"" . $this->getNameForInputField() . "\"]').length + 1) +'\" type=\"text\" value=\"' +
                        $('#" . $this->attribute . "_AddInput').val()
                         + '\" size=\"50\"/><input name=\"" . $this->getNameForExistingValueHiddenField() . "\" type=\"hidden\" value=\"' +
                        $('#" . $this->attribute . "_AddInput').val() + '\" />&#160;&#160;&#160;" . $this->renderRemoveLink() . " </li>').appendTo($('#" . $this->attribute . "_ul'));
                        $('#" . $this->attribute . "_AddInput').val('');
                        $('#" . $inputIdPrefix . "' + (currenInputCollectionLength + 1)).change(function()
                        {
                            " . $this->renderScriptCallToRebuildSelectInputFromInputs() . "
                        }
                        );
                            " . $this->renderScriptCallToRebuildSelectInputFromInputs() . "
                        return false;
                    }
                );
            ");
        }

        protected function renderScriptCallToRebuildSelectInputFromInputs()
        {
            assert('$this->getSpecificValueFromDropDownAttributeName() != null');
            return "rebuildSelectInputFromInputs(
                        '" . get_class($this->model) . "_" .
                        $this->getSpecificValueFromDropDownAttributeName() . "', '" .
                        $this->getNameForInputField() . "')";
        }

        protected function renderItemsOnChangeScript()
        {
            $content = null;
            foreach ($this->getDropDownArray() as $key => $item)
            {
                $content .= "$('#" . $this->getIdForInputField($key) . "').change(function()
                {
                    " . $this->renderScriptCallToRebuildSelectInputFromInputs() . "
                }
                );";
            }
            return $content;
        }

        protected function renderControlNonEditable()
        {
            return Yii::app()->format->text($this->model->{$this->attribute});
        }

        protected function getSpecificValueFromDropDownAttributeName()
        {
            if (isset($this->params['specificValueFromDropDownAttributeName']))
            {
                return $this->params['specificValueFromDropDownAttributeName'];
            }
            return null;
        }

        protected function getIdForInputField($suffix)
        {
            return get_class($this->model) . '_' . $this->attribute . '_'. $suffix;
        }

        protected function getNameForInputField()
        {
            return get_class($this->model) . '[' . $this->attribute . '][]';
        }

        protected function getNameForExistingValueHiddenField()
        {
            return get_class($this->model) . '[' . $this->attribute . 'ExistingValues][]';
        }

        protected function getDropDownArray()
        {
            $dropDownArray = $this->model->{$this->attribute};
            if ($dropDownArray == null)
            {
                return array();
            }
            return $dropDownArray;
        }

        protected function renderMultipleAttributesUsingCollectionContent()
        {
            $content                       = '';
            $modelLabelAttributeLabelsData =  $this->model->getModelPluralNameAndAttributeLabelsThatUseCollectionData();
            if (count($modelLabelAttributeLabelsData) > 1)
            {
                $message = Yii::t('Default', 'This pick-list is used by more than one module.');
                foreach ($modelLabelAttributeLabelsData as $modelLabel => $attributeLabel)
                {
                    $message .= '<br/>' . $modelLabel . '&#160;-&#160;' . $attributeLabel;
                }
                $content .= HtmlNotifyUtil::renderHighlightBoxByMessage($message);
            }
            return $content;
        }

        protected function renderRemovalNoticeContent()
        {
            if ($this->canAllItemsBeRemoved())
            {
                return;
            }
            $message = Yii::t('Default', 'Some values cannot be removed because they are currently in use. ' .
                                         'Try changing the records that use them first.');
            $content  = HtmlNotifyUtil::renderHighlightBoxByMessage($message);
            return $content;
        }
    }
?>

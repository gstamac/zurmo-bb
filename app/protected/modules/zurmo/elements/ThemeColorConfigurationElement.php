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

    class ThemeColorConfigurationElement extends ThemeColorElement
    {
        protected $shouldDisableLocked = false;

        protected $orderByUnlockLevel  = false;

        public function renderControlEditable()
        {
            assert('$this->model instanceof ZurmoUserInterfaceConfigurationForm');
            $content  = parent::renderControlEditable();
            $content .= $this->renderCustomThemeColorChooser();
            return $content;
        }

        protected function shouldRenderControlEditable()
        {
            return true;
        }

        protected  function renderCustomThemeColorChooser()
        {
            $attribute   = 'customThemeColor1';
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip($attribute);
            $cClipWidget->widget('application.core.widgets.ZurmoColorPicker', array(
                'inputName'            => $this->getEditableInputName($attribute),
                'inputId'              => $this->getEditableInputId($attribute),
                'inputValue'           => $this->model->$attribute,
                'swatchName'           => 'theme-color-1'
            ));
            $cClipWidget->endClip();
            $content = ZurmoHtml::tag('div', array(), $cClipWidget->getController()->clips[$attribute]);
            $attribute   = 'customThemeColor2';
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip($attribute);
            $cClipWidget->widget('application.core.widgets.ZurmoColorPicker', array(
                'inputName'            => $this->getEditableInputName($attribute),
                'inputId'              => $this->getEditableInputId($attribute),
                'inputValue'           => $this->model->$attribute,
                'swatchName'           => 'theme-color-2'
            ));
            $cClipWidget->endClip();
            $content .= ZurmoHtml::tag('div', array(), $cClipWidget->getController()->clips[$attribute]);
            $attribute   = 'customThemeColor3';
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip($attribute);
            $cClipWidget->widget('application.core.widgets.ZurmoColorPicker', array(
                'inputName'            => $this->getEditableInputName($attribute),
                'inputId'              => $this->getEditableInputId($attribute),
                'inputValue'           => $this->model->$attribute,
                'swatchName'           => 'theme-color-3'
            ));
            $cClipWidget->endClip();
            $content .= ZurmoHtml::tag('div', array(), $cClipWidget->getController()->clips[$attribute]);
            return ZurmoHtml::tag('div',
                                  array(
                                      'id' => 'customThemeColorPicker',
                                      'class' => 'clearfix',
                                      'style' => $this->getStyleForInitialColorPicker(),
                                  ),
                                  $content);
        }

        public function registerScript()
        {
            $customThemePosition = count(Yii::app()->themeManager->getThemeColorNamesAndLabels()) - 1;
            $inputId             = $this->getEditableInputId() . '_' . $customThemePosition;
            Yii::app()->clientScript->registerScript('customThemeColorPicker', "
                if ($('#{$inputId}').attr('checked')){
                    $('#customThemeColorPicker').show(0);
                }
                $('#edit-form').change(function(){
                    if ($('#{$inputId}').attr('checked')){
                        $('#customThemeColorPicker').slideDown();
                    } else {
                        $('#customThemeColorPicker').slideUp();
                    }
                });
            ", CClientScript::POS_END);
            parent::registerScript();
        }

        protected function getStyleForInitialColorPicker()
        {
            $attribute = $this->attribute;
            if ($this->model->$attribute != 'custom')
            {
                return 'display: none';
            }
        }
    }
?>
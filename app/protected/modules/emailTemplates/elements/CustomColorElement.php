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

    class CustomColorElement extends Element
    {
        protected function renderControlEditable()
        {
            $this->registerScript();
            $content = $this->renderCustomColorChooser();
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function renderCustomColorChooser()
        {
            $inputId     = $this->getEditableInputId();
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip($this->attribute);
            // Begin Not Coding Standard
            $cClipWidget->widget('application.core.widgets.ZurmoColorPicker', array(
                'inputName'            => $this->getEditableInputName(),
                'inputId'              => $inputId,
                'inputValue'           => $this->model->{$this->attribute},
                'htmlOptions'          => array('class' => 'color-picker'),
                'palettes'             => 'true',
                'change'               => "function(event, ui) {
                                                    $('#{$inputId}').css('border-color', ui.color.toString());
                                          }",
            ));
            // End Not Coding Standard
            $cClipWidget->endClip();
            $content = ZurmoHtml::tag('div', array(), $cClipWidget->getController()->clips[$this->attribute]);
            return $content;
        }

        protected function getParamsForPicker()
        {
            return array(

            );
        }

        public function registerScript()
        {
            // Begin Not Coding Standard
            $script = "
                            $('.color-picker').focus(function(){
                                $('.color-picker').iris('hide');
                                $(this).iris('show');
                            });
                            $(document).click(function(e) {
                                if (!$(e.target).is('.color-picker, .iris-picker, .iris-picker-inner')) {
                                    $('.color-picker').iris('hide');
                                }
                            });
                      ";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('customColor', $script);
        }
    }
?>
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
     * Class used by reporting or workflow to show available operator types in a dropdown.
     */
    class OperatorStaticDropDownElement extends StaticDropDownElement
    {
        protected function renderControlEditable()
        {
            $content = parent::renderControlEditable();
            $this->renderChangeScript();
            return $content;
        }

        protected function getDropDownArray()
        {
            return $this->model->getOperatorValuesAndLabels();
        }

        public function getIdForSelectInput()
        {
            return $this->getEditableInputId($this->attribute);
        }

        protected function getNameForSelectInput()
        {
            return $this->getEditableInputName($this->attribute);
        }

        protected function renderChangeScript()
        {
            Yii::app()->clientScript->registerScript('operatorRules', "
                $('#" . $this->getIdForSelectInput() . "').change( function()
                    {
                        arr  = " . CJSON::encode($this->getValueTypesRequiringFirstDateInput()) . ";
                        arr2 = " . CJSON::encode($this->getValueTypesRequiringSecondDateInput()) . ";
                        if ($.inArray($(this).val(), arr) != -1)
                        {
                            $(this).parent().parent().parent().find('.value-data').find('.first-value-area').show();
                            $(this).parent().parent().parent().find('.value-data').find('.first-value-area')
                            .find(':input, select').prop('disabled', false);
                        }
                        else
                        {
                            $(this).parent().parent().parent().find('.value-data').find('.first-value-area').hide();
                            $(this).parent().parent().parent().find('.value-data').find('.first-value-area')
                            .find(':input, select').prop('disabled', true);
                        }
                        if ($.inArray($(this).val(), arr2) != -1)
                        {
                            $(this).parent().parent().parent().find('.value-data').find('.second-value-area').show();
                            $(this).parent().parent().parent().find('.value-data').find('.second-value-area')
                            .find(':input, select').prop('disabled', false);
                        }
                        else
                        {
                            $(this).parent().parent().parent().find('.value-data').find('.second-value-area').hide();
                            $(this).parent().parent().parent().find('.value-data').find('.second-value-area')
                            .find(':input, select').prop('disabled', true);
                        }
                        if($(this).val() == 'oneOf')
                        {
                            var newName = $(this).parent().parent().parent().find('.value-data')
                                          .find('.flexible-drop-down').attr('name') + '[]';
                            $(this).parent().parent().parent().find('.value-data').find('.flexible-drop-down')
                            .attr('multiple', 'multiple').addClass('multiple').addClass('ignore-style')
                            .attr('name', newName);
                        }
                        else
                        {
                            var newName = $(this).parent().parent().parent().find('.value-data')
                                          .find('.flexible-drop-down').attr('name');
                            if(newName != undefined)
                            {
                                $(this).parent().parent().parent().find('.value-data').find('.flexible-drop-down')
                                .prop('multiple', false).removeClass('multiple').removeClass('ignore-style')
                                .attr('name', newName.replace('[]',''));
                            }
                        }
                    }
                );
            ");
        }

        public static function getValueTypesRequiringFirstDateInput()
        {
            return array('equals',   'doesNotEqual', 'greaterThanOrEqualTo', 'lessThanOrEqualTo', 'greaterThan',
                         'lessThan', 'oneOf', 'between');
        }

        public static function getValueTypesRequiringSecondDateInput()
        {
            return array('between');
        }
    }
?>
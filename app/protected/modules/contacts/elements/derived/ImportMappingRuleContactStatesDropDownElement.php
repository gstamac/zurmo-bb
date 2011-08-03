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
     * Display a drop down of contact states specifically for mapping rules during the import process.
     */
    class ImportMappingRuleContactStatesDropDownElement extends DropDownElement
    {
        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            assert('$model instanceof DefaultValueContactStateModelAttributeMappingRuleForm');
            parent::__construct($model, $attribute, $form, $params);
        }

        /**
         * Renders the editable dropdown content.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $dropDownArray = $this->getDropDownArray();
            $htmlOptions = array(
                'name' => $this->getEditableInputName(),
                'id'   => $this->getEditableInputId(),
            );
            $htmlOptions['empty'] = Yii::t('Default', 'None');
            return $this->form->dropDownList($this->model, $this->attribute, $dropDownArray, $htmlOptions);
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function getDropDownArray()
        {
            $dropDownArray = $this->model->statesData;
            if (empty($dropDownArray))
            {
                return array();
            }
            return array_combine($dropDownArray, $dropDownArray);
        }
    }
?>

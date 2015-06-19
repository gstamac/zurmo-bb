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

    /**
     * Display active currencies from the currency model.  This is specifically used when the user interface has
     * a currency attribute that requires a dropdown to select the currency to enter the value in.
     */
    class CurrencyIdForAModelsRelatedCurrencyValueDropDownElement extends DropDownElement
    {
        /**
         * Renders the editable dropdown content.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            if (ArrayUtil::getArrayValue($this->params, 'defaultToBlank'))
            {
                return ZurmoHtml::dropDownList($this->getNameForSelectInput(),
                                               null,
                                               $this->getDropDownArray(),
                                               $this->resolveHtmlOptions());
            }
            else
            {
                return $this->form->dropDownList(
                    $this->model->{$this->attribute}->currency,
                    'id',
                    $this->getDropDownArray(),
                    $this->resolveHtmlOptions()
                );
            }
        }

        public function getIdForSelectInput()
        {
            return $this->resolveInputIdPrefix() . '_' . $this->attribute . '_currency_id';
        }

        protected function getNameForSelectInput()
        {
            return $this->resolveInputNamePrefix() . '[' . $this->attribute . '][currency][id]';
        }

        /**
         * (non-PHPdoc)
         * @see DropDownElement::getDropDownArray()
         */
        protected function getDropDownArray()
        {
           $selectedCurrencyId = $this->model->{$this->attribute}->currency->id;
           if ($selectedCurrencyId < 0)
           {
               $selectedCurrencyId = null;
           }
           return Yii::app()->currencyHelper->getActiveCurrenciesOrSelectedCurrenciesData((int)$selectedCurrencyId);
        }

        /**
         * Override to properly handle scoping the error id for the currency id.  Without this method, the currency_id
         * would not be appending to the error id and then a validation error would never render correctly from the
         * yiiactiveform.
         * @return string error
         */
        protected function renderError()
        {
            return $this->form->error($this->model, $this->attribute,
                                      array('inputID' => $this->getEditableInputId($this->attribute, 'currency_id')), true, true,
                                      $this->renderScopedErrorId($this->attribute, 'currency_id'));
        }

        protected function resolveHtmlOptions()
        {
            $defaultHtmlOptions     = $this->getEditableHtmlOptions();
            $additionalHtmlOptions  = array();
            if (isset($this->params['htmlOptions']))
            {
                $additionalHtmlOptions  = $this->params['htmlOptions'];
            }
            return array_merge($defaultHtmlOptions, $additionalHtmlOptions);
        }
    }
?>
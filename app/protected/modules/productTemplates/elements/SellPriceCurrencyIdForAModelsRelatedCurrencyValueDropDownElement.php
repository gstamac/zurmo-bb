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
     * Display active currencies from the currency model.  This is specifically used when the user interface has
     * a sell price currency attribute that requires a dropdown to select the currency to enter the value in.
     * This would remain disabled if sell price is not editable
     */
    class SellPriceCurrencyIdForAModelsRelatedCurrencyValueDropDownElement extends CurrencyIdForAModelsRelatedCurrencyValueDropDownElement
    {
        /**
         * Renders the editable dropdown content.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $defaultHtmlOptions     = $this->getEditableHtmlOptions();
            $sellPriceFormulaModel  = $this->model->sellPriceFormula;
            $type                   = $sellPriceFormulaModel->type;
            $additionalHtmlOptions  = array();
            if ($type != null)
            {
                if ($type != SellPriceFormula::TYPE_EDITABLE)
                {
                    $additionalHtmlOptions = array('readonly' => 'readonly', 'class' => 'disabled');
                }
            }
            $defaultHtmlOptions = array_merge($defaultHtmlOptions, $additionalHtmlOptions);

            if (ArrayUtil::getArrayValue($this->params, 'defaultToBlank'))
            {
                return ZurmoHtml::dropDownList($this->getNameForSelectInput(),
                                               null,
                                               $this->getDropDownArray(),
                                               $defaultHtmlOptions);
            }
            else
            {
                return $this->form->dropDownList(
                    $this->model->{$this->attribute}->currency,
                    'id',
                    $this->getDropDownArray(),
                    $defaultHtmlOptions
                );
            }
        }
    }
?>
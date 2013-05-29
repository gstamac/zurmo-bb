<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Extends the ExtendedGridView to provide additional functionality.
     * @see ExtendedGridView class
     */
    class ProductPortletExtendedGridView extends ExtendedGridView
    {
        public $params;

        protected function renderTotalBarDetails()
        {
            $persistantProductConfigItemValue = ProductsPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey(
                                                                                                $this->params['portletId'],
                                                                                                'filteredByStage');
            $relationModelClassName = get_class($this->params["relationModel"]);
            $relationModelId        = $this->params["relationModel"]->id;
            $relationModel          = $relationModelClassName::getById($relationModelId);
            $models                 = $relationModel->products;
            $oneTimeTotal           = 0;
            $monthlyTotal           = 0;
            $annualTotal            = 0;
            foreach ($models as $model)
            {
                if($persistantProductConfigItemValue != ProductsConfigurationForm::FILTERED_BY_ALL_STAGES)
                {
                    if($model->stage->value != $persistantProductConfigItemValue)
                    {
                        continue;
                    }
                }
                if ($model->priceFrequency == ProductTemplate::PRICE_FREQUENCY_ONE_TIME)
                {
                    $oneTimeTotal += $model->sellPrice->value * $model->quantity;
                }

                if ($model->priceFrequency == ProductTemplate::PRICE_FREQUENCY_MONTHLY)
                {
                    $monthlyTotal += $model->sellPrice->value * $model->quantity;
                }

                if ($model->priceFrequency == ProductTemplate::PRICE_FREQUENCY_ANNUALLY)
                {
                    $annualTotal += $model->sellPrice->value * $model->quantity;
                }
            }

            $oneTimeTotal = Yii::app()->numberFormatter->formatCurrency($oneTimeTotal,
                                                                Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay());
            $monthlyTotal = Yii::app()->numberFormatter->formatCurrency($monthlyTotal,
                                                                Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay());
            $annualTotal  = Yii::app()->numberFormatter->formatCurrency($annualTotal,
                                                                Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay());
            //$currencySymbol     = Yii::app()->locale->getCurrencySymbol(Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay());
            echo Zurmo::t("Core", "Total: ") .
                $oneTimeTotal . Zurmo::t("Core", " One Time") .
                ", " . $monthlyTotal . Zurmo::t("Core", " Monthly") .
                ", " . $annualTotal . Zurmo::t("Core", " Annually");
        }
    }
?>

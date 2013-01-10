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

    abstract class ModelRelationsAndAttributesToSummableReportAdapter extends ModelRelationsAndAttributesToReportAdapter
    {
        const DISPLAY_CALCULATION_COUNT      = 'Count';

        const DISPLAY_CALCULATION_SUMMMATION = 'Summation';

        const DISPLAY_CALCULATION_AVERAGE    = 'Average';

        const DISPLAY_CALCULATION_MINIMUM    = 'Minimum';

        const DISPLAY_CALCULATION_MAXIMUM    = 'Maximum';

        const GROUP_BY_CALCULATION_DAY       = 'Day';

        const GROUP_BY_CALCULATION_WEEK      = 'Week';

        const GROUP_BY_CALCULATION_MONTH     = 'Month';

        const GROUP_BY_CALCULATION_QUARTER   = 'Quarter';

        const GROUP_BY_CALCULATION_YEAR      = 'Year';

        protected $shouldIncludeIdAsGroupByAttribute = true;

        protected static function getTranslatedDisplayCalculationShortLabel($type)
        {
            assert('is_string($type)');
            $labels = static::translatedDisplayCalculationShortLabels();
            return $labels[$type];
        }

        protected static function getDisplayCalculationTypes()
        {
            return array(
                self::DISPLAY_CALCULATION_COUNT,
                self::DISPLAY_CALCULATION_SUMMMATION,
                self::DISPLAY_CALCULATION_AVERAGE,
                self::DISPLAY_CALCULATION_MINIMUM,
                self::DISPLAY_CALCULATION_MAXIMUM,
            );
        }

        protected static function translatedDisplayCalculationShortLabels()
        {
            return array(
                self::DISPLAY_CALCULATION_COUNT       => Yii::t('Default', 'Count'),
                self::DISPLAY_CALCULATION_SUMMMATION  => Yii::t('Default', 'Sum'),
                self::DISPLAY_CALCULATION_AVERAGE     => Yii::t('Default', 'Avg'),
                self::DISPLAY_CALCULATION_MINIMUM     => Yii::t('Default', 'Min'),
                self::DISPLAY_CALCULATION_MAXIMUM     => Yii::t('Default', 'Max'),
            );
        }

        protected static function getTranslatedGroupByCalculationShortLabel($type)
        {
            assert('is_string($type)');
            $labels = static::translatedGroupByCalculationShortLabels();
            return $labels[$type];
        }

        protected static function translatedGroupByCalculationShortLabels()
        {
            return array(
                self::GROUP_BY_CALCULATION_DAY       => Yii::t('Default', 'Day'),
                self::GROUP_BY_CALCULATION_WEEK      => Yii::t('Default', 'Week'),
                self::GROUP_BY_CALCULATION_MONTH     => Yii::t('Default', 'Month'),
                self::GROUP_BY_CALCULATION_QUARTER   => Yii::t('Default', 'Quarter'),
                self::GROUP_BY_CALCULATION_YEAR      => Yii::t('Default', 'Year'),
            );
        }

        public function getAttributesForFilters()
        {
            $attributes       = $this->getAttributesNotIncludingDerivedAttributesData();
            $attributes       = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        /**
         *
         * Enter description here ...
         * @param string $attribute
         */
        public function getAttributeLabel($attribute)
        {
            assert('is_string($attribute)');
            $calculatedDisplayAttributes = $this->getDisplayCalculationAttributes();
            $groupByModifierAttributes   = $this->getGroupByModifierAttributes();
            if(isset($calculatedDisplayAttributes[$attribute]))
            {
                return $calculatedDisplayAttributes[$attribute]['label'];
            }
            elseif(isset($groupByModifierAttributes[$attribute]))
            {
                return $groupByModifierAttributes[$attribute]['label'];
            }
            return parent::getAttributeLabel($attribute);
        }

        public function getAttributesForDisplayAttributes($existingGroupBys = array(),
                                                          RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            assert('is_array($existingGroupBys)');
            if(($precedingModel != null && $precedingRelation == null) ||
               ($precedingModel == null && $precedingRelation != null))
            {
                throw new NotSupportedException();
            }
            if(empty($existingGroupBys))
            {
                return array();
            }
            $attributes       = array();
            $this->resolveGroupByAttributesForDisplayAttributes($precedingModel, $precedingRelation, $attributes,
                                                                $existingGroupBys);
            $attributes       = array_merge($attributes, $this->getDisplayCalculationAttributes());
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        public function resolveDisplayAttributeTypeAndAddSelectClause(RedBeanModelSelectQueryAdapter $selectQueryAdapter,
                                                                      $attribute, $tableName, $columnName,
                                                                      $columnAliasName, $queryStringExtraPart = null)
        {
            assert('is_string($attribute)');
            assert('is_string($columnAliasName)');
            assert('is_string($queryStringExtraPart) || $queryStringExtraPart == null');
            $type = $this->getDisplayAttributeForMakingViaSelectType($attribute);
            if($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_COUNT)
            {
                $selectQueryAdapter->addCountClause($tableName, $columnName, $columnAliasName);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_SUMMMATION)
            {
                $selectQueryAdapter->addSummationClause($tableName, $columnName, $columnAliasName, $queryStringExtraPart);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_AVERAGE)
            {
                $selectQueryAdapter->addAverageClause($tableName, $columnName, $columnAliasName, $queryStringExtraPart);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_MINIMUM)
            {
                $selectQueryAdapter->addMinimumClause($tableName, $columnName, $columnAliasName, $queryStringExtraPart);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_MAXIMUM)
            {
                $selectQueryAdapter->addMaximumClause($tableName, $columnName, $columnAliasName, $queryStringExtraPart);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_DAY)
            {
                $selectQueryAdapter->addDayClause($tableName, $columnName, $columnAliasName,
                                                  $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_WEEK)
            {
                $selectQueryAdapter->addWeekClause($tableName, $columnName, $columnAliasName,
                                                   $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_MONTH)
            {
                $selectQueryAdapter->addMonthClause($tableName, $columnName, $columnAliasName,
                                                    $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_QUARTER)
            {
                $selectQueryAdapter->addQuarterClause($tableName, $columnName, $columnAliasName,
                                                      $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_YEAR)
            {
                $selectQueryAdapter->addYearClause($tableName, $columnName, $columnAliasName,
                                                   $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            else
            {
                $selectQueryAdapter->addClause($tableName, $columnName, $columnAliasName);
            }
        }

        public function resolveOrderByStringForCalculationOrModifier($attribute, $tableName, $columnName, $queryStringExtraPart = null)
        {
            assert('is_string($attribute)');
            assert('is_string($columnName)');
            assert('is_string($queryStringExtraPart) || $queryStringExtraPart == null');
            $type = $this->getDisplayAttributeForMakingViaSelectType($attribute);
            if($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_COUNT)
            {
                return RedBeanModelSelectQueryAdapter::makeCountString($tableName, $columnName);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_SUMMMATION)
            {
                return RedBeanModelSelectQueryAdapter::makeSummationString($tableName, $columnName, $queryStringExtraPart);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_AVERAGE)
            {
                return RedBeanModelSelectQueryAdapter::makeAverageString($tableName, $columnName, $queryStringExtraPart);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_MINIMUM)
            {
                return RedBeanModelSelectQueryAdapter::makeMinimumString($tableName, $columnName, $queryStringExtraPart);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_MAXIMUM)
            {
                return RedBeanModelSelectQueryAdapter::makeMaximumString($tableName, $columnName, $queryStringExtraPart);
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_DAY)
            {
                return RedBeanModelSelectQueryAdapter::makeDayModifierString($tableName, $columnName,
                        $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_WEEK)
            {
                return RedBeanModelSelectQueryAdapter::makeWeekModifierString($tableName, $columnName,
                    $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_MONTH)
            {
                return RedBeanModelSelectQueryAdapter::makeMonthModifierString($tableName, $columnName,
                    $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_QUARTER)
            {
                return RedBeanModelSelectQueryAdapter::makeQuarterModifierString($tableName, $columnName,
                    $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            elseif($type == ModelRelationsAndAttributesToSummableReportAdapter::GROUP_BY_CALCULATION_YEAR)
            {
                return RedBeanModelSelectQueryAdapter::makeYearModifierString($tableName, $columnName,
                    $this->shouldDoTimeZoneAdjustmentOnModifierClause($attribute));
            }
            else
            {
                throw new NotSupportedException();
            }
        }

//todo: should we really call resolveReal in here? or have that outside and then call this method? make it a bit cleaner
        private function shouldDoTimeZoneAdjustmentOnModifierClause($attribute)
        {
            assert('is_string($attribute)');
            if($this->getRealModelAttributeType($this->resolveRealAttributeName($attribute)) == 'DateTime')
            {
                return true;
            }
            return false;
        }

        public function relationIsReportedAsAttribute($relation)
        {
            assert('is_string($relation)');
            if($this->isAttributeACalculationOrModifier($relation))
            {
                return false;
            }
            return parent::relationIsReportedAsAttribute($relation);
        }


        public function isAttributeACalculationOrModifier($attribute)
        {
            assert('is_string($attribute)');
            $displayCalculationAttributes = $this->getDisplayCalculationAttributes();
            $groupByModifiersAttributes   = $this->getGroupByCalculatedModifierAttributes();
            if(isset($displayCalculationAttributes[$attribute]) || isset($groupByModifiersAttributes[$attribute]))
            {
                return true;
            }
            return false;
        }

        public function isAttributeACalculatedGroupByModifier($attribute)
        {
            assert('is_string($attribute)');
            $groupByModifiersAttributes   = $this->getGroupByCalculatedModifierAttributes();
            if(isset($groupByModifiersAttributes[$attribute]))
            {
                return true;
            }
            return false;
        }

        public function resolveRealAttributeName($attribute)
        {
            assert('is_string($attribute)');
            if($attribute == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_COUNT)
            {
                return 'id';
            }
            return parent::resolveRealAttributeName($attribute);
        }

        public function getCalculationOrModifierType($attribute)
        {
            $parts = explode(FormModelUtil::DELIMITER, $attribute);
            if($parts > 1)
            {
                return $parts[1];
            }
        }


        public function getAttributesForGroupBys()
        {
            $attributes       = array();
            if($this->shouldIncludeIdAsGroupByAttribute)
            {
                $attributes['id'] = array('label' => Yii::t('Default', 'Id'));
            }
            $attributes       = array_merge($attributes, $this->getGroupByModifierAttributes());
            $attributes       = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        public function isAttributeIndexOrDerivedTypeADisplayCalculation($attributeIndexOrDerivedType)
        {
            assert('is_string($attributeIndexOrDerivedType)');
            $parts = explode(FormModelUtil::DELIMITER, $attributeIndexOrDerivedType);
            if(count($parts) > 1 && in_array(array_pop($parts), static::getDisplayCalculationTypes()))
            {
                return true;
            }
            elseif(count($parts) == 1 && $parts[0] == self::DISPLAY_CALCULATION_COUNT)
            {
                return true;
            }
            return false;
        }

        public function isDisplayAttributeMadeViaSelect($attribute)
        {
            $displayCalculationAttributes = $this->getDisplayCalculationAttributes();
            $groupByModifiersAttributes   = $this->getGroupByCalculatedModifierAttributes();
            if(isset($displayCalculationAttributes[$attribute]) ||
                isset($groupByModifiersAttributes[$attribute]))
            {
                return true;
            }
            return parent::isDisplayAttributeMadeViaSelect($attribute);
        }

        public function getDisplayElementType($attribute)
        {
            assert('is_string($attribute)');
            if($this->isAttributeIndexOrDerivedTypeADisplayCalculation($attribute))
            {
                if($attribute == self::DISPLAY_CALCULATION_COUNT)
                {
                    return 'Integer';
                }
                list($realAttribute, $notUsed) = explode(FormModelUtil::DELIMITER, $attribute);
                $attributeType = ModelAttributeToMixedTypeUtil::getType($this->model, $realAttribute);
                if ($attributeType == 'Decimal' || $attributeType == 'Integer')
                {
                    return 'Decimal';
                }
                elseif($attributeType == 'Date')
                {
                    return 'Date';
                }
                elseif($attributeType == 'DateTime')
                {
                    return 'DateTime';
                }
                elseif($this->model->isRelation($realAttribute) &&
                       $this->model->getRelationModelClassName($realAttribute) == 'CurrencyValue')
                {
                    return 'CalculatedCurrencyValue';
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            elseif($this->isAttributeACalculatedGroupByModifier($attribute))
            {
                return 'Text';
            }
            return parent::getDisplayElementType($attribute);
        }

        protected function getDisplayCalculationAttributes()
        {
            $attributes = array(self::DISPLAY_CALCULATION_COUNT => array('label' => Yii::t('Default', 'Count')));
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                $this->getDisplayCalculationAttribute($attributes, $attribute);
            }
            return $attributes;
        }

        protected function getDisplayCalculationAttribute(& $attributes, $attribute)
        {
            $attributeType = ModelAttributeToMixedTypeUtil::getType($this->model, $attribute);
            if ($attributeType == 'Decimal' || $attributeType == 'Integer')
            {
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_SUMMMATION);
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_AVERAGE);
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MINIMUM);
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MAXIMUM);
            }
            elseif($attributeType == 'Date' || $attributeType == 'DateTime')
            {
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MINIMUM);
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MAXIMUM);
            }
            elseif($this->model->isRelation($attribute) &&
                $this->model->getRelationModelClassName($attribute) == 'CurrencyValue')
            {
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_SUMMMATION);
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_AVERAGE);
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MINIMUM);
                $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MAXIMUM);
            }
        }

        protected function resolveGroupByAttributesForDisplayAttributes(RedBeanModel $precedingModel = null,
                                                                        $precedingRelation = null,
                                                                        & $attributes,
                                                                        $existingGroupBys)
        {
            assert('is_array($attributes)');
            assert('is_array($existingGroupBys)');
            foreach($existingGroupBys as $groupBy)
            {
                $addAttribute = false;
                //is there is preceding model/relation info
                if($precedingModel != null && $precedingRelation != null)
                {
                    if($groupBy->hasRelatedData() &&
                       $groupBy->getPenultimateModelClassName() == get_class($precedingModel) &&
                       $groupBy->getPenultimateRelation() == $precedingRelation &&
                       $groupBy->getResolvedAttributeModelClassName() == get_class($this->model))
                    {
                        $addAttribute = true;
                    }
                }
                else
                {
                    //is there is no preceding model/relation info
                    //if the groupBy attribute is part of a related data chain, ignore,
                    //since must be at the wrong spot in the chain.
                    if(!$groupBy->hasRelatedData() &&
                       $groupBy->getResolvedAttributeModelClassName() == get_class($this->model))
                    {
                        $addAttribute = true;
                    }
                }
                if($addAttribute)
                {
                    $resolvedAttribute = $groupBy->getResolvedAttribute();
                    $attributes[$resolvedAttribute] = array('label' => $this->model->getAttributeLabel($resolvedAttribute));
                }
            }
        }

        protected function resolveDisplayCalculationAttributeData(& $attributes, $attribute, $type)
        {
            assert('is_array($attributes)');
            assert('is_string($attribute)');
            assert('is_string($type)');
            $attributes[$attribute . FormModelUtil::DELIMITER . $type] =
                        array('label' => $this->resolveDisplayCalculationLabel($attribute, $type));
        }

        protected function resolveDisplayCalculationLabel($attribute, $type)
        {
            assert('is_string($type)');
            return $this->model->getAttributeLabel($attribute) .
                   ' -(' . static::getTranslatedDisplayCalculationShortLabel($type) . ')';
        }

        protected function getGroupByModifierAttributes()
        {
            $attributes = array();
            foreach ($this->getAttributesNotIncludingDerivedAttributesData() as $attribute => $data)
            {
                $attributeType = ModelAttributeToMixedTypeUtil::getType($this->model, $attribute);
                if(!in_array($attributeType, static::getAttributeTypesToExcludeAsGroupByModifiers()))
                {
                    $attributes[$attribute] = $data;
                }
            }
            return array_merge($this->getGroupByCalculatedModifierAttributes(), $attributes);
        }

        protected static function getAttributeTypesToExcludeAsGroupByModifiers()
        {
            return array('MultiSelectDropDown', 'TagCloud', 'TextArea', 'Date', 'DateTime');
        }

        protected function getGroupByCalculatedModifierAttributes()
        {
            $attributes = array();
            foreach ($this->getAttributesNotIncludingDerivedAttributesData() as $attribute => $data)
            {
                $attributeType = ModelAttributeToMixedTypeUtil::getType($this->model, $attribute);
                if($attributeType == 'Date' || $attributeType == 'DateTime')
                {
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_DAY);
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_WEEK);
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_MONTH);
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_QUARTER);
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_YEAR);
                }
            }
            return $attributes;
        }


        protected function resolveGroupByCalculationAttributeData(& $attributes, $attribute, $type)
        {
            assert('is_array($attributes)');
            assert('is_string($attribute)');
            assert('is_string($type)');
            $attributes[$attribute . FormModelUtil::DELIMITER . $type] =
                        array('label' => $this->resolveGroupByCalculationLabel($attribute, $type));
        }

        protected function resolveGroupByCalculationLabel($attribute, $type)
        {
            assert('is_string($type)');
            return $this->model->getAttributeLabel($attribute) .
                   ' -(' . static::getTranslatedGroupByCalculationShortLabel($type) . ')';
        }

        protected function getDisplayAttributeForMakingViaSelectType($attribute)
        {
            assert('is_string($attribute)');
            $displayCalculationAttributes = $this->getDisplayCalculationAttributes();
            $groupByModifiersAttributes   = $this->getGroupByCalculatedModifierAttributes();
            if($attribute == ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_COUNT)
            {
                return ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_COUNT;
            }
            elseif(isset($displayCalculationAttributes[$attribute]) || isset($groupByModifiersAttributes[$attribute]))
            {
                $parts = explode(FormModelUtil::DELIMITER, $attribute);
                return $parts[1];
            }
        }
    }
?>
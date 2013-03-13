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
     * Popuplate the RedBeanModelSelectQueryAdapter with the necessary columns or calculations to select
     */
    class DisplayAttributeReportItemQueryBuilder extends ReportItemQueryBuilder
    {
        protected $selectQueryAdapter;

        protected function isDisplayAttributeMadeViaSelect()
        {
            if($this->componentForm->madeViaSelectInsteadOfViaModel)
            {
                return true;
            }
            if($this->modelToReportAdapter->isDisplayAttributeMadeViaSelect($this->componentForm->getResolvedAttribute()))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public function __construct(ComponentForReportForm $componentForm,
                                    RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                    ModelRelationsAndAttributesToReportAdapter $modelToReportAdapter,
                                    RedBeanModelSelectQueryAdapter $selectQueryAdapter,
                                    $currencyConversionType = null)
        {
            parent::__construct($componentForm, $joinTablesAdapter, $modelToReportAdapter, $currencyConversionType);
            $this->selectQueryAdapter = $selectQueryAdapter;
        }

        protected function resolveFinalContent($modelAttributeToDataProviderAdapter, $onTableAliasName = null)
        {
            $this->resolveDisplayAttributeColumnName($modelAttributeToDataProviderAdapter, $onTableAliasName);
        }

        protected function resolveDisplayAttributeColumnName($modelAttributeToDataProviderAdapter, $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $builder              = new ModelJoinBuilder($modelAttributeToDataProviderAdapter, $this->joinTablesAdapter);
            if($this->shouldPrematurelyStopBuildingJoinsForAttribute($this->modelToReportAdapter, $modelAttributeToDataProviderAdapter))
            {
                $this->resolveDisplayAttributeForPrematurelyStoppingJoins($modelAttributeToDataProviderAdapter,
                                                                          $onTableAliasName);
            }
            else
            {
                $this->resolveDisplayAttributeForProcessingAllJoins(      $builder,
                                                                          $modelAttributeToDataProviderAdapter,
                                                                          $onTableAliasName);
            }
        }

        protected function resolveDisplayAttributeForPrematurelyStoppingJoins($modelAttributeToDataProviderAdapter,
                                                                              $onTableAliasName = null)
        {
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $resolvedModelClassName     = $this->resolvedModelClassName($modelAttributeToDataProviderAdapter);
            if($onTableAliasName == null)
            {
                $onTableAliasName     = $modelAttributeToDataProviderAdapter->getModelTableName();
            }
            $this->selectQueryAdapter->resolveIdClause($resolvedModelClassName, $onTableAliasName);
            $this->componentForm->setModelAliasUsingTableAliasName($onTableAliasName);
        }

        protected function resolveDisplayAttributeForProcessingAllJoins(ModelJoinBuilder $builder,
                                                                        $modelAttributeToDataProviderAdapter,
                                                                        $onTableAliasName = null)
        {
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $tableAliasName                 = $builder->resolveJoins($onTableAliasName,
                                              ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
            if($this->isDisplayAttributeMadeViaSelect())
            {
                if(!$this->modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter)
                {
                    throw new NotSupportedException();
                }
                $this->modelToReportAdapter->resolveDisplayAttributeTypeAndAddSelectClause(
                                  $this->selectQueryAdapter,
                                  $this->componentForm->getResolvedAttribute(),
                                  $tableAliasName,
                                  $this->resolveColumnName($modelAttributeToDataProviderAdapter),
                                  $this->componentForm->columnAliasName,
                                  $this->getAttributeClauseQueryStringExtraPart($tableAliasName));
                //todo: actually make getSelectClauseQueryStringExtraPart here in this class. then in constructor need to pass the conversion info param in.
                //todo: then we can do it all here and scrap that method in displayAttribute.
            }
            else
            {
                $tableAliasName = $this->resolvedTableAliasName($modelAttributeToDataProviderAdapter, $builder);
                $this->selectQueryAdapter->resolveIdClause(
                    $this->resolvedModelClassName($modelAttributeToDataProviderAdapter),
                    $tableAliasName);
                $this->componentForm->setModelAliasUsingTableAliasName($tableAliasName);
            }
        }

        protected function resolveColumnName(RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter)
        {
            if($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName();
            }
            else
            {
                return $modelAttributeToDataProviderAdapter->getColumnName();
            }
        }

        protected function resolvedModelClassName(RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter)
        {
            if($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $modelAttributeToDataProviderAdapter->getRelationModelClassName();
            }
            else
            {
                return $modelAttributeToDataProviderAdapter->getModelClassName();
            }
        }

        protected function resolvedTableAliasName(RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter,
                                                  ModelJoinBuilder $builder)
        {
            if($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $builder->getTableAliasNameForRelatedModel();
            }
            else
            {
                return $builder->getTableAliasNameForBaseModel();
            }
        }

        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
               $modelToReportAdapter->isAttributeACalculationOrModifier($attribute))
            {
                $relatedAttribute = static::resolveRelatedAttributeForMakingAdapter($modelToReportAdapter, $attribute);
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute), $relatedAttribute);
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute);
        }

        protected function shouldPrematurelyStopBuildingJoinsForAttribute($modelToReportAdapter,
                                                                          $modelAttributeToDataProviderAdapter)
        {
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            if($this->isDisplayAttributeMadeViaSelect())
            {
                return false;
            }
            if($modelAttributeToDataProviderAdapter instanceof
               DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter)
            {
                return false;
            }
            elseif($modelAttributeToDataProviderAdapter instanceof
                   InferredRedBeanModelAttributeToDataProviderAdapter)
            {
                return false;
            }
            //If casted up non-relation
            elseif($modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel() &&
               !$modelAttributeToDataProviderAdapter->isRelation())
            {
                return true;
            }
            //Owned relations such as Address or Email
            elseif($modelAttributeToDataProviderAdapter->isOwnedRelation())
            {
                return true;
            }
            //likeContactState for example. It is not covered by ownedRelation above but should stop prematurely
            elseif($modelToReportAdapter->relationIsReportedAsAttribute($modelAttributeToDataProviderAdapter->getAttribute()))
            {
                return true;
            }
            //if a User relation
            elseif($modelAttributeToDataProviderAdapter->isRelation() &&
                   $modelAttributeToDataProviderAdapter->getRelationModelClassName() == 'User')
            {
                return true;
            }
            return parent::shouldPrematurelyStopBuildingJoinsForAttribute($modelToReportAdapter,
                                                                          $modelAttributeToDataProviderAdapter);
        }

        protected function resolveCastingHintForAttribute($modelToReportAdapter, $modelAttributeToDataProviderAdapter,
                                                          $modelClassName, $realAttributeName)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            if($this->isDisplayAttributeMadeViaSelect())
            {
                return parent::resolveCastingHintForAttribute($modelToReportAdapter, $modelAttributeToDataProviderAdapter,
                                                              $modelClassName, $realAttributeName);
            }
        }

//todo: this is lame because it knows that madeViaSelectInsteadOfViaModel true, means it is a group by. try to decouple.
        protected function makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute(
            $modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($this->componentForm->madeViaSelectInsteadOfViaModel)
            {
                $resolvedRelatedAttribute = $modelToReportAdapter->getRules()->
                    getGroupByRelatedAttributeForRelationReportedAsAttribute(
                    $modelToReportAdapter->getModel(), $attribute);
            }
            else
            {
                $resolvedRelatedAttribute = null;
            }
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                $attribute, $resolvedRelatedAttribute);
        }
    }
?>
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
     * Generic rules for any model that extends the Activity class.
     */
    class ActivityMashableActivityRules extends MashableActivityRules
    {
        public function resolveSearchAttributesDataByRelatedItemId($relationItemId)
        {
            assert('is_int($relationItemId)');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $relationItemId,
                )
            );
            $searchAttributeData['structure'] = '1';
            return $this->resolveSearchAttributeDataForLatestActivities($searchAttributeData);
        }

        public function resolveSearchAttributesDataByRelatedItemIds($relationItemIds)
        {
            assert('is_array($relationItemIds)');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'oneOf',
                    'value'                => $relationItemIds,
                )
            );
            $searchAttributeData['structure'] = '1';
            return $this->resolveSearchAttributeDataForLatestActivities($searchAttributeData);
        }

        public function resolveSearchAttributeDataForLatestActivities($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            return $searchAttributeData;
        }

        public function getLatestActivitiesOrderByAttributeName()
        {
            return 'latestDateTime';
        }

        /**
         * Override if you want to display anything extra in the view for a particular model.
         */
        public function getLatestActivityExtraDisplayStringByModel($model)
        {
        }

        protected static function getActivityItemsStringContentByModelClassName(RedBeanModel $model, $castDownModelClassName)
        {
            assert('is_string($castDownModelClassName)');
            $existingModels = array();
            $modelDerivationPathToItem = ActivitiesUtil::getModelDerivationPathToItem($castDownModelClassName);
            foreach ($model->activityItems as $item)
            {
                try
                {
                    $castedDownmodel = $item->castDown(array($modelDerivationPathToItem));
                    if (get_class($castedDownmodel) == $castDownModelClassName)
                    {
                        if (strval($castedDownmodel) != null)
                        {
                            $params          = array('label' => strval($castedDownmodel));
                            $moduleClassName = $castedDownmodel->getModuleClassName();
                            $moduleId        = $moduleClassName::getDirectoryName();
                            $element         = new DetailsLinkActionElement('default', $moduleId,
                                                                            $castedDownmodel->id, $params);
                            $existingModels[] = $element->render();
                        }
                    }
                }
                catch (NotFoundException $e)
                {
                    //do nothing
                }
            }
            return self::resolveStringValueModelsDataToStringContent($existingModels);
        }

        protected static function getFirstActivityItemStringContent($relationModelClassNames, RedBeanModel $model)
        {
            assert('is_string($relationModelClassNames)');
            foreach ($relationModelClassNames as $relationModelClassName)
            {
                //ASSUMES ONLY A SINGLE ATTACHED ACTIVITYITEM PER RELATION TYPE.
                foreach ($model->activityItems as $item)
                {
                    try
                    {
                        $modelDerivationPathToItem = ActivitiesUtil::getModelDerivationPathToItem($relationModelClassName);
                        $castedDownModel = $item->castDown(array($modelDerivationPathToItem));
                        return strval($castedDownModel);
                    }
                    catch (NotFoundException $e)
                    {
                        //do nothing
                    }
                }
            }
        }
    }
?>
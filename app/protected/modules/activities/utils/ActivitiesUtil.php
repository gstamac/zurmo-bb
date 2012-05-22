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
     * Helper class for Activity module processes.
     */
    class ActivitiesUtil
    {
        /**
         * Given a modelClassName, find the deriviation path to Item. This is used by the castDown method
         * for example in RedBeanModel.
         * @param string $relationModelClassName
         * @return array of derivation path.
         */
        public static function getModelDerivationPathToItem($modelClassName)
        {
            assert('is_string($modelClassName)');
            $modelDerivationPath = RuntimeUtil::getClassHierarchy($modelClassName, 'RedBeanModel');
            $modelDerivationPathToItem = array();
            foreach ($modelDerivationPath as $modelClassName)
            {
                if ($modelClassName == 'Item')
                {
                    break;
                }
                $modelDerivationPathToItem[] = $modelClassName;
            }
            return array_reverse($modelDerivationPathToItem);
        }

        /**
         * Renders and returns string content of summary content for the given model.
         * @param RedBeanModel $model
         * @param mixed $redirectUrl
         * @param string $ownedByFilter
         * @param string $viewModuleClassName
         * @return string content
         */
        public static function renderSummaryContent(RedBeanModel $model, $redirectUrl, $ownedByFilter, $viewModuleClassName)
        {
            assert('is_string($redirectUrl) || $redirectUrl == null');
            assert('is_string($ownedByFilter)');
            assert('is_string($viewModuleClassName)');
            $mashableActivityRules  = MashableActivityRulesFactory::createMashableActivityRulesByModel(get_class($model));
            $orderByAttributeName   = $mashableActivityRules->getLatestActivitiesOrderByAttributeName();
            $summaryContentTemplate = $mashableActivityRules->getSummaryContentTemplate($ownedByFilter, $viewModuleClassName);

            //Render icon
            $content  = '<span class="'.get_class($model).'"></span>';
            //Render date
            $content .= '<strong>'.DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                            $model->{$orderByAttributeName}, 'long', null) . '</strong><br/>';

            $data                                            = array();
            $data['modelStringContent']                      = self::renderModelStringContent($model, $redirectUrl);
            $data['ownerStringContent']                      = self::renderOwnerStringContent($model);
            $data['relatedModelsByImportanceContent']        = self::renderRelatedModelsByImportanceContent($model);
            $data['extraContent']                            = self::resolveAndRenderExtraContent($model,
                                                                     $mashableActivityRules);

            //Render display content
            $content .= self::resolveContentTemplate($summaryContentTemplate, $data);
            return $content;
        }

        protected static function renderModelStringContent(RedBeanModel $model, $redirectUrl)
        {
            assert('is_string($redirectUrl) || $redirectUrl == null');
            $modelDisplayString = strval($model);
            if (strlen($modelDisplayString) > 200)
            {
                $modelDisplayString = substr($modelDisplayString, 0, 200) . '...';
            }
            if (get_class($model) == 'Task')
            {
                $modelDisplayString = '<span style="text-decoration:line-through;">' . $modelDisplayString . '</span>';
            }
            $params          = array('label' => $modelDisplayString, 'redirectUrl' => $redirectUrl);
            $moduleClassName = $model->getModuleClassName();
            $moduleId        = $moduleClassName::getDirectoryName();
            $element  = new DetailsLinkActionElement('default', $moduleId, $model->id, $params);
            return $element->render();
        }

        protected static function renderOwnerStringContent($model)
        {
            if($model instanceof MashableActivity)
            {
                return strval($model->owner);
            }
            else
            {
                return null;
            }
        }

        /**
         * Renders related models. But only renders one type of related model given that the $model supplied
         * is connected to more than one type of activity item.  There is an order of importance that is checked
         * starting with Account, then Contact, then Opportunity. If none are found, then it grabs the first available.
         * @see getActivityItemsStringContentByModelClassName
         * @param RedBeanModel $model
         */
        protected static function renderRelatedModelsByImportanceContent(RedBeanModel $model)
        {
            if($model->activityItems->count() == 0)
            {
                return;
            }
            $stringContent = self::getActivityItemsStringContentByModelClassName($model, 'Account');
            if($stringContent != null)
            {
                return Yii::t('Default', 'for {relatedModelsStringContent}', array('{relatedModelsStringContent}' => $stringContent));
            }
            $stringContent = self::getActivityItemsStringContentByModelClassName($model, 'Contact');
            if($stringContent != null)
            {
                return Yii::t('Default', 'with {relatedContactsStringContent}', array('{relatedContactsStringContent}' => $stringContent));
            }
            $stringContent = self::getActivityItemsStringContentByModelClassName($model, 'Opportunity');
            if($stringContent != null)
            {
                return Yii::t('Default', 'for {relatedModelsStringContent}', array('{relatedModelsStringContent}' => $stringContent));
            }
            $metadata      = Activity::getMetadata();
            $stringContent =  self::getFirstActivityItemStringContent($metadata['Activity']['activityItemsModelClassNames'], $model);
            if($stringContent != null)
            {
                return Yii::t('Default', 'for {relatedModelsStringContent}', array('{relatedModelsStringContent}' => $stringContent));
            }
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
                        $existingModels[] = strval($castedDownmodel);
                    }
                }
                catch (NotFoundException $e)
                {
                    //do nothing
                }
            }
            return self::resolveStringValueModelsDataToStringContent($existingModels);
        }

        protected static function resolveStringValueModelsDataToStringContent($modelsAndStringData)
        {
            assert('is_array($modelsAndStringData)');
            $content = null;
            foreach($modelsAndStringData as $modelStringContent)
            {
                if($content != null)
                {
                    $content .= ', ';
                }
                $content .= $modelStringContent;
            }
            return $content;
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

        protected static function resolveAndRenderExtraContent(RedBeanModel $model,
                                                               MashableActivityRules $mashableActivityRules)
        {
            $content      = null;
            $extraContent = $mashableActivityRules->getLatestActivityExtraDisplayStringByModel($model);
            if ($extraContent)
            {
                $content .= '<br/>' . $extraContent;
            }
            return $content;
        }

        protected static function resolveContentTemplate($template, $data)
        {
            assert('is_string($template)');
            assert('is_array($data)');
            $preparedContent = array();
            foreach ($data as $templateVar => $content)
            {
                $preparedContent["{" . $templateVar . "}"] = $content;
            }
            return strtr($template, $preparedContent);
        }

        public static function getActivityItemsModelClassNamesDataExcludingContacts()
        {
            $metadata = Activity::getMetadata();
            $activityItemsModelClassNamesData = $metadata['Activity']['activityItemsModelClassNames'];
            foreach ($activityItemsModelClassNamesData as $index => $relationModelClassName)
            {
                if ($relationModelClassName == 'Contact')
                {
                    unset($activityItemsModelClassNamesData[$index]);
                }
            }
            return $activityItemsModelClassNamesData;
        }
    }
?>
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

        public static function renderSummaryContent(RedBeanModel $model, $redirectUrl)
        {
            $mashableActivityRules = MashableActivityRulesFactory::createMashableActivityRulesByModel(
                                         get_class($model));
            $orderByAttributeName = $mashableActivityRules->getLatestActivitiesOrderByAttributeName();
            $content  = '<span class="'.get_class($model).'"></span>';
            $content .= '<strong>'.DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                            $model->{$orderByAttributeName}, 'long', null) . '</strong><br/>';
            $modelDisplayString = strval($model);
            if (strlen($modelDisplayString) > 200)
            {
                $modelDisplayString = substr($modelDisplayString, 0, 200) . '...';
            }
            $params = array('label' => $modelDisplayString, 'redirectUrl' => $redirectUrl);
            $moduleClassName = $model->getModuleClassName();
            $moduleId        = $moduleClassName::getDirectoryName();
            $element  = new DetailsLinkActionElement('default', $moduleId, $model->id, $params);
            $content .= $element->render() . '<br/>';
            //$content .= Yii::t('Default', 'by') . '&#160;' . Yii::app()->format->text($model->createdByUser);
            $extraContent = $mashableActivityRules->getLatestActivityExtraDisplayStringByModel($model);
            if ($extraContent)
            {
                $content .= '<br/>' . $extraContent;
            }
            return $content;
        }
    }
?>
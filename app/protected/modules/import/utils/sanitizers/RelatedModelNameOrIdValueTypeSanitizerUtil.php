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
     * Sanitizer to handle attribute values that are possible a model name not just a model id.
     */
    class RelatedModelNameOrIdValueTypeSanitizerUtil extends ExternalSystemIdSuppportedSanitizerUtil
    {
        public static function supportsSqlAttributeValuesDataAnalysis()
        {
            return false;
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'RelatedModelNameOrIdValueType';
        }

        public static function getLinkedMappingRuleType()
        {
            return 'RelatedModelValueType';
        }

        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName');
            assert('$value != ""');
            assert('$mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID ||
                    $mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME');
            if($value == null)
            {
                return $value;
            }
            $model                   = new $modelClassName(false);
            $attributeModelClassName = $this->resolveAttributeModelClassName($model,$attributeName);
            if($mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                try
                {
                    return $attributeModelClassName::getById((int)$value);
                }
                catch(NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'The id specified did not match any existing records.'));
                }
            }
            elseif($mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                try
                {
                    return static::getModelByExternalSystemIdAndModelClassName($value, $attributeModelClassName);
                }
                catch(NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'The other id specified did not match any existing records.'));
                }
            }
            else
            {
                if(!method_exists($attributeModelClassName, 'getByName'))
                {
                    throw new NotSupportedException();
                }
                $modelsFound = $attributeModelClassName::getByName($value);
                if(count($modelsFound) == 0)
                {
                    $newRelatedModel       = new $attributeModelClassName();
                    $newRelatedModel->name = $value;
                }
                else
                {
                    return $modelsFound[0];
                }
            }

        }
    }
?>
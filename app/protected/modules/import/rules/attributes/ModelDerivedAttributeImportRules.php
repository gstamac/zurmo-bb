<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Base class for a derived relation attribute. This would occur if the relation attribute is not specifically
     * defined on a model, but instead a casted up model is specifically defined.
     * @see DefaultModelNameIdDerivedAttributeMappingRuleForm
     */
    abstract class ModelDerivedAttributeImportRules extends DerivedAttributeImportRules
    {
        protected static function getAllModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array('DefaultModelNameIdDerivedAttribute' => 'ImportMappingRuleDefaultModelNameId',
                         'IdValueType'                        => 'ImportMappingModelIdValueTypeDropDown');
        }

        public function getDisplayLabel()
        {
            $name           = get_called_class();
            $modelClassName = substr($name, 0, strlen($name) - strlen('DerivedAttributeImportRules'));
            return $modelClassName::getModelLabelByTypeAndLanguage('Singular');
        }

        /**
         * This information regarding the correct attribute name on the model is not available. This information is
         * available via DerivedAttributeSupportedImportRules::getDerivedAttributeRealAttributeName();  Since we don't
         * have access to that information in this class, we don't know the import rule type, we cannot return anything.
         * Resolving what attribute to save the derived model to will need to be handled outside of this class.
         * @see DerivedAttributeSupportedImportRules::getRealModelAttributeNameForDerivedAttribute()
         * @return array
         */
        public function getRealModelAttributeNames()
        {
            return array();
        }

        public static function getSanitizerUtilTypesInProcessingOrder()
        {
            throw new NotImplementedException();
        }

        /**
         * @param mixed $value
         * @param string $columnName
         * @param array $columnMappingData
         * @param ImportSanitizeResultsUtil $importSanitizeResultsUtil
         * @return array|void
         */
        public function resolveValueForImport($value, $columnName, $columnMappingData, ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            assert('is_string($columnName)');
            $modelClassName        = $this->getModelClassName();
            $derivedModelClassName = static::getDerivedModelClassName();
            $sanitizedValue = ImportSanitizerUtil::
                              sanitizeValueBySanitizerTypes(static::getSanitizerUtilTypesInProcessingOrder(),
                                                            $modelClassName, null,
                                                            $value, $columnName, $columnMappingData, $importSanitizeResultsUtil);
             if ($sanitizedValue == null &&
                $columnMappingData['mappingRulesData']
                                  ['DefaultModelNameIdDerivedAttributeMappingRuleForm']['defaultModelId'] != null)
             {
                $modelId               = $columnMappingData['mappingRulesData']
                                                           ['DefaultModelNameIdDerivedAttributeMappingRuleForm']
                                                           ['defaultModelId'];
                $sanitizedValue        = $derivedModelClassName::getById((int)$modelId);
             }
            return array(static::getDerivedAttributeName() => $sanitizedValue);
        }

        public static function getDerivedAttributeName()
        {
            return static::getDerivedModelClassName() . 'Derived';
        }

        public static function getDerivedModelClassName()
        {
            $class = get_called_class();
            $class = substr($class, 0, strlen($class) - strlen('DerivedAttributeImportRules'));
            return $class;
        }
    }
?>
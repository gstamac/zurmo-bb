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
     * Helper class for working with MappingRuleForms and Element types that are used with those forms.
     */
    class MappingRuleFormAndElementTypeUtil
    {
        /**
         * Given an array of AttributeImportRules an attribute index or derived type, make a MappingRuleForm
         * @param array $attributeImportRules
         * @param string $attributeIndexOrDerivedType
         */
        public static function makeCollectionByAttributeImportRules($attributeImportRules, $attributeIndexOrDerivedType)
        {
            assert('$attributeImportRules instanceof AttributeImportRules');
            assert('is_string($attributeIndexOrDerivedType)');
            $mappingRuleFormsAndElementTypes = array();
            foreach($attributeImportRules::getModelAttributeMappingRuleFormTypesAndElementTypes()
                    as $mappingRuleFormType => $elementType)
            {
                $mappingRuleFormClassName          = $mappingRuleFormType . 'MappingRuleForm';
                $mappingRuleForm                   = new $mappingRuleFormClassName(
                                                         $attributeImportRules->getModelClassName(),
                                                         $attributeIndexOrDerivedType);
                $mappingRuleFormsAndElementTypes[] = array('elementType'     => $elementType,
                                                           'mappingRuleForm' => $mappingRuleForm);
            }
            return $mappingRuleFormsAndElementTypes;
        }

        /**
         * Given an array of mapping data and an import rules type, make an array of mapping rule forms
         * and element types.  This is indexed by the column name from the mapping data.  If the type of column
         * is 'extraColumn', then the mapping rules forms will be set with the scenario 'extraColumn'.
         * @param array $mappingData
         * @param string $importRulesType
         */
        public static function makeFormsAndElementTypesByMappingDataAndImportRulesType($mappingData, $importRulesType)
        {
            assert('is_array($mappingData)');
            assert('is_string($importRulesType)');
            $mappingRuleFormsAndElementTypes = array();
            foreach($mappingData as $columnName => $mappingDataByColumn)
            {
                $mappingRuleFormsAndElementTypes[$columnName] = array();
                assert('isset($mappingDataByColumn["mappingRulesData"])');
                assert('$mappingDataByColumn["type"] == "importColumn" || $mappingDataByColumn["type"] == "extraColumn"');
                assert('$mappingDataByColumn["attributeIndexOrDerivedType"] != null');
                $attributeImportRules = AttributeImportRulesFactory::
                                        makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                        $importRulesType, $mappingDataByColumn['attributeIndexOrDerivedType']);
                assert('$attributeImportRules!= null');
                foreach($mappingDataByColumn["mappingRulesData"] as $mappingRuleFormClassName => $mappingRuleFormData)
                {
                    $mappingRuleFormAndElementTypes = $attributeImportRules::
                                                      getModelAttributeMappingRuleFormTypesAndElementTypes();
                    $elementType                    = $mappingRuleFormAndElementTypes
                                                      [$mappingRuleFormClassName::getType()];
                    $mappingRuleForm                = static::makeForm($importRulesType,
                                                      $mappingDataByColumn['attributeIndexOrDerivedType'],
                                                      $mappingRuleFormClassName);
                    if($mappingDataByColumn['type'] == "extraColumn")
                    {
                        $mappingRuleForm->setScenario = 'extraColumn';
                    }
                    $mappingRuleForm->setAttributes   ($mappingRuleFormData);
                    $mappingRuleFormsAndElementTypes[$columnName][]= array('mappingRuleForm' => $mappingRuleForm,
                                                                           'elementType'     => $elementType);
                }
            }
            return $mappingRuleFormsAndElementTypes;
        }

        protected static function makeForm($importRulesType, $attributeIndexOrDerivedType, $mappingRuleFormClassName)
        {
            assert('is_string($importRulesType)');
            assert('is_string($attributeIndexOrDerivedType)');
            assert('is_string($mappingRuleFormClassName)');
            $importRulesTypeClassName = $importRulesType . 'ImportRules';
            $modelClassName           = $importRulesTypeClassName::getModelClassNameByAttributeIndexOrDerivedType(
                                        $attributeIndexOrDerivedType);
            $mappingRuleForm          = new $mappingRuleFormClassName($importRulesTypeClassName::getModelClassName(),
                                        $attributeIndexOrDerivedType);
            return $mappingRuleForm;
        }

        /**
         * Given an array of mappingRuleForms data, validate each form.  If any form does not validate,
         * then return false.
         * @param array $mappingDataMappingRuleFormsAndElementTypes
         * @return true/false validation results.
         */
        public static function validateMappingRuleForms($mappingRuleFormsData)
        {
            assert('is_array($mappingRuleFormsData)');
            $anyValidatedFalse = false;
            foreach($mappingRuleFormsData as $mappingRuleFormData)
            {
                assert('$mappingRuleFormData["mappingRuleForm"] instanceof MappingRuleForm');
                if(!$mappingRuleFormData["mappingRuleForm"]->validate())
                {
                    $anyValidatedFalse = true;
                }

            }
            return !$anyValidatedFalse;
        }
    }
?>
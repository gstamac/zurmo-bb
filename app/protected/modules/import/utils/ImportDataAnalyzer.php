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
     * Class for handling the data analysis performed on mapped data in an import.  Each column mapping can
     * be analyzed and the resulting message and instructional data will be stored in an array which is accessible
     * once the analysis is complete.
     * NOTE - Analysis is only performed on mapped import columns and not extra columns with mapping rules.
     */
    class ImportDataAnalyzer
    {
        /**
         * ImportRules object to base the analysis on.
         * @var object
         */
        protected $importRules;

        /**
         * AnalyzerSupportedDataProvider extended data provider for use in querying data to analyze.
         * @var object
         */
        protected $dataProvider;

        /**
         * Analyzing data can produce messages that need to be saved for later use.
         * @var array
         */
        protected $messagesData = array();

        /**
         * Analyzing data can produce instructional data that needs to be saved for later use during the actual import.
         * @var array
         */
        protected $importInstructionsData = array();

        /**
         * @param string $importRules
         * @param object $dataProvider
         */
        public function __construct($importRules, $dataProvider)
        {
            assert('$importRules instanceof ImportRules');
            assert('$dataProvider instanceof AnalyzerSupportedDataProvider');
            $this->importRules  = $importRules;
            $this->dataProvider = $dataProvider;
        }

        public function analyzePageOfRows()
        {
            $data = $this->dataProvider->getData(true);
            foreach ($data as $rowBean)
            {
                assert('$rowBean->id != null');
                $columnMessages = array();
                foreach($this->analyzableColumnNames as $columnName)
                {
                    $attributeIndexOrDerivedType = $this->mappingData[$columnName]['attributeIndexOrDerivedType'];
                    $attributeImportRules = AttributeImportRulesFactory::
                                            makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                            $this->importRules->getType(),
                                            $attributeIndexOrDerivedType);
                    $modelClassName       = $attributeImportRules->getModelClassName();
                    $attributeNames       = $attributeImportRules->getRealModelAttributeNames();
                    //todo: begin this if/else can be made into a static resolve method here.
                    if (count($attributeNames) > 1 || $attributeNames == null)
                    {
                        $dataAnalyzerAttributeName = null;
                    }
                    else
                    {
                        $dataAnalyzerAttributeName = $attributeNames[0];
                    }
                    //todo: end
                    //todO: somehow we need to do something with dataAnalyzerAttributeName, just not sure how yet.
                    if (null != $attributeValueSanitizerUtilTypes = $attributeImportRules->getSanitizerUtilTypesInProcessingOrder())
                    {
                        assert('is_array($attributeValueSanitizerUtilTypes)');
                        foreach ($attributeValueSanitizerUtilTypes as $attributeValueSanitizerUtilType)
                        {
                            $sanitizer = ImportSanitizerUtilFactory::makeByType($attributeValueSanitizerUtilType);
                            $sanitizer->analyzeByRowAndColumnName($rowBean, $columnName, $modelClassName); //todo: pass $dataAnalyzerAttributeName here?
                            foreach($sanitizer->getAnalysisMessages() as $message)
                            {
                                $columnMessages[$columnName][] = $message;
                            }
                            if(null !== $missingCustomFieldValue = $sanitizer->getMissingCustomFieldValue())
                            {
                                $this->customFieldsInstructionData->addMissingValueByColumnName($missingCustomFieldValue, $columnName);
                            }
                        }
                    }
                }
                if(!empty($columnMessages))
                {
                    $rowBean->serializedAnalysisMessages = serialize($columnMessages);
                    $rowBean->analysisStatus             = static::STATUS_INFO;
                }
                else
                {
                    $rowBean->serializedAnalysisMessages = null;
                    $rowBean->analysisStatus             = static::STATUS_CLEAN;
                }
                $rowBean->save();
            }
        }

        /**
         * Given a column name and column mapping data, perform data analysis on the column based on the mapped
         * attribute index or derived type.  The attribute index or derived type will correspond with an attribute
         * import rules which will have information on what sanitizers to use.  Based on this, the correct sanitizers
         * will be called and their appropriate analyzers will be used.
         * NOTE - Analysis is only performed on mapped import columns and not extra columns with mapping rules.
         * @param string $columnName
         * @param array $columnMappingData
         */
        public function analyzeByColumnNameAndColumnMappingData($columnName, $columnMappingData)
        {
            assert('is_string($columnMappingData["attributeIndexOrDerivedType"]) ||
                    $columnMappingData["attributeIndexOrDerivedType"] == null');
                    assert('$columnMappingData["type"] == "importColumn" ||
            $columnMappingData["type"] == "extraColumn"');
            if ($columnMappingData['attributeIndexOrDerivedType'] == null)
            {
                return;
            }
            //Currently does not support data analysis on extra columns.
            if ($columnMappingData['type'] =='extraColumn')
            {
                return;
            }
            $attributeImportRules = AttributeImportRulesFactory::
                                    makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    $this->importRules->getType(),
                                    $columnMappingData['attributeIndexOrDerivedType']);
            $modelClassName       = $attributeImportRules->getModelClassName();
            $attributeNames       = $attributeImportRules->getRealModelAttributeNames();
            if (count($attributeNames) > 1 || $attributeNames == null)
            {
                $dataAnalyzerAttributeName = null;
            }
            else
            {
                $dataAnalyzerAttributeName = $attributeNames[0];
            }
            if (null != $attributeValueSanitizerUtilTypes = $attributeImportRules->getSanitizerUtilTypesInProcessingOrder())
            {
                assert('is_array($attributeValueSanitizerUtilTypes)');
                foreach ($attributeValueSanitizerUtilTypes as $attributeValueSanitizerUtilType)
                {
                    $attributeValueSanitizerUtilClassName = $attributeValueSanitizerUtilType . 'SanitizerUtil';
                    if ($attributeValueSanitizerUtilClassName::supportsSqlAttributeValuesDataAnalysis())
                    {
                        $sanitizerUtilType              = $attributeValueSanitizerUtilClassName::getType();
                        $sqlAttributeValuesDataAnalyzer = $attributeValueSanitizerUtilClassName::
                                                          makeSqlAttributeValueDataAnalyzer($modelClassName,
                                                                                            $dataAnalyzerAttributeName);
                        assert('$sqlAttributeValuesDataAnalyzer != null');
                        $this->resolveRun($columnName, $columnMappingData,
                                          $attributeValueSanitizerUtilClassName,
                                          $sqlAttributeValuesDataAnalyzer);
                        $messages       = $sqlAttributeValuesDataAnalyzer->getMessages();
                        if ($messages != null)
                        {
                            foreach ($messages as $message)
                            {
                                $moreAvailable     = $sqlAttributeValuesDataAnalyzer::supportsAdditionalResultInformation();
                                $this->addMessageDataByColumnName($columnName, $message, $sanitizerUtilType, $moreAvailable);
                            }
                        }
                        $instructionsData = $sqlAttributeValuesDataAnalyzer->getInstructionsData();
                        if (!empty($instructionsData))
                        {
                            $this->addInstructionDataByColumnName($columnName, $instructionsData, $sanitizerUtilType);
                        }
                    }
                    elseif ($attributeValueSanitizerUtilClassName::supportsBatchAttributeValuesDataAnalysis())
                    {
                        $sanitizerUtilType = $attributeValueSanitizerUtilClassName::getType();
                        $batchAttributeValuesDataAnalyzer = $attributeValueSanitizerUtilClassName::
                                                            makeBatchAttributeValueDataAnalyzer($modelClassName,
                                                                                                $dataAnalyzerAttributeName);
                        assert('$batchAttributeValuesDataAnalyzer != null');
                        $this->resolveRun($columnName, $columnMappingData,
                                                       $attributeValueSanitizerUtilClassName,
                                                       $batchAttributeValuesDataAnalyzer);
                        $messages                    = $batchAttributeValuesDataAnalyzer->getMessages();
                        if ($messages != null)
                        {
                            foreach ($messages as $message)
                            {
                                $moreAvailable     = $batchAttributeValuesDataAnalyzer::
                                                     supportsAdditionalResultInformation();
                                $this->addMessageDataByColumnName($columnName, $message, $sanitizerUtilType, $moreAvailable);
                            }
                        }
                        $instructionsData = $batchAttributeValuesDataAnalyzer->getInstructionsData();
                        if (!empty($instructionsData))
                        {
                            $this->addInstructionDataByColumnName($columnName, $instructionsData, $sanitizerUtilType);
                        }
                    }
                }
            }
        }

        protected function resolveRun($columnName, $columnMappingData,
                                                   $attributeValueSanitizerUtilClassName, $dataAnalyzer)
        {
            assert('is_string($columnName)');
            assert('is_array($columnMappingData)');
            assert('is_subclass_of($attributeValueSanitizerUtilClassName, "SanitizerUtil")');
            assert('$dataAnalyzer instanceof BatchAttributeValueDataAnalyzer ||
                    $dataAnalyzer instanceof SqlAttributeValueDataAnalyzer');
            $classToEvaluate = new ReflectionClass(get_class($dataAnalyzer));
            if ($classToEvaluate->implementsInterface('LinkedToMappingRuleDataAnalyzerInterface'))
            {
                $mappingRuleType = $attributeValueSanitizerUtilClassName::getLinkedMappingRuleType();
                assert('$mappingRuleType != null');
                $mappingRuleFormClassName = $mappingRuleType .'MappingRuleForm';
                if (!isset($columnMappingData['mappingRulesData'][$mappingRuleFormClassName]))
                {
                    //do nothing. Either the data from the UI was improper or there is for some reason no mappingRulesFormData
                }
                else
                {
                    $mappingRuleData = $columnMappingData['mappingRulesData'][$mappingRuleFormClassName];
                    assert('$mappingRuleData != null');
                    $dataAnalyzer->runAndMakeMessages($this->dataProvider, $columnName, $mappingRuleType, $mappingRuleData);
                }
            }
            else
            {
                $dataAnalyzer->runAndMakeMessages($this->dataProvider, $columnName);
            }
        }

        /**
         * Add a analysis results message by column name.
         * @param string  $columnName
         * @param string  $message
         * @param string  $sanitizerUtilType
         * @param boolean $moreAvailable
         */
        public function addMessageDataByColumnName($columnName, $message, $sanitizerUtilType, $moreAvailable)
        {
            assert('is_string($columnName)');
            assert('is_string($message)');
            assert('is_string($sanitizerUtilType)');
            assert('is_bool($moreAvailable)');
            $this->messagesData[$columnName][] = array('message'           => $message,
                                                  'sanitizerUtilType' => $sanitizerUtilType,
                                                  'moreAvailable'     => $moreAvailable);
        }

        /**
         * Add instructional data by column name and sanitizer type
         * @param string $columnName
         * @param array  $instructionData
         * @param string $sanitizerUtilType
         */
        public function addInstructionDataByColumnName($columnName, $instructionData, $sanitizerUtilType)
        {
            assert('is_string($columnName)');
            assert('is_string($instructionData) || is_array($instructionData)');
            assert('is_string($sanitizerUtilType)');
            $this->importInstructionsData[$columnName][$sanitizerUtilType] = $instructionData;
        }

        /**
         * @return array of messages data.
         */
        public function getMessagesData()
        {
            return $this->messagesData;
        }

        /**
         * @return array of instructions data.
         */
        public function getImportInstructionsData()
        {
            return $this->importInstructionsData;
        }
    }
?>
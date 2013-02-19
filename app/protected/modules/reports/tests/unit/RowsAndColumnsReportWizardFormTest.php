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
    * Test ReportWizardForm validation functions.
    */
    class RowsAndColumnsReportWizardFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }
        
        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');            
        }
        
        public function testValidateFilters()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();                  
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'string';
            $filter->operator                        = OperatorRules::TYPE_EQUALS;
            $filter->value                           = 'Zurmo';
            $rowsAndColumnsReportWizardForm->filters = array($filter);            
            $rowsAndColumnsReportWizardForm->validateFilters();           
        }
        
        public function testValidateFiltersStructure()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'createdDateTime';
            $filter->operator                        = OperatorRules::TYPE_BETWEEN;            
            $filter->value                           = '2013-02-19 00:00';
            $filter->secondValue                     = '2013-02-20 00:00';                   
            $rowsAndColumnsReportWizardForm->filters = array($filter);  
            $rowsAndColumnsReportWizardForm->filtersStructure  = '1 and 2';            
            $rowsAndColumnsReportWizardForm->validateFiltersStructure();              
        }
        
        public function testValidateDisplayAttributes()
        {                       
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();
            $reportModelTestItem = new ReportModelTestItem();
            $reportModelTestItem->date = '2013-02-12';
            $displayAttribute    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute->attributeIndexOrDerivedType = 'date';
            $rowsAndColumnsReportWizardForm->displayAttributes = array($displayAttribute);
            $rowsAndColumnsReportWizardForm->validateDisplayAttributes();
        }
        
        public function testValidateOrderBys()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();
            $orderBy                                 = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType = 'modifiedDateTime';
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->order                       = 'desc';
            $rowsAndColumnsReportWizardForm->orderBys = array($orderBy);
            $rowsAndColumnsReportWizardForm->validateOrderBys();
        }        

        public function testValidateSpotConversionCurrencyCode()
        {
        
        }        
    }
?>    
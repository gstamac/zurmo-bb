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

    class ReportResultsRowDataTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $attributeName = 'calculated';
            $attributeForm = new CalculatedNumberAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array('en' => 'Test Calculated');
            $attributeForm->formula          = 'integer + float';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new ReportModelTestItem());
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            DisplayAttributeForReportForm::resetCount();
        }

        public function testGetModel()
        {
            $reportModelTestItemX = new ReportModelTestItem();
            $reportModelTestItemX->firstName = 'xFirst';
            $reportModelTestItemX->lastName = 'xLast';
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'FullNameForReportResults';

            $reportModelTestItemY = new ReportModelTestItem();
            $reportModelTestItemY->firstName = 'yFirst';
            $reportModelTestItemY->lastName = 'yLast';
            $displayAttributeY    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeY->setModelAliasUsingTableAliasName('def');
            $displayAttributeY->attributeIndexOrDerivedType = 'FullNameForReportResults';

            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX, $displayAttributeY));
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemY, 'def');

            $model1 = $reportResultsRowData->getModel('attribute0');
            $this->assertEquals('xFirst xLast', strval($model1));
            $model2 = $reportResultsRowData->getModel('attribute1');
            $this->assertEquals('yFirst yLast', strval($model2));
        }

        public function testGettingAttributesOfDifferentTypes()
        {
            $reportModelTestItemX = new ReportModelTestItem();
            $reportModelTestItemX->string = 'someString';
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                    Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'string';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX));
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');

            $this->assertEquals('someString', $reportResultsRowData->attribute0);

            //todo: split this into mulitple methods since it will be easier to read
            //todo: oinly if you want to so for contactState it can conitue to look at type in model.. this would be acceptable solution
            //todo: also test all other types including calcualted
            //todo: other list view adapter useage..
        }
    }
?>
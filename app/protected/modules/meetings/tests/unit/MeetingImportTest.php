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

    class MeetingImportTest extends ActivityImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            MeetingTestHelper::createCategories();
        }



        public function testSimplUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');

            $meetings                              = Meeting::getAll();
            $this->assertEquals(0, count($meetings));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'Meeting';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.meetings.tests.unit.files'));

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $mappingData = array(
                'column_0' => ImportTestHelper::makeStringColumnMappingData       ('name'),
                'column_1' => ImportTestHelper::makeStringColumnMappingData       ('location'),
                'column_2' => ImportTestHelper::makeDateTimeColumnMappingData     ('startDateTime'),
                'column_3' => ImportTestHelper::makeDateTimeColumnMappingData     ('endDateTime'),
                'column_4' => ImportTestHelper::makeDropDownColumnMappingData     ('category'),
                'column_5' => ImportTestHelper::makeModelDerivedColumnMappingData ('AccountDerived'),
                'column_6' => ImportTestHelper::makeModelDerivedColumnMappingData ('ContactDerived'),
                'column_7' => ImportTestHelper::makeModelDerivedColumnMappingData ('OpportunityDerived'),
                'column_8' => ImportTestHelper::makeTextAreaColumnMappingData     ('description'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Meetings');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             new ExplicitReadWriteModelPermissions());
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 3 models where created.
            $meetings = Meeting::getAll();
            $this->assertEquals(3, count($meetings));

            $meetings = Meeting::getByName('meeting1');
            $this->assertEquals(1, count($meetings[0]));
            $this->assertEquals(1, count($meetings[0]->activityItems));
            $this->assertEquals('testAccount', $meetings[0]->activityItems[0]->name);
            $this->assertEquals('Account',     get_class($meetings[0]->activityItems[0]));

            $meetings = Meeting::getByName('meeting2');
            $this->assertEquals(1, count($meetings[0]));
            $this->assertEquals(1, count($meetings[0]->activityItems));
            $this->assertEquals('testContact', $meetings[0]->activityItems[0]->firstName);
            $this->assertEquals('Contact',     get_class($meetings[0]->activityItems[0]));

            $meetings = Meeting::getByName('meeting3');
            $this->assertEquals(1, count($meetings[0]));
            $this->assertEquals(1, count($meetings[0]->activityItems));
            $this->assertEquals('testOpportunity', $meetings[0]->activityItems[0]->name);
            $this->assertEquals('Opportunity',     get_class($meetings[0]->activityItems[0]));

            //Confirm 10 rows were processed as 'created'.
            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
                                                                 . ImportRowDataResultsUtil::CREATED));

            //Confirm that 0 rows were processed as 'updated'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::UPDATED));

            //Confirm 2 rows were processed as 'errors'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::ERROR));

            $beansWithErrors = ImportDatabaseUtil::getSubset($import->getTempTableName(),     "status = "
                                                                 . ImportRowDataResultsUtil::ERROR);
            $this->assertEquals(0, count($beansWithErrors));
        }
    }
?>
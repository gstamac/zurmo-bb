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

    class DisplayAttributesReportQueryBuilderTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            DisplayAttributeForReportForm::resetCount();
        }

        public function testNonRelatedNonDerivedAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'phone';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Add a second attribute on the same model
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute2                     = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'integer';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testAttributeOnOwnedModelWithNoBeanSkips()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is on an owned model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'primaryAddress___street1';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testAttributeOnOwnedModelWithBeanSkip()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is on an owned model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'dropDown';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Two display attributes that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttributeThatIsAUserRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'owner___lastName';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Two display attributes that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'owner___lastName';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'modifiedByUser___lastName';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedAttributeNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute nested in a relation
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___phone';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem2{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());

            //Add a second attribute
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute2                     = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'integer';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent = "select {$q}reportmodeltestitem2{$q}.{$q}id{$q}, {$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testAttributeOnOwnedModelWithNoBeanSkipsThatIsNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is on an owned model through a relation
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem2');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasMany2___primaryAddress___street1';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttributeThatIsAUserRelationWhenNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___owner___lastName';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select {$q}reportmodeltestitem2{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());

            //Two display attributes that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___owner___lastName';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___modifiedByUser___lastName';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $this->assertEquals("select {$q}reportmodeltestitem2{$q}.{$q}id{$q} ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            //Add third display attribute on the base model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___owner___lastName';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___modifiedByUser___lastName';
            $displayAttribute3                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'modifiedByUser___lastName';
            $content        = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent = "select {$q}reportmodeltestitem2{$q}.{$q}id{$q}, {$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }


        public function testDisplayCalculationAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'Count';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select count({$q}reportmodeltestitem{$q}.{$q}id{$q}) col0 ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Minimum';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select min({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Maximum';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select max({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'integer__Average';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select avg({$q}reportmodeltestitem{$q}.{$q}integer{$q}) col0 ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'integer__Summation';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select sum({$q}reportmodeltestitem{$q}.{$q}integer{$q}) col0 ", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Day';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select day({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Week';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select week({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Month';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select month({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Quarter';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select quarter({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            DisplayAttributeForReportForm::resetCount();
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime__Year';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $this->assertEquals("select year({$q}item{$q}.{$q}createddatetime{$q}) col0 ", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDisplayCalculationMoreThanOneAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     =  new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    =  new RedBeanModelSelectQueryAdapter();
            $builder                               =  new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      =  new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'Count';
            $displayAttribute2                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType  = 'createdDateTime__Minimum';
            $displayAttribute3                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute3->attributeIndexOrDerivedType  = 'createdDateTime__Maximum';
            $displayAttribute4                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute4->attributeIndexOrDerivedType  = 'integer__Average';
            $displayAttribute5                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute5->attributeIndexOrDerivedType  = 'integer__Summation';
            $displayAttribute6                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute6->attributeIndexOrDerivedType  = 'createdDateTime__Day';
            $displayAttribute7                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute7->attributeIndexOrDerivedType  = 'createdDateTime__Week';
            $displayAttribute8                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute8->attributeIndexOrDerivedType  = 'createdDateTime__Month';
            $displayAttribute9                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute9->attributeIndexOrDerivedType  = 'createdDateTime__Quarter';
            $displayAttribute10                     = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute10->attributeIndexOrDerivedType  = 'createdDateTime__Year';

            $content = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3,
                                                        $displayAttribute4, $displayAttribute5, $displayAttribute6,
                                                        $displayAttribute7, $displayAttribute8, $displayAttribute9,
                                                        $displayAttribute10));
            $compareContent  = "select count({$q}reportmodeltestitem{$q}.{$q}id{$q}) col0, ";
            $compareContent .= "min({$q}item{$q}.{$q}createddatetime{$q}) col1, ";
            $compareContent .= "max({$q}item{$q}.{$q}createddatetime{$q}) col2, ";
            $compareContent .= "avg({$q}reportmodeltestitem{$q}.{$q}integer{$q}) col3, ";
            $compareContent .= "sum({$q}reportmodeltestitem{$q}.{$q}integer{$q}) col4, ";
            $compareContent .= "day({$q}item{$q}.{$q}createddatetime{$q}) col5, ";
            $compareContent .= "week({$q}item{$q}.{$q}createddatetime{$q}) col6, ";
            $compareContent .= "month({$q}item{$q}.{$q}createddatetime{$q}) col7, ";
            $compareContent .= "quarter({$q}item{$q}.{$q}createddatetime{$q}) col8, ";
            $compareContent .= "year({$q}item{$q}.{$q}createddatetime{$q}) col9 ";

            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }


        public function testASingleDisplayCalculationAttributesThatIsNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___createdDateTime__Maximum';

            $content        = $builder->makeQueryContent(array($displayAttribute));
            $compareContent = "select max({$q}item{$q}.{$q}createddatetime{$q}) col0 ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testMultipleDisplayCalculationAttributesThatAreNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //A single display attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___Count';
            $displayAttribute2                     = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType  = 'createdDateTime__Minimum';
            $displayAttribute3                     = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $displayAttribute3->attributeIndexOrDerivedType  = 'hasOne___createdDateTime__Maximum';

            $content = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent  = "select count({$q}reportmodeltestitem2{$q}.{$q}id{$q}) col0, ";
            $compareContent .= "min({$q}item{$q}.{$q}createddatetime{$q}) col1, ";
            $compareContent .= "max({$q}item1{$q}.{$q}createddatetime{$q}) col2 ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());

        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasOneRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_ONE
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                     = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem2{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                     = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany___createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem3{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasManyBelongsToRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                     = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'account___createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}account{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}account1{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAManyManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is MANY_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany1___createdDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem3{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasOneRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with two on a relation that is HAS_ONE
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem2{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasMany___createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem3{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasManyBelongsToRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'account___createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'account___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}account1{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAManyManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is MANY_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasMany1___createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany1___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasOneRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is HAS_ONE and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___createdDateTime';
            $displayAttribute3                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'hasOne___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent  = "select {$q}reportmodeltestitem{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem2{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasManyRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is HAS_MANY and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany___createdDateTime';
            $displayAttribute3                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'hasMany___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent  = "select {$q}reportmodeltestitem{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem3{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasManyBelongsToRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'account___createdDateTime';
            $displayAttribute3                              = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'account___modifiedDateTime';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent  = "select {$q}account{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}account1{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAManyManyRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is MANY_MANY and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany1___createdDateTime';
            $displayAttribute3                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType = 'hasMany1___modifiedDateTime';
            $content                                        = $builder->makeQueryContent(array($displayAttribute,
                                                              $displayAttribute2, $displayAttribute3));
            $compareContent  = "select {$q}reportmodeltestitem3{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenOneIsOnRelatedModelAndOneIsOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with 1 on relation and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'dropDown';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___dropDown';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem9{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenBothAreOnTheSameRelatedModelButDifferentRelations()
        {

            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on a related model, but the links are different
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany___dropDown';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem1{$q}.{$q}id{$q} ";
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenBothAreOnRelatedModelsThatAreDifferent()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on 2 different related models
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne2___dropDownX';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem8{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenBothAreOnTheSameRelatedModel()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on a related model, but 2 different dropdowns
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___dropDown2';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //2 __User attributes on the same model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'modifiedByUser__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem9{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //2 __User attributes on the same model, one is owned, so not originating both from Item
            DisplayAttributeForReportForm::resetCount();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'owner__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem9{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeOneOnSelfAndOneOnRelatedModelWhereSameAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //2 createdByUser__User attributes. One of self, one on related.
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                              Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___createdByUser__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem9{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeOneOnSelfAndOneOnRelatedModelWhereDifferentAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Self createdByUser__User, related owner__User
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___owner__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem9{$q}.{$q}id{$q}, ";
            $compareContent .= "{$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeBothOnRelatedModelWhereDifferentAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Related createdByUser__User and related owner__User. On same related model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'hasOne___createdByUser__User';
            $displayAttribute2                              = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___owner__User';
            $content                               = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent  = "select {$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNestedRelationsThatComeBackOnTheBaseModel()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Base model is Account.  Get related contact's opportunity's account's name
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'contacts___opportunities___account___name';
            $content                               = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                        = "select {$q}account1{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }


        public function testThreeTestedRelationsWhereTheyBothGoToTheSameModelButAtDifferentNestingPoints()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            //Accounts -> Opportunities, but also Accounts -> Contacts -> Opportunities,
            //and a third to go to Accounts again.
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'opportunities___name';
            $displayAttribute2                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'contacts___opportunities___name';
            $displayAttribute3                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->attributeIndexOrDerivedType  = 'contacts___opportunities___account___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2, $displayAttribute3));
            $compareContent                         = "select {$q}opportunity{$q}.{$q}id{$q}, " .
                                                      "{$q}opportunity1{$q}.{$q}id{$q}, " .
                                                      "{$q}account1{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelOne()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'meetings___category';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "select {$q}meeting{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelTwo()
        {
            //This test tests name instead of category which is an attribute on the meeting model.
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                                = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                               Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'meetings___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "select {$q}meeting{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoAttributesDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModel()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'meetings___category';
            $displayAttribute2                               = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                               Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'meetings___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select {$q}meeting{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatDoesNotCastDown()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'model5ViaItem___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "select {$q}reportmodeltestitem5{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeWhenThroughARelation()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests derivedRelation when going through a relation already before doing the derived relation
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'opportunities___meetings___category';
            $displayAttribute2                      = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'opportunities___meetings___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select {$q}meeting{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeWithCastingHintToNotCastDownSoFar()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'meetings___latestDateTime';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "select {$q}meeting{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testDisplayCalculationDerivedRelationViaCastedUpModelAttributeThatDoesNotCastDown()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType   = 'model5ViaItem___integer__Average';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "select avg({$q}reportmodeltestitem5{$q}.{$q}integer{$q}) col0 ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDisplayCalculationDerivedRelationViaCastedUpModelAttributeWhenThroughARelation()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests derivedRelation when going through a relation already before doing the derived relation
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType   = 'opportunities___meetings___startDateTime__Maximum';
            $displayAttribute2                      = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType  = 'opportunities___meetings___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select max({$q}meeting{$q}.{$q}startdatetime{$q}) col0, " .
                                                      "{$q}meeting{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDisplayCalculationDerivedRelationViaCastedUpModelAttributeWithCastingHintToNotCastDownSoFar()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType   = 'meetings___latestDateTime__Maximum';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "select max({$q}activity{$q}.{$q}latestdatetime{$q}) col0 ";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testInferredRelationModelAttributeWithTwoAttributes()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests inferredRelation with 2 attributes on the opposing model. Only one declares the module specifically
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___industry';
            $displayAttribute2                      = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select {$q}account{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testInferredRelationModelAttributeWithTwoAttributesNestedTwoLevelsDeep()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___opportunities___stage';
            $displayAttribute2                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___opportunities___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select {$q}opportunity{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testInferredRelationModelAttributeWithTwoAttributesComingAtItFromANestedPoint()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Also declaring Via modules
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem7');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'model5___ReportModelTestItem__reportItems__Inferred___phone';
            $displayAttribute2                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'model5___ReportModelTestItem__reportItems__Inferred___dropDown';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select {$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithItemAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "select {$q}account{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithMixedInAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___owner__User';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "select {$q}account{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDowButAlsoWithFullCastDown()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime';
            $displayAttribute2                               = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                               Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select {$q}account{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }


        public function testDisplayCalculationInferredRelationModelAttributeWithTwoAttributes()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests inferredRelation with 2 attributes on the opposing model. Only one declares the module specifically
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___employees__Average';
            $displayAttribute2                      = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select avg({$q}account{$q}.{$q}employees{$q}) col0, " .
                                                      "{$q}account{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDisplayCalculationInferredRelationModelAttributeWithTwoAttributesNestedTwoLevelsDeep()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___opportunities___amount__Average';
            $displayAttribute2                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___opportunities___closeDate';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select avg({$q}currencyvalue{$q}.{$q}value{$q}) col0, " .
                                                      "{$q}opportunity{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testDisplayCalculationInferredRelationModelAttributeWithTwoAttributesComingAtItFromANestedPoint()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Also declaring Via modules
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem7');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType   = 'model5___ReportModelTestItem__reportItems__Inferred___integer__Average';
            $displayAttribute2                      = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType  = 'model5___ReportModelTestItem__reportItems__Inferred___dropDown';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select avg({$q}reportmodeltestitem{$q}.{$q}integer{$q}) col0, " .
                                                      "{$q}reportmodeltestitem{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testDisplayCalculationInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithItemAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime__Maximum';
            $content                                = $builder->makeQueryContent(array($displayAttribute));
            $compareContent                         = "select max({$q}item{$q}.{$q}createddatetime{$q}) col0 ";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        public function testDisplayCalculationInferredRelationModelAttributeWithCastingHintToNotCastDowButAlsoWithFullCastDown()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $selectQueryAdapter                     = new RedBeanModelSelectQueryAdapter();
            $builder                                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                       = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime__Maximum';
            $displayAttribute2                      = new DisplayAttributeForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $content                                = $builder->makeQueryContent(array($displayAttribute, $displayAttribute2));
            $compareContent                         = "select max({$q}item{$q}.{$q}createddatetime{$q}) col0, " .
                                                      "{$q}account{$q}.{$q}id{$q} ";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
            //todo: validate the correct table information.
        }

        /**
         * echo "<pre>";
        print_r($joinTablesAdapter->getFromTablesAndAliases());
        print_r($joinTablesAdapter->getLeftTablesAndAliases());
        echo "</pre>";
         */

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownTwiceWithNoSkips()
        {
            //todo: test casting down more than one level. not sure how to test this.. since meetings is only one skip past activity not really testing that castDown fully
            $this->fail();
        }

        public function testPolymorphic()
        {
            //todo: test polymorphics too? maybe we wouldnt have any for now? but we should still mark fail test here...
            $this->fail();
        }
    }
?>
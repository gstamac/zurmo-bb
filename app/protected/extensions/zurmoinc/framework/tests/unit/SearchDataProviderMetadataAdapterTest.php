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

    class SearchDataProviderMetadataAdapterTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testGetAdaptedMetadata()
        {
            $searchAttributes = array(
                'name'          => 'Vomitorio Corp',
                'officePhone'   => '5',
                'billingAddress' => array(
                    'street1'    => null,
                    'street2'    => 'Suite 101',
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'name',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                2 => array(
                    'attributeName' => 'officePhone',
                    'operatorType'  => 'startsWith',
                    'value'         => 5,
                ),
                3 => array(
                    'attributeName'        => 'billingAddress',
                    'relatedAttributeName' => 'street2',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Suite 101',
                ),
            );

            $compareStructure = '1 and 2 and 3';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testGetAdaptedMetadataUsingOrClause()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'name'          => 'Vomitorio Corp',
                'officePhone'   => '5',
                'billingAddress' => array(
                    'street1'    => null,
                    'street2'    => 'Suite 101',
                )
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata(false);
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'name',
                    'operatorType'  => 'startsWith',
                    'value'         => 'Vomitorio Corp',
                ),
                2 => array(
                    'attributeName' => 'officePhone',
                    'operatorType'  => 'startsWith',
                    'value'         => 5,
                ),
                3 => array(
                    'attributeName'        => 'billingAddress',
                    'relatedAttributeName' => 'street2',
                    'operatorType'         => 'startsWith',
                    'value'                => 'Suite 101',
                ),
            );

            $compareStructure = '1 or 2 or 3';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }

        public function testSearchFormAttributesAreAdaptedProperly()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchAttributes = array(
                'ABName' => null,
                'anyA'   => null,
            );
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array();

            $compareStructure = null;
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);

            //Now put values in for the search.
            $searchAttributes = array(
                'ABName' => 'something',
                'anyA'   => 'nothing',
            );
            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $searchForm,
                $super->id,
                $searchAttributes
            );
            $metadata = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'aName',
                    'operatorType'  => 'startsWith',
                    'value'         => 'something',
                ),
                2 => array(
                    'attributeName' => 'bName',
                    'operatorType'  => 'startsWith',
                    'value'         => 'something',
                ),
                3 => array(
                    'attributeName'        => 'primaryA',
                    'relatedAttributeName' => 'name',
                    'operatorType'         => 'startsWith',
                    'value'                => 'nothing',
                ),
                4 => array(
                    'attributeName'        => 'secondaryA',
                    'relatedAttributeName' => 'name',
                    'operatorType'         => 'startsWith',
                    'value'                => 'nothing',
                ),
            );

            $compareStructure = '(1 or 2) and (3 or 4)';
            $this->assertEquals($compareClauses, $metadata['clauses']);
            $this->assertEquals($compareStructure, $metadata['structure']);
        }
    }
?>
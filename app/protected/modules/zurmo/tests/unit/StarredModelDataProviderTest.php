<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Data provider for starred models only
     */
    class StarredModelDataProviderTest extends DataProviderBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::createActualPermissionsCacheTable();
            ZurmoDatabaseCompatibilityUtil::createNamedSecurableActualPermissionsCacheTable();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $account              = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account0';
            $account->officePhone = '1234567890';
            $account->save();
            StarredUtil::markModelAsStarred($account);
            $account              = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account1';
            $account->officePhone = '1234567891';
            $account->save();
        }

        public function setUp()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testOnlyGetStarredModels()
        {
            $dataProvider         = new RedBeanModelDataProvider('Account');
            $this->assertCount(2, $dataProvider->getData());
            $dataProvider         = new StarredModelDataProvider('Account');
            $this->assertCount(1, $dataProvider->getData());
        }

        public function testGetStarredModelsWithSearchAttributeData()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'name',
                    'operatorType'         => 'contains',
                    'value'                => 'Test',
                )
            );
            $searchAttributeData['structure'] = '1';
            $dataProvider         = new RedBeanModelDataProvider('Account', null, false, $searchAttributeData);
            $this->assertCount(2, $dataProvider->getData());
            $dataProvider         = new StarredModelDataProvider('Account', null, false, $searchAttributeData);
            $this->assertCount(1, $dataProvider->getData());

            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'name',
                    'operatorType'         => 'equals',
                    'value'                => 'Test',
                )
            );
            $searchAttributeData['structure'] = '1';
            $dataProvider         = new RedBeanModelDataProvider('Account', null, false, $searchAttributeData);
            $this->assertCount(0, $dataProvider->getData());
            $dataProvider         = new StarredModelDataProvider('Account', null, false, $searchAttributeData);
            $this->assertCount(0, $dataProvider->getData());
        }
    }
?>
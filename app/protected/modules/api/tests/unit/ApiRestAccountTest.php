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

    class ApiRestAccountTest extends ApiRestTest
    {
        public function testApiServerUrl()
        {
            $this->assertTrue(strlen($this->serverUrl) > 0);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testGetAccount()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $super = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $account = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer', 'Automotive', $super);
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($account);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $account->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testGetAccount
        */
        public function testDeleteAccount()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $accounts = Account::getByName('First Account');
            $this->assertEquals(1, count($accounts));

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The id specified was invalid.', $response['message']);
        }

        public function testCreateAccount()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $super = User::getByUsername('super');

            $industryValues = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($industryValues);
            $this->assertTrue($industryFieldData->save());

            $typeValues = array(
                'Prospect',
                'Customer',
                'Vendor',
            );
            $typeFieldData = CustomFieldData::getByName('AccountTypes');
            $typeFieldData->serializedData = serialize($typeValues);
            $this->assertTrue($typeFieldData->save());

            $primaryEmail['emailAddress']   = "a@example.com";
            $primaryEmail['optOut']         = 1;

            $secondaryEmail['emailAddress'] = "b@example.com";
            $secondaryEmail['optOut']       = 0;
            $secondaryEmail['isInvalid']    = 1;


            $billingAddress['street1']      = '129 Noodle Boulevard';
            $billingAddress['street2']      = 'Apartment 6000A';
            $billingAddress['city']         = 'Noodleville';
            $billingAddress['postalCode']   = '23453';
            $billingAddress['country']      = 'The Good Old US of A';

            $shippingAddress['street1']     = '25 de Agosto 2543';
            $shippingAddress['street2']     = 'Local 3';
            $shippingAddress['city']        = 'Ciudad de Los Fideos';
            $shippingAddress['postalCode']  = '5123-4';
            $shippingAddress['country']     = 'Latinoland';

            $account = new Account();
            $data['name']                = "My Company";
            $data['officePhone']         = "6438238";
            $data['officeFax']           = "6565465436";
            $data['employees']           = 100;
            $data['website']             = "http://www.google.com";
            $data['annualRevenue']       = "1000000";
            $data['description']         = "Some Description";

            $data['industry']['value']   = $industryValues[2];
            $data['type']['value']       = $typeValues[1];

            $data['primaryEmail']        = $primaryEmail;
            $data['secondaryEmail']      = $secondaryEmail;
            $data['billingAddress']      = $billingAddress;
            $data['shippingAddress']     = $shippingAddress;

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $data['owner'] = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['createdByUser']    = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['modifiedByUser'] = array(
                'id' => $super->id,
                'username' => 'super'
            );

            // We need to unset some empty values from response.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['primaryEmail']['id']);
            unset($response['data']['primaryEmail']['isInvalid']);
            unset($response['data']['secondaryEmail']['id']);
            unset($response['data']['billingAddress']['id']);
            unset($response['data']['billingAddress']['state']);
            unset($response['data']['billingAddress']['longitude']);
            unset($response['data']['billingAddress']['latitude']);
            unset($response['data']['billingAddress']['invalid']);

            unset($response['data']['shippingAddress']['id']);
            unset($response['data']['shippingAddress']['state']);
            unset($response['data']['shippingAddress']['longitude']);
            unset($response['data']['shippingAddress']['latitude']);
            unset($response['data']['shippingAddress']['invalid']);
            unset($response['data']['industry']['id']);
            unset($response['data']['type']['id']);
            unset($response['data']['id']);

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);
        }

        /**
        * @depends testCreateAccount
        */
        public function testUpdateAccount(){
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $accounts = Account::getByName('My Company');
            $this->assertEquals(1, count($accounts));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($accounts[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $data['name']                = "My Company 2";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['name'] = "My Company 2";
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            unset($response['data']['modifiedDateTime']);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testUpdateAccount
        */
        public function testListAccounts()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $accounts = Account::getByName('My Company 2');
            $this->assertEquals(1, count($accounts));
            $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($accounts[0]);
            $compareData  = $redBeanModelToApiDataUtil->getData();

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            $this->assertEquals(array($compareData), $response['data']['array']);
        }

        /**
        * @depends testListAccounts
        */
        public function testUnprivilegedUserViewUpdateDeleteAcounts()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $notAllowedUser = UserTestHelper::createBasicUser('Steven');
            $notAllowedUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $saved = $notAllowedUser->save();
            $this->assertTrue($saved);
            // Test with unprivileged user to view, edit and delete account.
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $accounts = Account::getByName('My Company 2');
            $this->assertEquals(1, count($accounts));
            $data['name']                = "My Company 3";

            // Check first if user doesn't have rights.
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            //now check if user have rights, but no permissions.
            $notAllowedUser->setRight('AccountsModule', AccountsModule::getAccessRight());
            $notAllowedUser->setRight('AccountsModule', AccountsModule::getCreateRight());
            $notAllowedUser->setRight('AccountsModule', AccountsModule::getDeleteRight());
            $saved = $notAllowedUser->save();
            $this->assertTrue($saved);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            // Test with privileged user
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            //Test Delete
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testUnprivilegedUserViewUpdateDeleteAcounts
        */
        public function testSearchAccounts()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $super = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer', 'Automotive', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Second Account', 'Customer', 'Automotive', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Third Account', 'Customer', 'Financial Services', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Forth Account', 'Vendor', 'Financial Services', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Fifth Account', 'Vendor', 'Financial Services', $super);

            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'name' => '',
                ),
                'sort' => 'name',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('Fifth Account', $response['data']['array'][0]['name']);
            $this->assertEquals('First Account', $response['data']['array'][1]['name']);
            $this->assertEquals('Forth Account', $response['data']['array'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('Second Account', $response['data']['array'][0]['name']);
            $this->assertEquals('Third Account', $response['data']['array'][1]['name']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Account';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            $this->assertEquals(1, $response['data']['total']);
            $this->assertEquals('First Account', $response['data']['array'][0]['name']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Account 2';
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/filter/' . $searchParamsQuery, 'GET', $headers);

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(0, $response['data']['total']);
            $this->assertFalse(isset($response['data']['array']));

            // Search by name desc.
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'name' => '',
                ),
                'sort' => 'name.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('Third Account', $response['data']['array'][0]['name']);
            $this->assertEquals('Second Account', $response['data']['array'][1]['name']);
            $this->assertEquals('Forth Account', $response['data']['array'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['array']));
            $this->assertEquals(5, $response['data']['total']);
            $this->assertEquals('First Account', $response['data']['array'][0]['name']);
            $this->assertEquals('Fifth Account', $response['data']['array'][1]['name']);

            // Search by custom fields, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'industry' => array( 'value' => 'Financial Services'),
                    'type'     => array( 'value' => 'Vendor'),
                    'owner'   => array( 'id' => $super->id),
                ),
                'sort' => 'name.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, $response['data']['total']);
            $this->assertEquals(2, count($response['data']['array']));
            $this->assertEquals('Forth Account', $response['data']['array'][0]['name']);
            $this->assertEquals('Fifth Account', $response['data']['array'][1]['name']);
        }

        public function testEditAccountWithIncompleteData()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $super = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('New Account', 'Customer', 'Automotive', $super);

            // Provide data without required field
            $data['officePhone']         = "6438238";
            $data['officeFax']           = "6565465436";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            $accounts = Account::getByName('New Account');
            $this->assertEquals(1, count($accounts));
            $id = $accounts[0]->id;
            $data = array();
            $data['name']                = '';
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        public function testEditAccountWIthIncorrectDataType()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $super = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Newest Account', 'Customer', 'Automotive', $super);

            // Provide data with wrong type.
            $data['name']         = "AAA";
            $data['employees']           = "SS";

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));

            $accounts = Account::getByName('Newest Account');
            $this->assertEquals(1, count($accounts));
            $id = $accounts[0]->id;
            $data = array();
            $data['employees']                = 'DD';
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/accounts/api/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }
    }
?>
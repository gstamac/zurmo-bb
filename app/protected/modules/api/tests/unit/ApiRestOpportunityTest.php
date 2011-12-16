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

    class ApiRestOpportunityTest extends ApiRestTest
    {
        public function testApiServerUrl()
        {
            $this->assertTrue(strlen($this->serverUrl) > 0);
        }

        /**
        * @depends testApiServerUrl
        */
        public function testListViewCreateUpdateDelete()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $notAllowedUser = UserTestHelper::createBasicUser('Steven');
            $notAllowedUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $saved = $notAllowedUser->save();

            $sessionId = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $sessionId
            );

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $sourceValues = array(
                'Word of Mouth',
                'Outbound',
                'Trade Show',
            );
            $sourceFieldData = CustomFieldData::getByName('LeadSources');
            $sourceFieldData->serializedData = serialize($sourceValues);
            $this->assertTrue($sourceFieldData->save());

            $stageValues = array(
                'Prospecting',
                'Negotiating',
                'Close Won',
            );
            $stageFieldData = CustomFieldData::getByName('SalesStages');
            $stageFieldData->serializedData = serialize($stageValues);
            $this->assertTrue($stageFieldData->save());

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $account        = new Account();
            $account->name  = 'Some Account';
            $account->owner = $super;
            $this->assertTrue($account->save());

            $data['name']           = "Michael";
            $data['closeDate']            = "2002-04-03";
            $data['probability']            = "10";
            $data['description']          = "Opportunity description";

            $data['source']['value']     = $sourceValues[1];
            $data['account']['id']       = $account->id;
            $data['amount']       = array(
                'value' => $currencyValue->value,
                'currency' => array(
                    'id' => $currencyValue->currency->id
                )
            );
            $data['stage']['value']     = $stageValues[1];

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/opportunity', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

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
            unset($response['data']['stage']['id']);
            unset($response['data']['source']['id']);
            unset($response['data']['amount']['id']);
            unset($response['data']['rateToBase']['id']);

            $this->assertEquals(ksort($data), ksort($response['data']));
            $id = $response['data']['id'];
            //Test update
            $data['probability']                = "15";
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/opportunity/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/opportunity/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['stage']['id']);
            unset($response['data']['source']['id']);
            unset($response['data']['amount']['id']);
            unset($response['data']['rateToBase']['id']);

            $this->assertEquals(ksort($data), ksort($response['data']));

            //Test List
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/opportunity', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['array']));
            foreach ($response['data']['array'] as $key => $value)
            {
                unset($response['data']['array'][$key]['createdDateTime']);
                unset($response['data']['array'][$key]['modifiedDateTime']);
                unset($response['data']['array'][$key]['stage']['id']);
                unset($response['data']['array'][$key]['source']['id']);
                unset($response['data']['array'][$key]['amount']['id']);
                unset($response['data']['array'][$key]['amount']['rateToBase']);
                unset($response['data']['array'][$key]['id']);
                ksort($response['data']['array'][$key]);
            }
            $this->assertEquals(array($data), $response['data']['array']);

            // Test with unprivileged user to view, edit and delete account.
            $sessionId = $this->login('steven', 'steven');
            $headers = array(
                                        'Accept: application/json',
                                        'ZURMO_SESSION_ID: ' . $sessionId
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/opportunity/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/opportunity/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/opportunity/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);

            // Test with privileged user
            $sessionId = $this->login();
            $headers = array(
                                        'Accept: application/json',
                                        'ZURMO_SESSION_ID: ' . $sessionId
            );

            //Test Delete
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/opportunity/' . $id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_SUCCESS, $response['status']);

            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/api/rest/opportunity/' . $id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiRestResponse::STATUS_FAILURE, $response['status']);
        }
    }
?>
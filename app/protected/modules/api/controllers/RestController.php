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
     *
     * All requests to api will go to this controller.
     * UrlManager must be configured to determine which action is requested.
     * We should be able to catch all actions, even invalid one, and to provide error to user in that case.
     *
     */
    class ApiRestController extends ApiController
    {
        public function actionList()
        {
            $params = Yii::app()->apiRequest->getParams();
            $requestData = $this->getParamsForBaseController();
            if ($requestData['controllerName'] != null)
            {
                $baseController = new $requestData['controllerName']($requestData['controllerName'], 'api');

                $res = $baseController->getAll($requestData['modelName'],
                                               $requestData['searchFormName'],
                                               $requestData['stateMetadataAdapterClassName']);
                if($res['status'] == 'SUCCESS')
                {
                    $status = ApiResponse::STATUS_SUCCESS;
                }
                else
                {
                    $status = ApiResponse::STATUS_FAILURE;
                }

                if(Yii::app()->apiRequest->getRequestType() == ApiRequest::REST)
                {
                    ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                    ApiResponse::STATUS_SUCCESS,
                                                    $res['data'],
                                                    $res['message']);
                }
                else
                {
                    //error
                }
            }
            else
            {
                $error = Yii::t('Default', 'Invalid controller.');
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiRestResponse::STATUS_FAILURE,
                                                null,
                                                $error);
            }
        }

        public function actionView()
        {
            $params = Yii::app()->apiRequest->getParams();
            $requestData = $this->getParamsForBaseController();
            if ($requestData['controllerName'] != null)
            {
                $baseController = new $requestData['controllerName']($requestData['controllerName'], 'api');
                $res = $baseController->getById($requestData['modelName'], $params['id']);
                if($res['status'] == 'SUCCESS')
                {
                    $status = ApiResponse::STATUS_SUCCESS;
                }
                else
                {
                    $status = ApiResponse::STATUS_FAILURE;
                }

                if(Yii::app()->apiRequest->getRequestType() == ApiRequest::REST)
                {
                    ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                    $status,
                                                    $res['data'],
                                                    $res['message']);
                }
                else
                {
                    //error
                }
            }
            else
            {
                $error = Yii::t('Default', 'Invalid controller.');
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiRestResponse::STATUS_FAILURE,
                                                null,
                                                $error);
            }
        }

        public function actionCreate()
        {
            $params = Yii::app()->apiRequest->getParams();
            $requestData = $this->getParamsForBaseController();
            if ($requestData['controllerName'] != null)
            {
                $baseController = new $requestData['controllerName']($requestData['controllerName'], 'api');
                $res = $baseController->create($requestData['modelName'], $params['data']);
                if($res['status'] == 'SUCCESS')
                {
                    $status = ApiResponse::STATUS_SUCCESS;
                }
                else
                {
                    $status = ApiResponse::STATUS_FAILURE;
                }

                if(Yii::app()->apiRequest->getRequestType() == ApiRequest::REST)
                {
                    ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                    $status,
                                                    $res['data'],
                                                    $res['message'],
                                                    $res['errors']);
                }
                else
                {
                    //error
                }
            }
            else
            {
                $error = Yii::t('Default', 'Invalid controller.');
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiRestResponse::STATUS_FAILURE,
                                                null,
                                                $error);
            }
        }

        public function actionUpdate()
        {
            $params = Yii::app()->apiRequest->getParams();
            $requestData = $this->getParamsForBaseController();
            if ($requestData['controllerName'] != null)
            {
                $baseController = new $requestData['controllerName']($requestData['controllerName'], 'api');
                $res = $baseController->update($requestData['modelName'], $params['id'], $params['data']);
                if($res['status'] == 'SUCCESS')
                {
                    $status = ApiResponse::STATUS_SUCCESS;
                }
                else
                {
                    $status = ApiResponse::STATUS_FAILURE;
                }

                if(Yii::app()->apiRequest->getRequestType() == ApiRequest::REST)
                {
                    ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                    $status,
                                                    $res['data'],
                                                    $res['message'],
                                                    $res['errors']);
                }
                else
                {
                    //error
                }
            }
            else
            {
                $error = Yii::t('Default', 'Invalid controller.');
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiRestResponse::STATUS_FAILURE,
                                                null,
                                                $error);
            }
        }

        public function actionDelete()
        {
            $params = Yii::app()->apiRequest->getParams();
            $requestData = $this->getParamsForBaseController();
            if ($requestData['controllerName'] != null)
            {
                $baseController = new $requestData['controllerName']($requestData['controllerName'], 'api');
                $res = $baseController->delete($requestData['modelName'], $params['id']);
                if($res['status'] == 'SUCCESS')
                {
                    $status = ApiResponse::STATUS_SUCCESS;
                }
                else
                {
                    $status = ApiResponse::STATUS_FAILURE;
                }

                if(Yii::app()->apiRequest->getRequestType() == ApiRequest::REST)
                {
                    ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                    $status,
                                                    null,
                                                    $res['message']);
                }
                else
                {
                    //error
                }
            }
            else
            {
                $error = Yii::t('Default', 'Invalid controller.');
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                ApiRestResponse::STATUS_FAILURE,
                                                null,
                                                $error);
            }
        }

        public function actionListCustomData()
        {
            $industryFieldData = CustomFieldData::getByName('Industries');
            $typeFieldData     = CustomFieldData::getByName('AccountTypes');
            $sourceFieldData   = CustomFieldData::getByName('LeadSources');
            $meetingFieldData  = CustomFieldData::getByName('MeetingCategories');
            $stageFieldData    = CustomFieldData::getByName('SalesStages');
            $titleFieldData    = CustomFieldData::getByName('Titles');

            $industryFieldData = unserialize($industryFieldData->serializedData);
            $typeFieldData     = unserialize($typeFieldData->serializedData);
            $sourceFieldData   = unserialize($sourceFieldData->serializedData);
            $meetingFieldData  = unserialize($meetingFieldData->serializedData);
            $stageFieldData    = unserialize($stageFieldData->serializedData);
            $titleFieldData    = unserialize($titleFieldData->serializedData);

            $status = ApiResponse::STATUS_SUCCESS;
            $data = array(
                'Industries'        => $industryFieldData,
                'AccountTypes'      => $typeFieldData,
                'LeadSources'       => $sourceFieldData,
                'MeetingCategories' => $meetingFieldData,
                'SalesStages'       => $stageFieldData,
                'Titles'            => $titleFieldData,
            );

            if(Yii::app()->apiRequest->getRequestType() == ApiRequest::REST)
            {
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                                                $status,
                                                $data,
                                                null);
            }
            else
            {
                //error
            }
        }

        public function actionCustomData()
        {
            $model = $_GET['model'];

            $customFieldData = CustomFieldData::getByName($model);
            $customFieldData    = unserialize($customFieldData->serializedData);

            if(count($customFieldData) > 0)
            {
                $status = ApiResponse::STATUS_SUCCESS;
                $data = $customFieldData;
            }
            else
            {
                $status = ApiResponse::STATUS_FAILURE;
                $data = null;
            }

            if(Yii::app()->apiRequest->getRequestType() == ApiRequest::REST)
            {
                ApiRestResponse::generateOutput(Yii::app()->apiRequest->getParamsFormat(),
                $status,
                $data,
                null);
            }
            else
            {
                //error
            }
        }
    }
?>

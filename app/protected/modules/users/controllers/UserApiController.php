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
    * Users API Controller
    */
    class UsersUserApiController extends ZurmoModuleApiController
    {
        protected static function getSearchFormClassName()
        {
            return 'UsersSearchForm';
        }

        /**
         * Create new model, and send response
         * @throws ApiException
         */
        public function actionCreate()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if (!isset($params['data']))
            {
                $message = Yii::t('Default', 'Please provide data.');
                throw new ApiException($message);
            }

            // We have to encrypt password
            if (isset($params['data']['password']) && $params['data']['password'] != '')
            {
                $params['data']['hash'] = User::encryptPassword($params['data']['password']);
            }
            unset($params['data']['password']);

            $result    =  $this->processCreate($params['data']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Update model and send response
         * @throws ApiException
         */
        public function actionUpdate()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if (!isset($params['id']))
            {
                $message = Yii::t('Default', 'The ID specified was invalid.');
                throw new ApiException($message);
            }

            // We have to encrypt password
            if (isset($params['data']['password']) && $params['data']['password'] != '')
            {
                $params['data']['hash'] = User::encryptPassword($params['data']['password']);
            }
            unset($params['data']['password']);

            $result    =  $this->processUpdate((int)$params['id'], $params['data']);
            Yii::app()->apiHelper->sendResponse($result);
        }
    }
?>

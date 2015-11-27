<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2015 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2015. All rights reserved".
     ********************************************************************************/

    /**
    * Contact State API Controller
    */
    class ContactsContactStateApiController extends ZurmoModuleApiController
    {
        protected function getModelName()
        {
            return 'ContactState';
        }

        protected static function getSearchFormClassName()
        {
            return 'ContactStateSearchForm';
        }

        /**
        * We cant use Module::getStateMetadataAdapterClassName() because that references
        * to Contact model and we are using ContactState model.
        */
        public function getStateMetadataAdapterClassName()
        {
            return null;
        }

        public function actionCreate()
        {
            throw new ApiUnsupportedException();
        }

        public function actionUpdate()
        {
            throw new ApiUnsupportedException();
        }

        public function actionDelete()
        {
            throw new ApiUnsupportedException();
        }

         public function actionListContactStates()
        {
            $this->sendStatesByLeadOrContact('contact');
        }

        public function actionListLeadStates()
        {
            $this->sendStatesByLeadOrContact('lead');
        }

        /**
         * Get states by type.
         * @param string $state
         * @throws ApiException
         */
        protected function sendStatesByLeadOrContact($state = 'contact')
        {
            try
            {
                $states = array();
                if ($state =='contact')
                {
                    $states = ContactsUtil::getContactStateDataFromStartingStateLabelByLanguage(Yii::app()->language);
                }
                elseif ($state == 'lead')
                {
                    $states = LeadsUtil::getLeadStateDataFromStartingStateLabelByLanguage(Yii::app()->language);
                }
                foreach ($states as $model)
                {
                    $data['items'][] = static::getModelToApiDataUtilData($model);
                }

                $data['totalCount'] = count($data['items']);
                $data['currentPage'] = 1;
                $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                Yii::app()->apiHelper->sendResponse($result);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
        }
    }
?>
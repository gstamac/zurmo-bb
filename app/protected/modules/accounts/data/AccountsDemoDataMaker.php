<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Class that builds demo accounts.
     */
    class AccountsDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        public static function getDependencies()
        {
            return array('users');
        }

        /**
         * @param DemoDataHelper $demoDataHelper
         */
        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("User")');

            $accounts = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $account = new Account();
                $account->owner = $demoDataHelper->getRandomByModelName('User');
                $this->populateModel($account);
                $saved = $account->save();
                assert('$saved');
                $accounts[] = $account->id;
            }
            $demoDataHelper->setRangeByModelName('Account', $accounts[0], $accounts[count($accounts)-1]);
        }

        /**
         * @param RedBeanModel $model
         */
        public function populateModel(& $model)
        {
            assert('$model instanceof Account');
            parent::populateModel($model);
            $accountRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('AccountsModule', 'Account');
            $name = RandomDataUtil::getRandomValueFromArray($accountRandomData['names']);

            $domainName = static::makeDomainByName(strval($model));
            $type       = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('AccountTypes'));
            $industry   = RandomDataUtil::getRandomValueFromArray(static::getCustomFieldDataByName('Industries'));

            $model->name            = $name;
            $model->website         = static::makeUrlByDomainName($domainName);
            $model->type->value     = $type;
            $model->industry->value = $industry;
            $model->officePhone     = RandomDataUtil::makeRandomPhoneNumber();
            $model->officeFax       = RandomDataUtil::makeRandomPhoneNumber();
            $model->primaryEmail    = static::makeEmailAddressByAccount($model);
            $model->billingAddress  = ZurmoRandomDataUtil::makeRandomAddress();
            $model->employees       = mt_rand(1, 95) * 10;
            $model->annualRevenue   = mt_rand(1, 780) * 1000000;
        }

        protected static function makeEmailAddressByAccount(& $model)
        {
            assert('$model instanceof Account');
            $email = new Email();
            $email->emailAddress = 'info@' . static::makeDomainByName(strval($model));
            $email->optOut       = false;
            return $email;
        }
    }
?>
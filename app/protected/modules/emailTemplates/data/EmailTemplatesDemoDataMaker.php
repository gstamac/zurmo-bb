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
     * Class that builds demo emailTemplates.
     */
    class EmailTemplatesDemoDataMaker extends MarketingDemoDataMaker
    {
        protected $index;

        protected $seedData;

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

            $emailTemplates = array();
            $types          = array_keys(EmailTemplate::getTypeDropDownArray());
            for ($this->index = 0; $this->index < 7; $this->index++)
            {
                $emailTemplate              = new EmailTemplate();
                $emailTemplate->type        = $types[$this->index % 2];
                $emailTemplate->owner       = $demoDataHelper->getRandomByModelName('User');;
                $this->populateModel($emailTemplate);
                $emailTemplate->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME), Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
                $saved                      = $emailTemplate->save();
                assert('$saved');
                $emailTemplate = EmailTemplate::getById($emailTemplate->id);
                ReadPermissionsOptimizationUtil::
                    securableItemGivenPermissionsForGroup($emailTemplate, Group::getByName(Group::EVERYONE_GROUP_NAME));
                $emailTemplate->save();
                $emailTemplates[]           = $emailTemplate->id;
            }
            $demoDataHelper->setRangeByModelName('EmailTemplate', $emailTemplates[0], $emailTemplates[count($emailTemplates)-1]);
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof EmailTemplate');
            parent::populateModel($model);
            if (empty($this->seedData))
            {
                $this->seedData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('EmailTemplatesModule',
                                                                                            'EmailTemplate');
            }
            $modelClassName             = 'Contact';
            if ($model->isWorkflowTemplate())
            {
                $modelClassName         = $this->seedData['modelClassName'][$this->index];
            }
            $model->modelClassName      = $modelClassName;
            $model->name                = $this->seedData['name'][$this->index];
            $model->subject             = $this->seedData['subject'][$this->index];
            $model->language            = $this->seedData['language'][0];
            if (isset($this->seedData['language'][$this->index]))
            {
                $model->language            = $this->seedData['language'][$this->index];
            }
            $model->textContent         = str_replace('Zurmo', Yii::app()->label, $this->seedData['textContent'][$this->index % 2]);
            $model->htmlContent         = str_replace('Zurmo', Yii::app()->label, $this->seedData['htmlContent'][$this->index % 2]);
            $model->builtType           = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $model->isDraft             = false;
            $this->populateMarketingModelWithFiles($model);
        }
    }
?>
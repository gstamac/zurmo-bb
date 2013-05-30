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

    class ContactWebFormsModule extends SecurableModule
    {
        const RIGHT_CREATE_CONTACT_WEB_FORMS = 'Create Contact Web Forms';

        const RIGHT_DELETE_CONTACT_WEB_FORMS = 'Delete Contact Web Forms';

        const RIGHT_ACCESS_CONTACT_WEB_FORMS = 'Access Contact Web Forms Tab';

        public function getDependencies()
        {
            return array(
                'contacts',
            );
        }

        public function getRootModelNames()
        {
            return array('ContactWebForm', 'ContactWebFormEntry');
        }

        public static function getTranslatedRightsLabels()
        {
            //todo: fix this to be reflecting the name of 'contacts' i think.
            $params                                       = LabelUtil::getTranslationParamsForAllModules();
            $labels                                       = array();
            $labels[self::RIGHT_CREATE_CONTACT_WEB_FORMS] = Zurmo::t('ContactWebFormsModule', 'Create ContactWebFormsModulePluralLabel',     $params);
            $labels[self::RIGHT_DELETE_CONTACT_WEB_FORMS] = Zurmo::t('ContactWebFormsModule', 'Delete ContactWebFormsModulePluralLabel',     $params);
            $labels[self::RIGHT_ACCESS_CONTACT_WEB_FORMS] = Zurmo::t('ContactWebFormsModule', 'Access ContactWebFormsModulePluralLabel Tab', $params);
            return $labels;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'designerMenuItems' => array(),
                'globalSearchAttributeNames' => array('name'),
                'adminTabMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('ContactWebFormsModule', 'Web Forms')",
                        'url'   => array('/contactWebForms/default'),
                        'right' => self::RIGHT_ACCESS_CONTACT_WEB_FORMS,
                    ),
                ),
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ContactWebFormsModule', 'Web Forms')",
                        'descriptionLabel' => "eval:Zurmo::t('ContactWebFormsModule', 'Manage Web Forms')",
                        'route'            => '/contactWebForms/default',
                        'right'            => self::RIGHT_CREATE_CONTACT_WEB_FORMS,
                    ),
                )
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'ContactWebForm';
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_CONTACT_WEB_FORMS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_CONTACT_WEB_FORMS;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_CONTACT_WEB_FORMS;
        }

        public static function getGlobalSearchFormClassName()
        {
            return 'ContactWebFormsSearchForm';
        }

        public static function hasPermissions()
        {
            return true;
        }

        public static function isReportable()
        {
            return true;
        }

        public static function canHaveWorkflow()
        {
            return true;
        }

        public static function modelsAreNeverGloballySearched()
        {
            return true;
        }
    }
?>
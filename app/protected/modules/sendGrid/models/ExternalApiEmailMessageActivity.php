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
     * Model for external api email message activity.
     */
    class ExternalApiEmailMessageActivity extends Item
    {
        public static function getModuleClassName()
        {
            return 'SendGridModule';
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'api',
                    'type',
                    'datetime',
                    'reason',
                    'itemClass'
                ),
                'relations' => array(
                    'emailMessageActivity' => array(static::HAS_ONE, 'EmailMessageActivity')
                ),
                'rules'     => array(
                                  array('api',                 'required'),
                                  array('type',                'required'),
                                  array('type',                'type', 'type' => 'integer'),
                                  array('datetime',            'type', 'type' => 'datetime'),
                                  array('reason',              'type', 'type' => 'string'),
                                  array('itemClass',           'type', 'type' => 'string'),
                                  array('api',                 'type', 'type' => 'string'),
                )
            );
            return $metadata;
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('SendGridModule', 'External Api Email Message Activity', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('SendGridModule', 'External Api Email Message Activity', array(), null, $language);
        }

        /**
         * Before saving the model.
         * @return boolean
         */
        public function beforeSave()
        {
            if (parent::beforeSave())
            {
                $this->datetime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                return true;
            }
            return false;
        }

        /**
         * Get by type and email message activity
         * @param int $type
         * @param EmailMessageActivity $itemActivity
         * @return int
         */
        public static function getCountByTypeAndEmailMessageActivity($type, $itemActivity)
        {
            $modelClassName = get_class($itemActivity);
            $tableName      = $modelClassName::getTableName();
            $rows           = ZurmoRedBean::getAll('select emailmessageactivity_id from ' . $tableName .
                                    ' where id = ?', array($itemActivity->id));
            $searchAttributeData = array();
            $searchAttributeData['clauses'][1] = array(
                'attributeName'             => 'type',
                'operatorType'              => 'equals',
                'value'                     => $type,
            );
            $structure = '1';
            $clauseNumber = count($searchAttributeData['clauses']) + 1;
            $searchAttributeData['clauses'][$clauseNumber] = array(
                    'attributeName'             => 'emailMessageActivity',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => intval($rows[0]['emailmessageactivity_id']),
            );
            $structure .= ' and ' . $clauseNumber;
            $searchAttributeData['structure'] = "({$structure})";
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getCount($joinTablesAdapter, $where, get_called_class(), false);
        }
    }
?>

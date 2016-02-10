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
     * Helper class for working with beans from a temporary table used by the import module.
     */
    class ImportRowDataResultsUtil
    {
        /**
         * Import status of the row for when a model was updated successfully.
         * @var integer
         */
        const UPDATED = 1;

        /**
         *
         * Import status of the row for when a model was created successfully.
         * @var integer
         */
        const CREATED = 2;

        /**
         * Import status of the row for when an error occurred and the row data could not be imported.
         * @var integer
         */
        const ERROR   = 3;

        /**
         * Identifier for a row of data.
         * @var integer
         */
        private $id;

        /**
         * Messages generated by importing a row.
         * @var unknown_type
         */
        private $messages;

        /**
         * The resulting status from importing the row.  The row can be a new created model, updating an existing model
         * or there is some error that is generated trying to import the row.
         * @var integer or null
         */
        private $status;

        public static function getStatusLabelByType($type)
        {
            assert('is_int($type)');
            if ($type == self::UPDATED)
            {
                $label = Zurmo::t('ImportModule', 'Updated');
            }
            elseif ($type == self::CREATED)
            {
                $label = Zurmo::t('Core', 'Created');
            }
            elseif ($type == self::ERROR)
            {
                $label = Zurmo::t('Core', 'Skipped');
            }
            return $label;
        }

        public static function getStatusLabelAndVisualIdentifierContentByType($type)
        {
            assert('is_int($type)');
            $label = static::getStatusLabelByType($type);
            if ($type == self::UPDATED)
            {
                $stageContent = ' stage-true';
            }
            elseif ($type == self::CREATED)
            {
                $stageContent = ' stage-true';
            }
            elseif ($type == self::ERROR)
            {
                $stageContent = ' stage-false';
            }
            $content = ZurmoHtml::tag('div', array('class' => "import-item-stage-status" . $stageContent),
                '<i>●</i>' . ZurmoHtml::tag('span', array(), $label));
            return ZurmoHtml::wrapAndRenderContinuumButtonContent($content);
        }

        /**
         * Given an identifier of the row, set this identifier as the id.
         * @param integer $id
         */
        public function __construct($id)
        {
            assert('is_int($id)');
            $this->id = $id;
        }

        /**
         * @return The row identifier
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Given a message, add it to the messages collection.
         * @param string $message
         */
        public function addMessage($message)
        {
            assert('is_string($message)');
            $this->messages[] = $message;
        }

        /**
         * Set an array of messages for a row.
         * @param array $messages
         */
        public function addMessages($messages)
        {
            foreach ($messages as $message)
            {
                $this->addMessage($message);
            }
        }

        /**
         * @return An array of messages.
         */
        public function getMessages()
        {
            return $this->messages;
        }

        /**
         *
         * Sets the status to created, which should be used when a row was successfully updated into an existing model.
         */
        public function setStatusToUpdated()
        {
            $this->status = self::UPDATED;
        }

        /**
         * Sets the status to created, which should be used when a row was successfully made into a new model.
         */
        public function setStatusToCreated()
        {
            $this->status = self::CREATED;
        }

        /**
         *
         * Sets the status to created, which should be used when a row had an error when trying to either update or
         * create a model.
         */
        public function setStatusToError()
        {
            $this->status = self::ERROR;
        }

        /**
         * @return status.
         */
        public function getStatus()
        {
            return $this->status;
        }

        /**
         * @param string $tableName
         * @return int
         */
        public static function getCreatedCount($tableName)
        {
            return ImportDatabaseUtil::getCount($tableName, "status = " . ImportRowDataResultsUtil::CREATED);
        }

        /**
         * @param string $tableName
         * @return int
         */
        public static function getUpdatedCount($tableName)
        {
            return ImportDatabaseUtil::getCount($tableName, "status = " . ImportRowDataResultsUtil::UPDATED);
        }

        /**
         * @param string $tableName
         * @return int
         */
        public static function getErrorCount($tableName)
        {
            return ImportDatabaseUtil::getCount($tableName, "status = " . ImportRowDataResultsUtil::ERROR);
        }
    }
?>
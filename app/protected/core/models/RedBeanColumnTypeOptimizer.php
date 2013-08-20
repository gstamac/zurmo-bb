<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    // TODO: @Shoaibi/@Jason: Critical: No longer used?
    class RedBeanColumnTypeOptimizer
    {
        public static $optimizedTableColumns;

       /**
        * Optimize table column types, based on hints
        * @param string  $table   name of the table
        * @param string  $columnName name of the column
        * @param string  $datatype
        */
        public static function optimize($table, $columnName, $datatype, $length = null)
        {
            try
            {
                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType($datatype, $length);

                if (isset(self::$optimizedTableColumns[$table]))
                {
                    $fields = self::$optimizedTableColumns[$table];
                    // It is possible that field is created outside optimizer, so in this case reload fields from database
                    if (!in_array($columnName, array_keys($fields)))
                    {
                        $fields = ZurmoRedBean::$writer->getColumns($table);
                    }
                }
                else
                {
                    $fields = ZurmoRedBean::$writer->getColumns($table);
                }

                if (in_array($columnName, array_keys($fields)))
                {
                    $columnType = $fields[$columnName];
                    if (strtolower($columnType) != strtolower($databaseColumnType))
                    {
                        if (strtolower($datatype) == 'string' && isset($length) && $length > 0)
                        {
                            $maxLength = ZurmoRedBean::getCell("SELECT MAX(LENGTH($columnName)) FROM $table");
                            if ($maxLength <= $length)
                            {
                                ZurmoRedBean::exec("alter table {$table} change {$columnName} {$columnName} " . $databaseColumnType);
                            }
                        }
                        else
                        {
                            ZurmoRedBean::exec("alter table {$table} change {$columnName} {$columnName} " . $databaseColumnType);
                        }
                    }
                }
                else
                {
                    ZurmoRedBean::exec("alter table {$table} add {$columnName} " . $databaseColumnType);
                }
            }
            catch (RedBean_Exception_SQL $e)
            {
                //42S02 - Table does not exist.
                if (!in_array($e->getSQLState(), array('42S02')))
                {
                    throw $e;
                }
                else
                {
                    ZurmoRedBean::$writer->createTable($table);
                    ZurmoRedBean::exec("alter table {$table} add {$columnName} " . $databaseColumnType);
                }
            }

            if (isset($fields))
            {
                self::$optimizedTableColumns[$table] = $fields;
            }
            else
            {
                self::$optimizedTableColumns[$table] = ZurmoRedBean::$writer->getColumns($table);
            }
            self::$optimizedTableColumns[$table][$columnName] = $databaseColumnType;
        }

        /**
         * Optimize fields that will accept ids from external sources - for example during imports
         * @param string $table
         * @param string $columnName
         */
        public static function externalIdColumn($table, $columnName)
        {
            self::optimize($table, $columnName, 'string');
            // To-Do: In future we can use $length to limit length of varchar type
        }
    }
?>
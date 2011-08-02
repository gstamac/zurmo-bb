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
     * Helper class for working with import data tables.
     */
    class ImportDatabaseUtil
    {
        /**
         * Given a file resource, convert the file into a database table based on the table name provided.
         * Assumes the file is a csv.
         * @param resource $fileHandle
         * @param string $tableName
         * @return true on success.
         */
        public static function makeDatabaseTableByFileHandleAndTableName($fileHandle, $tableName)
        {
            assert('gettype($fileHandle) == "resource"');
            assert('is_string($tableName)');
            assert('$tableName == strtolower($tableName)');
            $freezeWhenComplete = false;
            if(RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freezeWhenComplete = true;
            }
            R::exec("drop table if exists $tableName");
            while (($data = fgetcsv($fileHandle, 0, ',')) !== false)
            {
                if(count($data) > 0)
                {
                    $newBean = R::dispense($tableName);
                    foreach($data as $columnId => $value)
                    {
                        $columnName = 'column_' . $columnId;
                        $newBean->{$columnName} = $value;
                    }
                    R::store($newBean);
                    unset($newBean);
                }
            }
            if($freezeWhenComplete)
            {
                RedBeanDatabase::freeze();
            }
            return true;
        }

        /**
         * Drops a table by the given table name.
         * @param string $tableName
         */
        public static function dropTableByTableName($tableName)
        {
            assert('$tableName == strtolower($tableName)');
            R::exec("drop table if exists $tableName");
        }

        /**
         * Gets the count of how many columns there are in a table minus the initial 'id' column.
         * @param string $tableName
         * @return integer
         */
        public static function getColumnCountByTableName($tableName)
        {
            assert('is_string($tableName)');
            $firstRowData = self::getFirstRowByTableName($tableName);
            return count($firstRowData) - 1;
        }

        /**
         * Get the first row of a table.  if no rows exist, an NoRowsInTableException is thrown.
         * @param string $tableName
         */
        public static function getFirstRowByTableName($tableName)
        {
            assert('is_string($tableName)');
            $sql = 'select * from ' . $tableName;
            try
            {
                $data = R::getRow($sql);
            }
            catch(RedBean_Exception_SQL $e)
            {
                throw new NoRowsInTableException();
            }
            return $data;
        }

        /**
         * Given a table name, count, and offset get an array of rows.
         * @param string $tableName
         * @param integer $count
         * @param integer $offset
         * @return array
         */
        public static function getRowsByTableNameAndCount($tableName, $count, $offset = null)
        {
            assert('is_string($tableName)');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            $sql = 'select * from ' . $tableName . ' limit ' . $count;
            if ($offset !== null)
            {
                $sql .= " offset $offset";
            }
            $data = R::getAll($sql);
            return $data;
        }

        /**
         * Get the row count in a given table.
         * @param string $tableName
         * @return integer
         */
        public static function getCount($tableName)
        {
            $sql = 'select count(*) count from ' . $tableName;
            $count = R::getCell($sql);
            if ($count === null)
            {
                $count = 0;
            }
            return $count;
        }
    }
?>
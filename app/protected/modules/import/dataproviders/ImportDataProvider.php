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
     * A data provider that manages import data during the import process.  The data provider will retrieve data
     * from the temporary import table that is created when a csv is uploaded.
     */
    class ImportDataProvider extends AnalyzerSupportedDataProvider
    {
        private $tableName;

        private $excludeFirstRow;

        public function __construct($tableName, $excludeFirstRow = false, array $config = array())
        {
            assert('is_string($tableName) && $tableName != ""');
            assert('is_bool($excludeFirstRow)');
            $this->tableName       = $tableName;
            $this->excludeFirstRow = $excludeFirstRow;
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
        }

        /**
         * See the yii documentation.
         */
        protected function fetchData()
        {
            $pagination = $this->getPagination();
            if (isset($pagination))
            {
                $pagination->setItemCount($this->getTotalItemCount());
                $offset = $pagination->getOffset();
                $limit  = $pagination->getLimit();
            }
            else
            {
                $offset = 0;
                $limit  = null;
            }
            $where = null;
            if($this->excludeFirstRow)
            {
                $where = 'id != 1';
            }
            return ImportDatabaseUtil::getSubset($this->tableName, $where, $limit, $offset);
        }

        /**
         * See the yii documentation. This function is made public for unit testing.
         */
        public function calculateTotalItemCount()
        {
            $where = null;
            if($this->excludeFirstRow)
            {
                $where = 'id != 1';
            }
            return ImportDatabaseUtil::getCount($this->tableName, $where);
        }

        /**
         * See the yii documentation.
         */
        protected function fetchKeys()
        {
            $keys = array();
            foreach ($this->getData() as $row)
            {
                $keys[] = $row['id'];
            }
            return $keys;
        }

        public function getCountByWhere($where)
        {
            assert('$where != null');
            if($this->excludeFirstRow)
            {
                $where .= ' and id != 1';
            }
            return ImportDatabaseUtil::getCount($this->tableName, $where);
        }

        public function getCountDataByGroupByColumnName($groupbyColumnName, $where = null)
        {
            assert(is_string($groupbyColumnName));
            assert('is_string($where) || $where == null');
            $sql = "select count(*) count, {$groupbyColumnName} from {$this->tableName} ";
            if($this->excludeFirstRow)
            {
                if($where != null)
                {
                    $where .= 'and ';
                }
                $where .= 'id != 1';
            }
            if($where != null)
            {
                $sql .= 'where ' . $where . ' ';
            }
            $sql .= 'group by ' . $groupbyColumnName;
            return R::getAll($sql);
        }
    }
?>

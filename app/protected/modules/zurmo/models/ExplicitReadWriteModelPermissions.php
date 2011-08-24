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
     * Class helps interaction between the user interface, forms, and controllers that are involved in setting
     * the explicit permissions on a model.  This class merges permission concepts together to form easier to
     * understand structures for the user interface.  Currently this only supports either readOnly or readWrite
     * permission combinations against a model for a user or group.
     * @see ExplicitReadWriteModelPermissionsElement
     * @see ExplicitReadWriteModelPermissionsUtil
     */
    class ExplicitReadWriteModelPermissions
    {
        /**
         * Array of permitable objects that will be explicity set to read only.
         * @var array
         */
        protected $readOnlyPermitables  = array();

        /**
         * Array of permitable objects that will be explicity set to read and write.
         * @var array
         */
        protected $readWritePermitables = array();

        /**
         * Add a permitable object to the read only array.
         * @param object $permitable
         */
        public function addReadOnlyPermitable($permitable)
        {
            assert('$permitable instanceof Permitable');
            if(!isset($this->readOnlyPermitables[$permitable->id]))
            {
                $this->readOnlyPermitables[$permitable->id] = $permitable;
            }
            else
            {
                throw notSupportedException();
            }
        }

        /**
         * Add a permitable object to the read write array.
         * @param object $permitable
         */
        public function addReadWritePermitable($permitable)
        {
            assert('$permitable instanceof Permitable');
            if(!isset($this->readWritePermitables[$permitable->id]))
            {
                $this->readWritePermitables[$permitable->id] = $permitable;
            }
            else
            {
                throw notSupportedException();
            }
        }

        /**
         * @return integer count of read only permitables
         */
        public function getReadOnlyPermitablesCount()
        {
            return count($this->readOnlyPermitables);
        }

        /**
         * @return integer count of read/write permitables
         */
        public function getReadWritePermitablesCount()
        {
            return count($this->readWritePermitables);
        }

        /**
         * @return array of read only permitables
         */
        public function getReadOnlyPermitables()
        {
            return $this->readOnlyPermitables;
        }

        /**
         * @return array of read/write permitables
         */
        public function getReadWritePermitables()
        {
            return $this->readWritePermitables;
        }
    }
?>
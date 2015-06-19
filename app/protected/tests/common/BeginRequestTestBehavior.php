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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class BeginRequestTestBehavior extends BeginRequestBehavior
    {
        public function attach($owner)
        {
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleApplicationCache'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleImports'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadWorkflowsObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadReadPermissionSubscriptionObserver'));
        }

        /**
         * Import all files that need to be included(for lazy loading)
         * @param $event
         */
        public function handleImports($event)
        {
            try
            {
                // we don't ue $default here as the computation of default on each request would take more time.
                $filesToInclude = GeneralCache::getEntry('filesToIncludeForTests');
            }
            catch (NotFoundException $e)
            {
                $filesToInclude   = FileUtil::getFilesFromDir(Yii::app()->basePath . '/modules', Yii::app()->basePath . '/modules', 'application.modules', true);
                $filesToIncludeFromFramework = FileUtil::getFilesFromDir(Yii::app()->basePath . '/core', Yii::app()->basePath . '/core', 'application.core', true);
                $totalFilesToIncludeFromModules = count($filesToInclude);

                foreach ($filesToIncludeFromFramework as $key => $file)
                {
                    $filesToInclude[$totalFilesToIncludeFromModules + $key] = $file;
                }
                GeneralCache::cacheEntry('filesToIncludeForTests', $filesToInclude);
            }
            foreach ($filesToInclude as $file)
            {
                Yii::import($file);
            }
        }

        public function handleLoadReadPermissionSubscriptionObserver($event)
        {
            parent::handleLoadReadPermissionSubscriptionObserver($event);
            Yii::app()->readPermissionSubscriptionObserver->enabled = false;
        }
    }
?>
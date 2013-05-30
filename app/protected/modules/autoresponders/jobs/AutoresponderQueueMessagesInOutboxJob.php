<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * A job for processing autoresponder messages that are not sent immediately when triggered
     */

    class AutoresponderQueueMessagesInOutboxJob extends AutoresponderOrCampaignBaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('AutorespondersModule', 'Process autoresponder messages');
        }

        /**
         * @see BaseJob::run()
         */
        public function run()
        {
            // TODO: @Shoaibi: Critical: Add tests that return only one item.
            $batchSize = $this->resolveBatchSize();
            $autoresponderItemsToProcess    = AutoresponderItem::getByProcessedAndProcessDateTime(
                                                                                        0,
                                                                                        time(),
                                                                                        $batchSize);
            if (!is_array($autoresponderItemsToProcess))
            {
                $autoresponderItemsToProcess    = array($autoresponderItemsToProcess);
            }
            foreach ($autoresponderItemsToProcess as $autoresponderItem)
            {
                try
                {
                    $this->processAutoresponderItemInQueue($autoresponderItem);
                }
                catch (NotFoundException $e)
                {
                    return $autoresponderItem->delete();
                }
                catch (NotSupportedException $e)
                {
                    $this->errorMessage = $e->getMessage();
                    return false;
                }
            }
            return true;
        }

        protected function processAutoresponderItemInQueue(AutoresponderItem $autoresponderItem)
        {
            AutoresponderItemsUtil::processDueItem($autoresponderItem);
        }
    }
?>
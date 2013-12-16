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

    /**
     * A job for processing campaign messages that are not sent immediately when triggered
     */
    class CampaignQueueMessagesInOutboxJob extends AutoresponderOrCampaignBaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('CampaignsModule', 'Process campaign messages');
        }

        /**
         * @see BaseJob::run()
         */
        public function run()
        {
            $processed = $this->processRun();
            $this->forgetModelsWithForgottenValidators();
            $this->modelIdentifiersForForgottenValidators = array();
            return $processed;
        }

        protected function processRun()
        {
            $batchSize = $this->resolveBatchSize();
            $campaignItemsToProcess    = CampaignItem::getByProcessedAndStatusAndSendOnDateTime(
                                                                                        0,
                                                                                        Campaign::STATUS_PROCESSING,
                                                                                        time(),
                                                                                        $batchSize);
            $startingMemoryUsage = memory_get_usage();
            $modelsProcessedCount = 0;
            foreach ($campaignItemsToProcess as $campaignItem)
            {
                try
                {
                    $this->processCampaignItemInQueue($campaignItem);
                }
                catch (NotFoundException $e)
                {
                    return $campaignItem->delete();
                }
                catch (NotSupportedException $e)
                {
                    $this->errorMessage = $e->getMessage();
                    return false;
                }
                $this->runGarbageCollection($campaignItem);
                $modelsProcessedCount++;
            }
            $this->addMaxmimumProcessingCountMessage($modelsProcessedCount, $startingMemoryUsage);
            return true;
        }

        protected function processCampaignItemInQueue(CampaignItem $campaignItem)
        {
            CampaignItemsUtil::processDueItem($campaignItem);
        }

        /**
         * Not pretty, but gets the job done. Solves memory leak problem.
         * @param CampaignItem $campaignItem
         */
        protected function runGarbageCollection($campaignItem)
        {
            assert('$campaignItem instanceof CampaignItem');
            $campaignItem->campaign->marketingList->forgetValidators();
            $campaignItem->campaign->forgetValidators();
            $this->modelIdentifiersForForgottenValidators[$campaignItem->campaign->marketingList->getModelIdentifier()] = true;
            $this->modelIdentifiersForForgottenValidators[$campaignItem->campaign->getModelIdentifier()] = true;
            parent::runGarbageCollection($campaignItem);
        }
    }
?>
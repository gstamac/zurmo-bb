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
     * Helper class for working with marketing list.
     */
    class MarketingListsUtil
    {
        /**
         * How many items of each type per one request - this is done for performance reasons
         * @var int
         */
        public static $pageSize = 50;

        /**
         * @param $resolveSubscribersForm
         * @param $campaign
         * @return A
         * @throws Exception
         * @throws NotFoundException
         * @throws NotSupportedException
         */
        public static function resolveAndSaveMarketingList($resolveSubscribersForm, $campaign)
        {
            if ($campaign->status != Campaign::STATUS_COMPLETED)
            {
                $message = Zurmo::t('MarketingListsModule', 'You can not retarget uncompleted campaigns!');
                throw new NotSupportedException($message);
            }

            // First check if user selected existing marketing list, if he didn't create new marketing list
            try
            {
                $marketingList = MarketingList::getById(intval($resolveSubscribersForm->marketingList['id']));
            }
            catch (NotFoundException $e)
            {
                if ($resolveSubscribersForm->newMarketingListName != '')
                {
                    $marketingList = new MarketingList();
                    $marketingList->name = $resolveSubscribersForm->newMarketingListName;
                    $marketingList->save();
                }
                else
                {
                    $message = Zurmo::t('MarketingListsModule', 'Invalid or not selected marketing list entered. Please go back and select marketing list!');
                    throw new NotFoundException($message);
                }
            }

            $offset = 0;
            $pageSize = static::$pageSize;
            do
            {
                $newMarketingListContacts = array();
                if ($resolveSubscribersForm->retargetOpenedEmailRecipients)
                {
                    $campaignItemOpenActivities = CampaignItemActivity::getByTypeAndCampaign(CampaignItemActivity::TYPE_OPEN, $campaign, $offset, $pageSize);
                    foreach ($campaignItemOpenActivities as $campaignItemActivity)
                    {
                        $newMarketingListContacts[] = $campaignItemActivity->campaignItem->contact;
                    }
                }
                if ($resolveSubscribersForm->retargetClickedEmailRecipients)
                {
                    $campaignItemClickActivities = CampaignItemActivity::getByTypeAndCampaign(CampaignItemActivity::TYPE_CLICK, $campaign, $offset, $pageSize);
                    foreach ($campaignItemClickActivities as $campaignItemActivity)
                    {
                        $newMarketingListContacts[] = $campaignItemActivity->campaignItem->contact;
                    }
                }
                if ($resolveSubscribersForm->retargetNotViewedEmailRecipients)
                {
                    $campaignItemNotViewedItems = CampaignItem::getNotViewedItems($campaign, $offset, $pageSize);

                    foreach ($campaignItemNotViewedItems as $campaignItem)
                    {
                        $newMarketingListContacts[] = $campaignItem->contact;
                    }
                }
                if ($resolveSubscribersForm->retargetNotClickedEmailRecipients)
                {
                    $campaignItemNotClickedItems = CampaignItem::getNotClickedOrUnsubscribedOrSpamItems($campaign, $offset, $pageSize);
                    foreach ($campaignItemNotClickedItems as $campaignItem)
                    {
                        $newMarketingListContacts[] = $campaignItem->contact;
                    }
                }
                foreach ($newMarketingListContacts as $marketingListContact)
                {
                    if (!MarketingListMember::getByMarketingListIdAndContactId($marketingList->id, $marketingListContact->id))
                    {
                        $marketingListMember               = new MarketingListMember();
                        $marketingListMember->unsubscribed = 0;
                        $marketingListMember->contact      = $marketingListContact;
                        $marketingList->marketingListMembers->add($marketingListMember);
                        $marketingList->save();
                    }
                }
                $offset = $offset + $pageSize;
            } while (!empty($newMarketingListContacts));
            return $marketingList;
        }

        /**
         * Generate name for new marketing list based on $campaign that user is retargeting
         * @param Campaign $campaign
         * @return string
         */
        public static function generateRandomNameForCampaignRetargetingList(Campaign $campaign)
        {
            $text = Zurmo::t('MarketingListsModule', 'Retargeting List');
            return  $campaign->name . ' - ' . $text . ' - ' . DateTimeUtil::getTodaysDate();
        }
    }
?>
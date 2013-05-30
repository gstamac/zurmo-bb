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
     * Helper class for working with emailMessageActivity
     */
    class EmailMessageActivityUtil
    {
        const IMAGE_PATH            =   '/default/images/1x1-pixel.png';

        const VALID_HASH_PATTERN    = '~^[A-Z0-9\+=/]+~i';

        protected static $baseQueryStringArray;

        public static function resolveContentForTrackingAndFooter($tracking, & $content, $modelId, $modelType, $personId,
                                                                            $marketingListId, $isHtmlContent = false)
        {
            $trackingAdded = static::resolveContentForTracking($tracking, $content, $modelId, $modelType,
                                                                                            $personId, $isHtmlContent);
            if (!$trackingAdded)
            {
                return false;
            }
            static::resolveContentForFooter($content, $personId, $marketingListId, $isHtmlContent);
            return true;
        }

        public static function resolveQueryStringArrayForHash($hash, $validateQueryStringArray = true,
                                                                                            $validateForTracking = true)
        {
            $hash = urldecode(trim($hash));
            if (static::isValidHash($hash))
            {
                $queryStringArray   = array();
                $decryptedString    = ZurmoPasswordSecurityUtil::decrypt($hash);
                if ($decryptedString)
                {
                    parse_str($decryptedString, $queryStringArray);
                    if ($validateQueryStringArray)
                    {
                        if ($validateForTracking)
                        {
                            static::validateAndResolveFullyQualifiedQueryStringArrayForTracking($queryStringArray);
                        }
                        else
                        {
                            static::validateQueryStringArrayForMarketingListPublicController($queryStringArray);
                        }
                    }
                    return $queryStringArray;
                }
            }
            throw new NotSupportedException();
        }

        public static function resolveQueryStringFromUrlAndCreateOrUpdateActivity()
        {
            // TODO: @Shoaibi: Critical: Tests
            $hash = Yii::app()->request->getQuery('id');
            if (!$hash)
            {
                throw new NotSupportedException();
            }
            $queryStringArray = static::resolveQueryStringArrayForHash($hash);
            return static::processActivityFromQueryStringArray($queryStringArray);
        }

        protected static function resolveContentForTracking($tracking, & $content, $modelId, $modelType, $personId,
                                                                                                        $isHtmlContent)
        {
            if (!$tracking)
            {
                return true;
            }
            if (strpos($content, static::resolveBaseTrackingUrl()) !== false) // it already contains few tracking  urls in the content
            {
                return false;
            }
            static::$baseQueryStringArray = static::resolveBaseQueryStringArray($modelId, $modelType, $personId);
            static::resolveContentForEmailOpenTracking($content, $isHtmlContent);
            static::resolveContentForLinkClickTracking($content, $isHtmlContent);
            return true;
        }

        protected static function processActivityFromQueryStringArray($queryStringArray)
        {
            $activityUpdated = static::createOrUpdateActivity($queryStringArray);
            if (!$activityUpdated)
            {
                throw new FailedToSaveModelException();
            }
            $trackingType = static::resolveTrackingTypeByQueryStringArray($queryStringArray);
            if ($trackingType === EmailMessageActivity::TYPE_CLICK)
            {
                return array('redirect' => true, 'url' => $queryStringArray['url']);
            }
            else
            {
                return array('redirect' => false, 'imagePath' => static::resolveFullyQualifiedImagePath());
            }
        }

        protected static function createOrUpdateActivity($queryStringArray)
        {
            $activity = static::resolveExistingActivity($queryStringArray);
            if ($activity)
            {
                $activity->quantity++;
                if (!$activity->save())
                {
                    throw new FailedToSaveModelException();
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return static::createNewActivity($queryStringArray);
            }
        }

        protected static function resolveExistingActivity($queryStringArray)
        {
            $type = static::resolveTrackingTypeByQueryStringArray($queryStringArray);
            list($modelId, $modelType, $personId, $url) = array_values($queryStringArray);
            $modelClassName = static::resolveModelClassNameByModelType($modelType);
            $activities = $modelClassName::getByTypeAndModelIdAndPersonIdAndUrl($type, $modelId, $personId, $url);
            $activitiesCount = count($activities);
            if ($activitiesCount > 1)
            {
                throw new NotSupportedException(); // we found multiple models matching our criteria, should never happen.
            }
            elseif ($activitiesCount === 1)
            {
                return $activities[0];
            }
            else
            {
                return false;
            }
        }

        protected static function createNewActivity($queryStringArray)
        {
            $type = static::resolveTrackingTypeByQueryStringArray($queryStringArray);
            list($modelId, $modelType, $personId, $url) = array_values($queryStringArray);
            $modelClassName = static::resolveModelClassNameByModelType($modelType);
            return $modelClassName::createNewActivity($type, $modelId, $personId, $url);
        }

        protected static function resolveContentForEmailOpenTracking(& $content, $isHtmlContent = false)
        {
            if (!$isHtmlContent)
            {
                return false;
            }
            $hash               = static::resolveHashForQueryStringArray(static::$baseQueryStringArray);
            $trackingUrl        = static::resolveAbsoluteTrackingUrlByHash($hash);
            $applicationName    = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            $imageTag           = ZurmoHtml::image($trackingUrl, $applicationName, array('width' => 1, 'height' => 1));
            $imageTag           = ZurmoHtml::tag('br') . $imageTag;
            if ($bodyTagPosition = strpos($content, '</body>'))
            {
                $content = substr_replace($content , $imageTag . '</body>' , $bodyTagPosition, strlen('</body>'));
            }
            else
            {
                $content .= $imageTag;
            }
            return true;
        }

        protected static function resolveContentForLinkClickTracking(& $content, $isHtmlContent = false)
        {
            static::resolvePlainLinksForClickTracking($content, $isHtmlContent);
            static::resolveHrefLinksForClickTracking($content, $isHtmlContent);
        }

        protected static function resolvePlainLinksForClickTracking(& $content, $isHtmlContent)
        {
            $spacePrefixedAndSuffixedLinkRegex = static::getPlainLinkRegex($isHtmlContent);
            $content = preg_replace_callback($spacePrefixedAndSuffixedLinkRegex,
                                                'static::resolveTrackingUrlForMatchedPlainLinkArray' ,
                                                $content);
            if ($content === null)
            {
                throw new NotSupportedException();
            }
        }

        protected static function resolveHrefLinksForClickTracking(& $content, $isHtmlContent)
        {
            if ($isHtmlContent)
            {
                $hrefPrefixedLinkRegex  = static::getHrefLinkRegex();
                $content = preg_replace_callback($hrefPrefixedLinkRegex,
                                                'static::resolveTrackingUrlForMatchedHrefLinkArray' ,
                                                $content);
                if ($content === null)
                {
                    throw new NotSupportedException();
                }
            }
        }

        protected static function resolveTrackingUrlForMatchedPlainLinkArray($matches)
        {
            $matchPosition  = strpos($matches[0], $matches[2]);
            $prefix = substr($matches[1], 0, $matchPosition);
            return $prefix . static::resolveTrackingUrlForLink(trim($matches[2])) . ' ';
        }

        protected static function resolveTrackingUrlForMatchedHrefLinkArray($matches)
        {
            $quotes         = $matches[1];
            $prefixLength   = strpos($matches[0], 'href=' . $matches[1]);
            $prefix         = substr($matches[0], 0, $prefixLength + 5);
            return $prefix . $quotes . static::resolveTrackingUrlForLink($matches[2]) . $quotes;
        }

        protected static function resolveTrackingUrlForLink($link)
        {
            $queryStringArray = static::$baseQueryStringArray;
            $queryStringArray['url'] = $link;
            $hash = static::resolveHashForQueryStringArray($queryStringArray);
            $link = static::resolveAbsoluteTrackingUrlByHash($hash);
            return $link;
        }

        protected static function resolveAbsoluteTrackingUrlByHash($hash)
        {
            return Yii::app()->createAbsoluteUrl(static::resolveBaseTrackingUrl(), array('id' => $hash));
        }

        protected static function resolveBaseTrackingUrl()
        {
            return '/tracking/default/track';
        }

        protected static function resolveHashForQueryStringArray($queryStringArray)
        {
            $queryString            = http_build_query($queryStringArray);
            $encryptedString        = ZurmoPasswordSecurityUtil::encrypt($queryString);
            if (!$encryptedString || !static::isValidHash($encryptedString))
            {
                throw new NotSupportedException();
            }
            $encryptedString        = urlencode($encryptedString);
            return $encryptedString;
        }

        protected static function resolveBaseQueryStringArray($modelId, $modelType, $personId)
        {
            return compact('modelId', 'modelType', 'personId');
        }

        protected static function getBaseLinkRegex()
        {
            $baseLinkRegex = <<<PTN
(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))
PTN;
            // (?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))
            return $baseLinkRegex;
        }

        protected static function getPlainLinkRegex($isHtmlContent)
        {
            $baseLinkRegex  = static::getBaseLinkRegex();
            // TODO: @Shoaibi: High: Change this so it matches to any link not surrounded by quotes(single or double)
            $plainLinkRegex = '(\n|\r|\s)' . $baseLinkRegex;
            if ($isHtmlContent)
            {
                $plainLinkRegex = substr($plainLinkRegex, 0, -1) . '(?!(?>[^<]*(?:<(?!/?a\b)[^<]*)*)</a>))';
            }
            $linkRegex = '%' . $plainLinkRegex . '%i';
            return $linkRegex;
        }

        protected static function getHrefLinkRegex()
        {
            $baseLinkRegex  = static::getBaseLinkRegex();
            $hrefPrefixedLinkRegex  = '<a [^>]*href=(\'|")' . $baseLinkRegex . '(\'|")';
            $linkRegex = '%' . $hrefPrefixedLinkRegex . '%i';
            return $linkRegex;
        }

        protected static function resolveTrackingTypeByQueryStringArray($queryStringArray)
        {
            if (!empty($queryStringArray['url']))
            {
                return EmailMessageActivity::TYPE_CLICK;
            }
            else
            {
                return EmailMessageActivity::TYPE_OPEN;
            }
        }

        protected static function resolveContentForFooter(& $content, $personId, $marketingListId, $isHtmlContent)
        {
            $placeholderFooterContent = static::resolveFooterPlaceholderContentByType($isHtmlContent);
            static::resolveFooterPlaceholders($content, $placeholderFooterContent, $personId,
                                                                                    $marketingListId, $isHtmlContent);
        }

        protected static function resolveFooterPlaceholders(& $content, $placeholderContent, $personId,
                                                                                    $marketingListId, $isHtmlContent)
        {
            $hash                           = static::resolveHashForFooter($personId, $marketingListId);
            $unsubscribeUrlPlaceholder      = AutoresponderOrCampaignMailFooterContentUtil::UNSUBSCRIBE_URL_PLACEHOLDER;
            $manageSubscriptionsPlaceholder = AutoresponderOrCampaignMailFooterContentUtil::
                                                                                MANAGE_SUBSCRIPTIONS_URL_PLACEHOLDER;
            $unsubscribeUrl                 = static::resolveUnsubscribeUrl($hash);
            $manageSubscriptionsUrl         = static::resolveManageSubscriptionsUrl($hash);
            $unsubscribeTranslated          = Zurmo::t('MarketingListsModule', 'Unsubscribe');
            $manageSubscriptionsTranslated  = Zurmo::t('MarketingListsModule', 'Manage Subscriptions');
            if ($isHtmlContent)
            {
                $unsubscribeUrl = ZurmoHtml::link($unsubscribeTranslated, $unsubscribeUrl);
                $manageSubscriptionsUrl =ZurmoHtml::link($manageSubscriptionsTranslated, $manageSubscriptionsUrl);
            }
            $placeholderContent = str_replace($unsubscribeUrlPlaceholder, $unsubscribeUrl, $placeholderContent);
            $placeholderContent = str_replace($manageSubscriptionsPlaceholder, $manageSubscriptionsUrl,
                                                                                                $placeholderContent);
            if ($isHtmlContent)
            {
                $placeholderContent = ZurmoHtml::tag('br') . $placeholderContent;
            }
            else
            {
                $placeholderContent = PHP_EOL . $placeholderContent;
            }
            $content            .= $placeholderContent;
        }

        protected static function resolveFooterPlaceholderContentByType($isHtmlContent)
        {
            return AutoresponderOrCampaignMailFooterContentUtil::getContentByType($isHtmlContent);
        }

        public static function resolveHashForFooter($personId, $marketingListId)
        {
            $queryStringArray       = compact('personId', 'marketingListId');
            return static::resolveHashForQueryStringArray($queryStringArray);
        }

        protected static function resolveUnsubscribeUrl($hash)
        {
            $baseUrl = static::resolveUnsubscribeBaseUrl();
            return Yii::app()->createAbsoluteUrl($baseUrl, array('hash' => $hash));
        }

        protected static function resolveManageSubscriptionsUrl($hash)
        {
            $baseUrl = static::resolveManageSubscriptionsBaseUrl();
            return Yii::app()->createAbsoluteUrl($baseUrl, array('hash' => $hash));
        }

        protected static function resolveUnsubscribeBaseUrl()
        {
            return '/marketingLists/public/unsubscribe';
        }

        protected static function resolveManageSubscriptionsBaseUrl()
        {
            return '/marketingLists/public/manageSubscriptions';
        }

        protected static function validateAndResolveFullyQualifiedQueryStringArrayForTracking(& $queryStringArray)
        {
            $rules = array(
                        'modelId' => array(
                            'required' => true,
                        ),
                        'modelType' => array(
                            'required' => true,
                        ),
                        'personId' => array(
                            'required' => true,
                        ),
                        'url'   => array(
                            'defaultValue'  => null,
                        ),
                    );
            foreach ($rules as $index => $rule)
            {
                if (!isset($queryStringArray[$index]))
                {
                    if (array_key_exists('defaultValue', $rule))
                    {
                        $queryStringArray[$index] = $rule['defaultValue'];
                    }
                    elseif (array_key_exists('required', $rule) && $rule['required'])
                    {
                        throw new NotSupportedException();
                    }
                }
            }
        }

        protected static function validateQueryStringArrayForMarketingListPublicController($queryStringArray)
        {
            if (empty($queryStringArray['personId']) || empty($queryStringArray['marketingListId']))
            {
                throw new NotSupportedException;
            }
        }

        protected static function resolveModelClassNameByModelType($modelType)
        {
            return $modelType . 'Activity';
        }

        protected static function resolveFullyQualifiedImagePath()
        {
            return Yii::app()->themeManager->basePath . static::IMAGE_PATH;
        }

        protected static function replaceSpacesWithPlusSymbol(& $hash)
        {
            // + in url often becomes space, we need to reverse that.
            $hash = str_replace(' ', '+', $hash);
        }

        protected static function isValidHash($hash)
        {
            if (empty($hash))
            {
                return false;
            }
            $matches = array();
            $matchesCount = preg_match_all(static::VALID_HASH_PATTERN, $hash, $matches);
            if (!$matchesCount || ($matches[0][0] !== $hash))
            {
                return false;
            }
            return true;
        }
    }
?>
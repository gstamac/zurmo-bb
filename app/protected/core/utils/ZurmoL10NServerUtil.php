<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     *
     * Interacts with the L10N server ( http://translate.zurmo.org )
     *
     */
    class ZurmoL10NServerUtil
    {
        /**
         * Domain of the l10n server
         */
        private static $serverDomain = 'http://translate.zurmo.org';

        /**
         * Path to the info XML file
         */
        private static $infoXmlPath = 'sites/default/files/l10n_packager/l10n_server.xml';

        /**
         * @return string
         */
        private static function getReleaseVersion()
        {
            return join('.', array(MAJOR_VERSION, MINOR_VERSION, PATCH_VERSION));
        }

        /**
         * Downloads the l10n info XML file
         *
         * @return SimpleXMLElement
         */
        protected static function getServerInfo()
        {
            $cacheIdentifier = 'l10nServerInfo';
            try {
                $l10nInfo = GeneralCache::getEntry($cacheIdentifier);
            } catch (NotFoundException $e) {
                $infoFileUrl = self::$serverDomain . '/' . self::$infoXmlPath;
                $l10nInfo = @simplexml_load_file($infoFileUrl);
                GeneralCache::cacheEntry($cacheIdentifier, $l10nInfo);
            }

            if (isset($l10nInfo->version) && $l10nInfo->version == '1.1')
            {
                return $l10nInfo;
            }

            throw new FailedServiceException();
        }

        /**
         * Retrives the list of all languages avalible on the l10n server
         */
        public static function getAvalibleLanguages()
        {
            try {
                $l10nInfo = self::getServerInfo();
            } catch (FailedServiceException $e) {
                return false;
            }

            $languages = array();
            foreach ($l10nInfo->languages->language as $language)
            {
                $languages[$language->code] = (array)$language;
            }

            if (is_array($languages) && !empty($languages))
            {
                return $languages;
            }

            throw new FailedServiceException();
        }
    }
?>
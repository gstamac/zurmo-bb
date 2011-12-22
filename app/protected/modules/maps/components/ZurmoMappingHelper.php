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

    class ZurmoMappingHelper extends MappingHelper
    {
        /**
         * @return rendered map content.
         */
        public function getMappingLinkContentForElement($mapData)
        {
            return GoogleMappingUtil::renderGeoCoderMap(self::getGeoCodeApi(),$mapData);
        }

        /**
         * @return rendered modal map view.
         */
        public static function setAjaxModeAndRenderMapModalView(CController $controller, $modalMapAddressData,
                                                 $pageTitle = null,
                                                 $stateMetadataAdapterClassName = null)
        {
            Yii::app()->getClientScript()->setToAjaxMode();
            return self::renderModalMapView($controller, $modalMapAddressData, $pageTitle,
                                               $stateMetadataAdapterClassName);
        }

        /**
         * @return rendered content from view as string.
         */
        public static function renderModalMapView(CController $controller, 
                                                  $modalMapAddressData,
                                                  $pageTitle = null,
                                                  $stateMetadataAdapterClassName = null)
        {
            $className = 'AddressGoogleMapModalView';
            $renderAndMapModalView = new $className(
                $controller->getId(),
                $controller->getModule()->getId(),
                $modalMapAddressData,
                'modal'
            );

            $view = new ModalView($controller,
                                  $renderAndMapModalView,
                                  'modalContainer',
                                  $pageTitle);
            return $view->render();
        }

        /**
         * @return modal map render url.
         */
        public static function getModalMapUrl($addressData = array())
        {
            return Yii::app()->createUrl('maps/default/renderAddressMapView/', array_merge($_GET,$addressData));
        }

        /**
         * @return lat / long array.
         */
        public static function getGeoCodes($address)
        {
            return GoogleGeoCodeUtil::getLatitudeLongitude(self::getGeoCodeApi(), $address);
        }

        /**
         * @return Geocode Api Key.
         */
        public static function getGeoCodeApi()
        {
            if (null != $apiKey = ZurmoConfigurationUtil::getByModuleName('MapsModule', 'googleMapApiKey'))
            {
                return $apiKey;
            }
            else
            {
                return '';
            }
        }
    }
?>
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
     * Application loaded component at run time. @see BeginBehavior - calls load() method.
     */
    class ZurmoWebservicexCurrencyHelper extends ZurmoCurrencyHelper
    {
        /**
         * @param $error - string by reference to attach error to if needed.
         * @return rate as a float, otherwise null if there is some sort of error
         */
        protected function getConversionRateViaWebService($fromCode, $toCode)
        {
            $url  = 'http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency=';
            $url .= $fromCode . '&ToCurrency=' . $toCode;
            $ch = curl_init();
            $timeout = 2;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
            if ($file_contents === false || empty($file_contents))
            {
                $this->webServiceErrorMessage = curl_error($ch);
                $this->webServiceErrorCode    = ZurmoCurrencyHelper::ERROR_WEB_SERVICE;
                return null;
            }
            if (!empty($file_contents) &&
                false !== $xml = @simplexml_load_string($file_contents))
            {
                if (is_object($xml) && $xml instanceof SimpleXMLElement)
                {
                    $xmlAsArray = (array)$xml;
                    return $xmlAsArray[0];
                }
                elseif (is_array($xml))
                {
                    return $xml[0];
                }
                else
                {
                    return null; //todo: throw exception
                }
            }
            if (stripos($file_contents, 'error') === false)
            {
                $this->webServiceErrorMessage = Yii::t('Default', 'Invalid currency code');
                $this->webServiceErrorCode    = ZurmoCurrencyHelper::ERROR_INVALID_CODE;
            }
            else
            {
                $this->webServiceErrorMessage = Yii::t('Default', 'There was an error with the web service.');
                $this->webServiceErrorCode    = ZurmoCurrencyHelper::ERROR_WEB_SERVICE;
            }
            return null;
        }
    }
?>
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
     * Helper class used to convert models into arrays
     */
    class ReportToExportAdapter
    {
        protected $reportResultsRowData;

        protected $report;

        public function __construct(ReportResultsRowData $reportResultsRowData, Report $report)
        {
            $this->reportResultsRowData = $reportResultsRowData;
            $this->report               = $report;
        }

        public function getData()
        {
            $data   = array();
            foreach($this->reportResultsRowData->getDisplayAttributes() as $key => $displayAttribute)
            {

                $resolvedAttributeName = $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                $className             = $this->resolveExportClassNameForListViewColumnAdapter($displayAttribute);
                $params                = array();
                $this->resolveParamsForCurrencyTypes($displayAttribute, $params);
                $adapter = new $className($this->reportResultsRowData, $resolvedAttributeName, $params);
                $adapter->resolveData($data);
            }
            return $data;
        }

        public function getHeaderData()
        {
            $data   = array();
            foreach($this->reportResultsRowData->getDisplayAttributes() as $key => $displayAttribute)
            {

                $resolvedAttributeName = $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                $className             = $this->resolveExportClassNameForListViewColumnAdapter($displayAttribute);
                $params                = array();
                $this->resolveParamsForCurrencyTypes($displayAttribute, $params);
                $adapter = new $className($this->reportResultsRowData, $resolvedAttributeName, $params);
                $adapter->resolveHeaderData($data);
            }
            return $data;
        }

        protected function resolveExportClassNameForListViewColumnAdapter(DisplayAttributeForReportForm $displayAttribute)
        {
            $displayElementType = $displayAttribute->getDisplayElementType();
            if(@class_exists($displayElementType . 'ForReportListViewColumnAdapter'))
            {
                return $displayElementType . 'ForReportToExportValueAdapter';
            }
            else
            {
                return $displayElementType . 'RedBeanModelAttributeValueToExportValueAdapter';
            }
        }

        protected function resolveParamsForCurrencyTypes(DisplayAttributeForReportForm $displayAttribute, & $params)
        {
            assert('is_array($params)');
            if($displayAttribute->isATypeOfCurrencyValue())
            {

                $params['currencyValueConversionType'] = $this->report->getCurrencyConversionType();
                $params['spotConversionCurrencyCode']  = $this->report->getSpotConversionCurrencyCode();
                $params['fromBaseToSpotRate']          = $this->report->getFromBaseToSpotRate();
            }
        }
    }
?>
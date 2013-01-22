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

    class SummationReportDataProvider extends ReportDataProvider
    {
        /**
         * Resolved to include the groupBys as query only display attributes, and mark all display attributes that are
         * also groupBys as used by the drillDown.
         * @var null | array of DisplayAttributesForReportForms
         */
        private $resolvedDisplayAttributes;

        public function __construct(Report $report, array $config = array())
        {
            parent::__construct($report, $config);
        }

        public function calculateTotalItemCount()
        {
        $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
        $sql                    = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter);
        $rows                   = R::getAll($sql);
        $count                  = count($rows);
        return $count;
        }

        public function makeReportDataProviderToAmChartMakerAdapter()
        {
            if(ChartRules::isStacked($this->report->getChart()->type))
            {
                return $this->makeStackedReportDataProviderToAmChartMakerAdapter();
            }
            else
            {
                return $this->makeNonStackedReportDataProviderToAmChartMakerAdapter();
            }
        }

        public function resolveDisplayAttributes()
        {
            if($this->resolvedDisplayAttributes != null)
            {
                return $this->resolvedDisplayAttributes;
            }
            $this->resolvedDisplayAttributes = array();
            foreach($this->report->getDisplayAttributes() as $displayAttribute)
            {
                $this->resolvedDisplayAttributes[] = $displayAttribute;
            }

            if(($this->report->getDrillDownDisplayAttributes()) > 0)
            {
                $this->resolveGroupBysThatAreNotYetDisplayAttributesAsDisplayAttributes();
            }
            return $this->resolvedDisplayAttributes;
        }

        protected function isReportValidType()
        {
            if($this->report->getType() != Report::TYPE_SUMMATION)
            {
                throw new NotSupportedException();
            }
        }

        protected function fetchChartData()
        {
            //todO: $totalItemCount = $this->getTotalItemCount(); if too many rows over 100? then we should block or limit or something not sure...
            return $this->runQueryAndGetResolveResultsData(null, null);
        }

        public function resolveFirstSeriesLabel()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstSeries)
                {
                    return $displayAttribute->label;
                }
            }
        }

        public function resolveFirstRangeLabel()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstRange)
                {
                    return $displayAttribute->label;
                }
            }
        }

        protected function resolveChartFirstSeriesAttributeNameForReportResultsRowData()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstSeries)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }

        protected function resolveChartFirstRangeAttributeNameForReportResultsRowData()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstRange)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }

        protected function resolveChartSecondSeriesAttributeNameForReportResultsRowData()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->secondSeries)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }

        protected function resolveChartSecondRangeAttributeNameForReportResultsRowData()
        {
            foreach($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->secondRange)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }

        protected function makeNonStackedReportDataProviderToAmChartMakerAdapter()
        {
            $resultsData              = $this->fetchChartData();
            $firstSeriesAttributeName = $this->resolveChartFirstSeriesAttributeNameForReportResultsRowData();
            $firstRangeAttributeName  = $this->resolveChartFirstRangeAttributeNameForReportResultsRowData();
            $chartData                = array();
            foreach ($resultsData as $data)
            {
                $chartData[] = array(ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName(1)
                                        => $data->$firstRangeAttributeName,
                                     ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesDisplayLabelName(1)
                                        => strval($data->$firstSeriesAttributeName));
            }
            return new ReportDataProviderToAmChartMakerAdapter($this->report, $chartData);
        }

        protected function makeStackedReportDataProviderToAmChartMakerAdapter()
        {
            $resultsData                     = $this->fetchChartData();
            $firstRangeAttributeName         = $this->resolveChartFirstRangeAttributeNameForReportResultsRowData();
            $secondSeriesAttributeName       = $this->resolveChartSecondSeriesAttributeNameForReportResultsRowData();
            $secondRangeAttributeName        = $this->resolveChartSecondRangeAttributeNameForReportResultsRowData();
            $chartData                       = array();
            $secondSeriesValueData           = array();
            $secondSeriesDisplayLabels       = array();
            $secondSeriesValueCount          = 1;
            $firstSeriesDisplayAttributeKey  = $this->getDisplayAttributeKeyByAttribute($this->report->getChart()->firstSeries);
            $secondSeriesDisplayAttributeKey = $this->getDisplayAttributeKeyByAttribute($this->report->getChart()->secondSeries);
            foreach ($resultsData as $data)
            {
                $firstSeriesDataValue             = $data->resolveRawValueByDisplayAttributeKey($firstSeriesDisplayAttributeKey);
                $chartData[$firstSeriesDataValue] = array(
                                                    ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesDisplayLabelName(1) =>
                                                    $this->getDisplayAttributeByAttribute($this->report->getChart()->firstSeries)->
                                                    resolveValueAsLabelForHeaderCell($firstSeriesDataValue));
                $secondSeriesDataValue            = $data->resolveRawValueByDisplayAttributeKey($secondSeriesDisplayAttributeKey);
                if(!isset($secondSeriesValueData[$secondSeriesDataValue]))
                {
                    $secondSeriesValueData[$secondSeriesDataValue]      = $secondSeriesValueCount;
                    $secondSeriesDisplayLabels[$secondSeriesValueCount] = $this->getDisplayAttributeByAttribute(
                                                                          $this->report->getChart()->secondSeries)->
                                                                          resolveValueAsLabelForHeaderCell($secondSeriesDataValue);
                    $secondSeriesValueCount ++;
                }
            }
            foreach ($resultsData as $data)
            {
                $firstSeriesDataValue  = $data->resolveRawValueByDisplayAttributeKey($firstSeriesDisplayAttributeKey);
                $secondSeriesDataValue = $data->resolveRawValueByDisplayAttributeKey($secondSeriesDisplayAttributeKey);
                $secondSeriesKey       = $secondSeriesValueData[$secondSeriesDataValue];
                if(!isset($chartData[$firstSeriesDataValue]
                          [ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue]
                              [ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName($secondSeriesKey)] = 0;
                }
                $chartData[$firstSeriesDataValue]
                          [ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName($secondSeriesKey)] +=
                          $data->$firstRangeAttributeName;
                if(!isset($chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveFirstRangeDisplayLabelName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveFirstRangeDisplayLabelName($secondSeriesKey)] =
                        $this->getDisplayAttributeByAttribute($this->report->getChart()->firstRange)->label;
                }
                if(!isset($chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesValueName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesValueName($secondSeriesKey)] = 0;
                }
                $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesValueName($secondSeriesKey)] += $data->$secondRangeAttributeName;
                if(!isset($chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($secondSeriesKey)] =
                            $secondSeriesDisplayLabels[$secondSeriesKey];
                }
                if(!isset($chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($secondSeriesKey)] =
                        $this->getDisplayAttributeByAttribute($this->report->getChart()->secondRange)->label;
                }
            }
            return new ReportDataProviderToAmChartMakerAdapter($this->report, array_values($chartData),
                                                               $secondSeriesValueData, $secondSeriesDisplayLabels,
                                                               $secondSeriesValueCount - 1);
        }

        private function resolveGroupBysThatAreNotYetDisplayAttributesAsDisplayAttributes()
        {
            foreach($this->resolveGroupBys() as $groupBy)
            {
                if(null === $index = $this->report->getDisplayAttributeIndex($groupBy->attributeIndexOrDerivedType))
                {
                    $displayAttribute                              = new DisplayAttributeForReportForm(
                                                                     $groupBy->getModuleClassName(),
                                                                     $groupBy->getModelClassName(),
                                                                     $this->report->getType());
                    $displayAttribute->attributeIndexOrDerivedType = $groupBy->attributeIndexOrDerivedType;
                    $displayAttribute->queryOnly                   = true;
                    $displayAttribute->valueUsedAsDrillDownFilter  = true;
                    $this->resolvedDisplayAttributes[]             = $displayAttribute;
                }
                else
                {
                    $this->resolvedDisplayAttributes[$index]->valueUsedAsDrillDownFilter = true;
                }
            }

        }
    }
?>
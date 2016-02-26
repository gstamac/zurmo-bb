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
     * Rule used in search form to hide old completed tasks.
     */
    class HideOlderCompletedItemsSearchFormAttributeMappingRules extends SearchFormAttributeMappingRules
    {
        public static function resolveValueDataIntoUsableValue($value)
        {
            if ($value == true)
            {
                return 0;
            }
            return 1;
        }
        
        /**
         * 
         * @param string $attributeName
         * @param array $attributeAndRelations
         * @param mixed $value
         */
        public static function resolveAttributesAndRelations($attributeName, & $attributeAndRelations, $value)
        {
            assert('is_string($attributeName)');
            assert('$attributeAndRelations == "resolveEntireMappingByRules"');
            
            if ($value == true)
            {
                $realAttributeName      = 'completedDateTime';
                $realAttributeName1     = 'completed';
                $todayMinusThirtyDays   = static::calculateNewDateByDaysFromNow(-30);
                $greaterThanValue       = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($todayMinusThirtyDays);
                $attributeAndRelations  = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue),
                                                array($realAttributeName1, null, 'equals', 'resolveValueByRules'),);
            }
            else
            {
                $attributeAndRelations = array();
            }
        }
        
        /**
         * Given an integer representing a count of days from the present day, returns a DB formatted date stamp based
         * on that calculation. This is a wrapper method for @see DateTimeCalculatorUtil::calculateNewByDaysFromNow
         * @param integer $daysFromNow
         */
        public static function calculateNewDateByDaysFromNow($daysFromNow)
        {
            assert('is_int($daysFromNow)');
            return   DateTimeCalculatorUtil::calculateNewByDaysFromNow($daysFromNow,
                     new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
        }
        
        /**
         * Override so that it shows up as a checkbox and not a dropdown since normally checkboxes are converted to
         * dropdowns in search views.
         */
        public static function getIgnoredSavableMetadataRules()
        {
            return array('BooleanAsDropDown');
        }
    }
?>
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
     * Base Class for all searchForms that are module specific. This for is to be used if your module form
     * needs to be adapted in the SearchDataProviderMetadataAdapter
     */
    abstract class SearchForm extends ModelForm
    {
        /**
         * For each SearchForm attribute, there is either 1 or more corresponding model attributes. Specify this
         * information in this method as an array
         * @return array of metadata or null.
         */
        public function getAttributesMappedToRealAttributesMetadata()
        {
            return array();
        }

        /**
         * All search forms on validation would ignore required.  There are no required attributes on
         * a search form.  This is an override.
         */
        protected static function shouldIgnoreRequiredValidator()
        {
            return true;
        }

        /**
         * Override if any attributes support SearchFormAttributeMappingRules
         */
        protected static function getSearchFormAttributeMappingRulesType()
        {
            return array();
        }

        /**
         * Given an attributeName, return the corresponding rule type.
         * @param string $attributeName
         */
        public static function getSearchFormAttributeMappingRulesTypeByAttribute($attributeName)
        {
            assert('is_string($attributeName)');
            $ruleTypesIndexedByAttributeName = static::getSearchFormAttributeMappingRulesType();
            if(isset($ruleTypesIndexedByAttributeName[$attributeName]))
            {
                return $ruleTypesIndexedByAttributeName[$attributeName];
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>
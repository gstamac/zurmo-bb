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

    class ProductCatalog extends Item
    {
        const DEFAULT_NAME = 'Default';

        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            $bean = R::findOne(ProductCatalog::getTableName('ProductCatalog'), "name = :name ", array(':name' => $name));
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            else
            {
                $catalog = self::makeModel($bean);
            }
            return $catalog;
        }

        public static function resolveAndGetByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            try
            {
                $catalog = self::getByName($name);
            }
            catch (NotFoundException $e)
            {
                if ($name == self::DEFAULT_NAME)
                {
                    $catalog       = new ProductCatalog();
                    $catalog->name = self::DEFAULT_NAME;
                    $saved         = $catalog->save();
                    assert('$saved');
                }
                else
                {
                    throw new NotFoundException();
                }
            }

            return $catalog;
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
                )
            );
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('ProductTemplatesModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public static function getModuleClassName()
        {
            return 'ProductTemplatesModule';
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return 'Product Catalog';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return 'Product Catalogs';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                ),
                'relations' => array(
                    'productCategories'         => array(RedBeanModel::MANY_MANY, 'ProductCategory'),
                ),
                'rules' => array(
                    array('name',  'required'),
                    array('name',  'type',    'type' => 'string'),
                    array('name',  'length',  'min'  => 3,  'max' => 64),
                ),
                'elements' => array(
                ),
                'customFields' => array(
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getRollUpRulesType()
        {
            return 'ProductCatalog';
        }

        public static function getGamificationRulesType()
        {
            //return 'ProductCatalogGamification';
        }

        protected function beforeDelete()
        {
            parent::beforeDelete();
            if($this->name != self::DEFAULT_NAME)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
?>
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
     * Class helps support adding/removing product template categories while saving a product template from a post.
     */
    class ProductTemplateZurmoControllerUtil extends ModelHasFilesAndRelatedItemsZurmoControllerUtil
    {
        protected $productTemplateProductCategoryFormName;

        /**
         * Array of Person models of people added as product categories
         * @var array
         */
        protected $peopleAddedAsProductTemplateProductCategories;

        public function __construct($relatedItemsRelationName, $relatedItemsFormName, $productTemplateProductCategoryFormName)
        {
            assert('is_string($relatedItemsRelationName)');
            assert('is_string($relatedItemsFormName)');
            parent::__construct($relatedItemsRelationName, $relatedItemsFormName);
            $this->productTemplateProductCategoryFormName = $productTemplateProductCategoryFormName;
        }

  //      protected static function resolveAndMakeExplicitReadWriteModelPermissions($sanitizedData, $model)
//        {
//            return ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($model);
//        }

        /**
         * Override to handle incoming product template product category information
         * (non-PHPdoc)
         * @see ModelHasRelatedItemsZurmoControllerUtil::afterSetAttributesDuringSave()
         */
        protected function afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions)
        {
            assert('$model instanceof ProductTemplate');
            assert('$explicitReadWriteModelPermissions instanceof ExplicitReadWriteModelPermissions');
            //parent::afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions);
            $postData = PostUtil::getData();
            if (isset($postData[$this->productTemplateProductCategoryFormName]))
            {
                $this->peopleAddedAsProductTemplateProductCategories = ProductTemplateProductCategoriesUtil::
                                                               resolveProductTemplateHasManyProductCategoriesFromPost($model,
                                                               $postData[$this->productTemplateProductCategoryFormName],
                                                               $explicitReadWriteModelPermissions);
            }
        }
    }
?>
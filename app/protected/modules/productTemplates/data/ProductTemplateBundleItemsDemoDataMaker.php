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
     * Class that builds demo product bundle items.
     */
    class ProductTemplateBundleItemsDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        public static function getDependencies()
        {
            return array('productTemplates');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            $productTemplateBundleItems = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $productTemplateBundleItem = new ProductTemplateBundleItem();
                $productTemplateBundleItem->productTemplate = $demoDataHelper->getRandomByModelName('ProductTemplate');
                $this->populateModel($productTemplateBundleItem);
                $saved                                      = $productTemplateBundleItem->save();
                assert('$saved');
                $productTemplateBundleItems[]               = $productTemplateBundleItem->id;
            }
            $demoDataHelper->setRangeByModelName('ProductTemplateBundleItem', $productTemplateBundleItems[0], $productTemplateBundleItems[count($productTemplateBundleItems)-1]);
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof ProductTemplateBundleItem');
            parent::populateModel($model);
            $productTemplateBundleItemRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames(
                                                'ProductTemplatesModule', 'ProductTemplateBundleItem');
            $name        = RandomDataUtil::getRandomValueFromArray($productTemplateBundleItemRandomData['names']);
            $model->name = $name;
        }
    }
?>
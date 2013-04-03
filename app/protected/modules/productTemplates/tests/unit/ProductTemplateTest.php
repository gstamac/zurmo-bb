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

    class ProductTemplateTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testDemoDataMaker()
        {
            $productTemplate                         = new ProductTemplate();
            $productTemplateRandomData               = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames(
                                            'ProductTemplatesModule', 'ProductTemplate');
            $name    = RandomDataUtil::getRandomValueFromArray($productTemplateRandomData['names']);
            $productTemplate->name                   = $name;
            $productTemplate->priceFrequency         = ProductTemplate::PRICE_FREQUENCY_ONE_TIME;
            $productTemplate->cost->value            = 200;
            $productTemplate->listPrice->value       = 200;
            $productTemplate->sellPrice->value       = 200;
            $productTemplate->type                   = ProductTemplate::TYPE_PRODUCT;
            $productTemplate->status                 = ProductTemplate::STATUS_ACTIVE;
            $this->assertTrue($productTemplate->save());
            $productTemplates[]                      = $productTemplate->id;
        }

        public function testCreateAndGetProductTemplateById()
        {
            $user            = UserTestHelper::createBasicUser('Steven');
            $product         = ProductTestHelper::createProductByNameForOwner('Product 1', $user);

            $productTemplate = ProductTemplateTestHelper::createProductTemplateByVariables($product, ProductTemplate::PRICE_FREQUENCY_ONE_TIME, ProductTemplate::TYPE_PRODUCT, ProductTemplate::STATUS_ACTIVE, SellPriceFormula::TYPE_EDITABLE);
            $this->assertTrue($productTemplate->save());
            $id              = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);
            $productTemplate = ProductTemplate::getById($id);
            $this->assertEquals('Red Widget', $productTemplate->name);
            $this->assertEquals('Description', $productTemplate->description);
            $this->assertEquals(ProductTemplate::PRICE_FREQUENCY_ONE_TIME, intval($productTemplate->priceFrequency));
            $this->assertEquals(500.54, $productTemplate->cost->value);
            $this->assertEquals(400.54, $productTemplate->listPrice->value);
            $this->assertEquals(300.54, $productTemplate->sellPrice->value);
            $this->assertEquals(ProductTemplate::TYPE_PRODUCT, $productTemplate->type);
            $typeArray = ProductTemplateElementUtil::getProductTemplateTypeDropdownArray();
            $statusArray = ProductTemplateElementUtil::getProductTemplateStatusDropdownArray();
            $this->assertEquals("Product", $typeArray[$productTemplate->type]);
            $this->assertEquals(ProductTemplate::STATUS_ACTIVE, $productTemplate->status);
            $this->assertEquals("Active", $statusArray[$productTemplate->status]);
            $this->assertEquals($product, $productTemplate->products[0]);
            //$this->assertTrue($productTemplate->sellPriceFormula->isSame($sellPriceFormula));
            $this->assertEquals($productTemplate->sellPriceFormula->type, SellPriceFormula::TYPE_EDITABLE);
        }

        public function testCreateAndGetProductTemplateByIdWithDifferentPriceFrequency()
        {
            $user                                        = UserTestHelper::createBasicUser('Steven1');
            $product                                     = ProductTestHelper::createProductByNameForOwner('Product 2', $user);

            $productTemplate                             = ProductTemplateTestHelper::createProductTemplateByVariables($product, ProductTemplate::PRICE_FREQUENCY_ONE_TIME, ProductTemplate::TYPE_PRODUCT, ProductTemplate::STATUS_ACTIVE, SellPriceFormula::TYPE_EDITABLE);
            $this->assertTrue($productTemplate->save());
            $id                                          = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);
            $productTemplate                             = ProductTemplate::getById($id);
            $this->assertEquals(ProductTemplate::PRICE_FREQUENCY_ONE_TIME, intval($productTemplate->priceFrequency));

            $productTemplate->priceFrequency             = ProductTemplate::PRICE_FREQUENCY_MONTHLY;
            $productTemplate->save();
            $id                                          = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);
            $productTemplate                             = ProductTemplate::getById($id);
            $this->assertEquals(ProductTemplate::PRICE_FREQUENCY_MONTHLY, intval($productTemplate->priceFrequency));

        }

        /**
         * @depends testCreateAndGetProductTemplateById
         */
        public function testGetProductTemplatesByName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $productTemplates           = ProductTemplate::getByName('Red Widget');
            $this->assertEquals(2, count($productTemplates));
            $this->assertEquals('Red Widget', $productTemplates[0]->name);
        }

        /**
         * @depends testCreateAndGetProductTemplateById
         */
        public function testGetLabel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $productTemplates           = ProductTemplate::getByName('Red Widget');
            $this->assertEquals(2, count($productTemplates));
            $this->assertEquals('Product Template',   $productTemplates[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Product Templates', $productTemplates[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetProductTemplatesByName
         */
        public function testGetProductTemplatesByNameForNonExistentName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $productTemplates           = ProductTemplate::getByName('Red Widget 1');
            $this->assertEquals(0, count($productTemplates));
        }

        /**
         * @depends testCreateAndGetProductTemplateById
         */
        public function testGetAll()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $productTemplates           = ProductTemplate::getAll();
            $this->assertEquals(3, count($productTemplates));
            $this->assertEquals(400.54, $productTemplates[1]->listPrice->value);
        }

        /**
         * @depends testCreateAndGetProductTemplateById
         */
        public function testUpdateProductTemplateFromForm()
        {
            $user                       = User::getByUsername('Steven');
            $currencies                 = Currency::getAll();
            $currency                   = $currencies[0];
            $currency->save();
            $postData = array(
                'name'             => 'Editable Sell Price',
                'description'      => 'Template Description',
                'status'           => ProductTemplate::STATUS_ACTIVE,
                'type'             => ProductTemplate::TYPE_PRODUCT,
                'sellPriceFormula' => array(
                                                'type' => 1,
                                                'discountOrMarkupPercentage' => 0
                                            ),
                'priceFrequency'   => ProductTemplate::PRICE_FREQUENCY_ANNUALLY,
                'cost'             => array(
                                                'currency' => array(
                                                                       'id' => $currency->id
                                                                    ),
                                                  'value' => 240
                                            ),
                'listPrice'        => array(
                                                'currency' => array(
                                                                        'id' => $currency->id
                                                                   ),
                                                    'value' => 200
                                            ),
                'sellPrice'        => array(
                                                'currency' => array(
                                                                        'id' => $currency->id
                                                                   ),
                                                    'value' => 170
                                            )
            );

            $controllerUtil             = new ZurmoControllerUtil();
            $productTemplate            = new ProductTemplate();
            $savedSucessfully           = false;
            $modelToStringValue         = null;
            $productTemplate            = $controllerUtil->saveModelFromPost($postData, $productTemplate, $savedSucessfully,
                                                                       $modelToStringValue);
            $this->assertTrue($savedSucessfully);

            $id                         = $productTemplate->id;
            unset($productTemplate);
            $productTemplate            = ProductTemplate::getById($id);
            $this->assertEquals('Editable Sell Price', $productTemplate->name);
            $this->assertEquals(170, $productTemplate->sellPrice->value);
            $this->assertEquals(240, $productTemplate->cost->value);
            $this->assertEquals(200, $productTemplate->listPrice->value);
        }

        public function testDeleteProductTemplate()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $productTemplates           = ProductTemplate::getAll();
            $this->assertEquals(4, count($productTemplates));
            $productTemplates[0]->delete();

            $productTemplates           = ProductTemplate::getAll();
            $this->assertEquals(3, count($productTemplates));
            $products = Product::getByName('Product 1');
            $productTemplates[0]->products->remove($products[0]);
            $productTemplates[0]->delete();

            $productTemplates           = ProductTemplate::getAll();
            $this->assertEquals(2, count($productTemplates));
            $products = Product::getByName('Product 2');
            $productTemplates[0]->products->remove($products[0]);
            $productTemplates[0]->delete();

            $productTemplates           = ProductTemplate::getAll();
            $this->assertEquals(1, count($productTemplates));
            $productTemplates[0]->delete();
        }

        public function testGetAllWhenThereAreNone()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $productTemplates           = ProductTemplate::getAll();
            $this->assertEquals(0, count($productTemplates));
        }

        public function testProductTemplateCategories()
        {
            $user            = UserTestHelper::createBasicUser('Steven 2');
            $product         = ProductTestHelper::createProductByNameForOwner('Product 1', $user);

            $productTemplate = ProductTemplateTestHelper::createProductTemplateByVariables($product, ProductTemplate::PRICE_FREQUENCY_ONE_TIME, ProductTemplate::TYPE_PRODUCT, ProductTemplate::STATUS_ACTIVE, SellPriceFormula::TYPE_EDITABLE);
            $productCategory = ProductCategoryTestHelper::createProductCategoryByName("Test Category");
            $productCategoryII = ProductCategoryTestHelper::createProductCategoryByName("Test CategoryII");
            $productTemplate->productCategories->add($productCategory);
            $productTemplate->productCategories->add($productCategoryII);
            $this->assertTrue($productTemplate->save());
            $id              = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);
            $productTemplate = ProductTemplate::getById($id);
            $this->assertEquals($productCategory, $productTemplate->productCategories[0]);
            $this->assertEquals($productCategoryII, $productTemplate->productCategories[1]);
        }
    }
?>
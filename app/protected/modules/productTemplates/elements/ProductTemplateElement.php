<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Display the product template selection. This is a
     * combination of a type-ahead input text field
     * and a selection button which renders a modal list view
     * to search on product template.  Also includes a hidden input for the user
     * id.
     */
    class ProductTemplateElement extends ModelElement
    {
        protected static $moduleId = 'productTemplates';

        /**
         * Render a hidden input, a text input with an auto-complete
         * event, and a select button. These three items together
         * form the Template Editable Element
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            assert('$this->model->{$this->attribute} instanceof ProductTemplate');
            return parent::renderControlEditable();
        }

        /**
         * @return string
         */
        protected function getModalTitleForSelectingModel()
        {
            return Zurmo::t('ProductTemplatesModule', 'Catalog Item Search');
        }

        /**
         * Registers scripts for autocomplete text field
         */
        protected function registerScriptForAutoCompleteTextField()
        {
            parent::registerScriptForAutoCompleteTextField();
            Yii::app()->clientScript->registerScriptFile(Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.productTemplates.elements.assets')
                    ) . '/ProductTemplateUtils.js',
                CClientScript::POS_END);
        }

        /**
         * Gets on select option for the automcomplete text field
         * @param string $idInputName
         * @return string
         */
        protected function getOnSelectOptionForAutoComplete($idInputName)
        {
            $url = Yii::app()->createUrl("productTemplates/default/getProductTemplateDataForProduct");
            return 'js:function(event, ui){ jQuery("#' . $idInputName . '").val(ui.item["id"]).trigger("change");
                        copyProductTemplateDataForProduct(ui.item["id"], \'' . $url . '\')}';
        }
    }
?>

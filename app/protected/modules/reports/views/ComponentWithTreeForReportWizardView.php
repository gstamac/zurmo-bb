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
     * Base view class for components that appear in the report wizard and have a tree widget to select attributes
     */
    abstract class ComponentWithTreeForReportWizardView extends ComponentForReportWizardView
    {
        /**
         * @return integer
         */
        abstract protected function getItemsCount();

        /**
         * @param integer $rowCount
         * @return string
         */
        abstract protected function getItemsContent(& $rowCount);

        /**
         * Override in child class
         * @throws NotImplementedException
         */
        public static function getTreeType()
        {
            throw new NotImplementedException();
        }

        /**
         * @return string
         */
        public static function getTreeDivId()
        {
            return static::getTreeType() . 'TreeArea';
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $content  = $this->renderAttributesAndRelationsTreeContent();
            $content .= ZurmoHtml::tag('div', array('class' => 'dynamic-droppable-area'), $this->renderRightSideContent());
            return $content;
        }

        /**
         * @return string
         */
        protected function renderRightSideContent()
        {
            $rowCount                    = 0;
            $items                       = $this->getItemsContent($rowCount);
            if ($this->isListContentSortable())
            {
                $itemsContent            = $this->getSortableListContent($items);
            }
            else
            {
                $itemsContent            = $this->getNonSortableListContent($items);
            }
            $idInputHtmlOptions          = array('id' => static::resolveRowCounterInputId(static::getTreeType()));
            $hiddenInputName             = static::getTreeType() . 'RowCounter';
            $dropZone                    = $this->renderRightSideDropZoneContent();
            $droppableAttributesContent  = ZurmoHtml::tag('div', array('class' => 'dynamic-rows'), $itemsContent);
            $droppableAttributesContent .= $this->renderExtraDroppableAttributesContent();
            $content                     = ZurmoHtml::hiddenField($hiddenInputName, $rowCount, $idInputHtmlOptions);
            $content                    .= ZurmoHtml::tag('div', array('class' => 'droppable-dynamic-rows-container ' .
                                                                           static::getTreeType()), $droppableAttributesContent . $dropZone);
            $content                    .= ZurmoHtml::tag('div', array('class' => 'zero-components-view ' .
                                           static::getTreeType()), $this->getZeroComponentsContent());
            return $content;
        }

        /**
         * @return string
         */
        protected function renderRightSideDropZoneContent()
        {
            return ZurmoHtml::tag('div', array('class' => 'drop-zone'), ZurmoHtml::tag('div', array(), Zurmo::t('Core', 'Drop Here')));
        }

        /**
         * Override in child class as needed
         */
        protected function renderExtraDroppableAttributesContent()
        {
        }

        /**
         * @param integer $rowCount
         * @param array $componentData
         * @param bool $trackableStructurePosition
         * @return array
         */
        protected function renderItems(& $rowCount, $componentData, $trackableStructurePosition = false)
        {
            assert('is_int($rowCount)');
            assert('is_array($componentData)');
            assert('is_bool($trackableStructurePosition)');
            $items                      = array();
            $wizardFormClassName        = get_class($this->model);
            foreach ($componentData as $component)
            {
                $nodeIdWithoutTreeType      = $component->attributeIndexOrDerivedType;
                $inputPrefixData            = ReportRelationsAndAttributesToTreeAdapter::
                                              resolveInputPrefixData($wizardFormClassName,
                                              $this->getTreeType(), $rowCount);
                $adapter                    = new ReportAttributeToElementAdapter($inputPrefixData, $component,
                                              $this->form, $this->getTreeType());
                $view                       = new AttributeRowForReportComponentView($adapter,
                                              $rowCount, $inputPrefixData,
                                              ReportRelationsAndAttributesToTreeAdapter::
                                              resolveAttributeByNodeId($nodeIdWithoutTreeType),
                                              (bool)$trackableStructurePosition, true, static::getTreeType());
                $view->addWrapper           = false;
                $items[]                    = array('content' => $view->render());
                $rowCount++;
            }
            return $items;
        }

        /**
         * @param array $items
         * @return string
         */
        protected function getNonSortableListContent(Array $items)
        {
            $content = null;
            foreach ($items as $item)
            {
                $content .= ZurmoHtml::tag('li', array(), $item['content']);
            }
            return ZurmoHtml::tag('ul', array(), $content);
        }

        /**
         * @param array $items
         * @return string
         */
        protected function getSortableListContent(Array $items)
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip(static::getTreeType() . 'ReportComponentSortable');
            $cClipWidget->widget('application.core.widgets.JuiSortable', array(
                'items' => $items,
                'itemTemplate' => '<li>content</li>',
                'htmlOptions' =>
                array(
                    'id'    => static::getTreeType() . 'attributeRowsUl',
                    'class' => 'sortable',
                ),
                'options' => array(
                    'placeholder' => 'ui-state-highlight',
                ),
                'showEmptyList' => false
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips[static::getTreeType() . 'ReportComponentSortable'];
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $script = '
                $(".droppable-dynamic-rows-container.' . static::getTreeType() . '").live("drop", function(event, ui)
                {
                    ' . $this->getAjaxForDroppedAttribute() . '
                });
                $(".item-to-place", "#' . static::getTreeType() . 'TreeArea").live("dblclick", function(event)
                {
                    ' . $this->getAjaxForDoubleClickedAttribute() . '
                });
                $(".remove-dynamic-row-link.' . static::getTreeType() . '").live("click", function()
                {
                    var size = $("#' . get_class($this) . ' .dynamic-rows > ul > li").length;
                    $(this).parent().parent().remove(); //removes the <li>
                    if (size < 2)
                    {
                        $(".' . static::getZeroComponentsClassName() . '").fadeIn(400);
                    }
                    ' . $this->getReportAttributeRowAddOrRemoveExtraScript() . '
                    return false;
                });
            ';
            Yii::app()->getClientScript()->registerScript(static::getTreeType() . 'ReportComponentForTreeScript', $script);
        }

        /**
         * @return string
         */
        protected function getAddAttributeUrl()
        {
            return  Yii::app()->createUrl('reports/default/addAttributeFromTree',
                        array_merge($_GET, array('type'     => $this->model->type,
                                                 'treeType' => static::getTreeType())));
        }

        /**
         * @return string
         */
        protected function getAjaxForDroppedAttribute()
        {
            $rowCounterInputId = static::resolveRowCounterInputId(static::getTreeType());
            return ZurmoHtml::ajax(array(
                    'type'     => 'POST',
                    'data'     => 'js:$("#' . $this->form->getId() . '").serialize()',
                    'url'      => 'js:$.param.querystring("' .
                                  $this->getAddAttributeUrl() .
                                  '", "nodeId=" + ui.helper.attr("id") + "&rowNumber="  + $(\'#' . $rowCounterInputId . '\').val())',
                    'beforeSend' => 'js:function()
                    {
                        $(".ui-overlay-block").fadeIn(50);
                        $(this).makeLargeLoadingSpinner(true, ".ui-overlay-block"); //- add spinner to block anything else
                    }',
                    'success' => 'js:function(data)
                    {
                        var ul = $(".droppable-dynamic-rows-container.' . static::getTreeType() . '").parent().find(".dynamic-rows").find("ul:first");
                        $(\'#' . $rowCounterInputId . '\').val(parseInt($(\'#' . $rowCounterInputId . '\').val()) + 1);
                        ul.append(data);
                        ' . $this->getReportAttributeRowAddOrRemoveExtraScript() . '
                        $(".' . static::getZeroComponentsClassName() . '").fadeOut(150);
                        $(this).makeLargeLoadingSpinner(false, ".ui-overlay-block");
                        $(".ui-overlay-block").fadeOut(50);
                        window.scrollTo(0, ul.find("li:last-child > div").offset().top);
                        ul.find("li:last-child > div").addClass("glow").animate({backgroundColor:"#f0f0f0"}, 2000);
                    }'
            ));
        }

        /**
         * @return string
         */
        protected function getAjaxForDoubleClickedAttribute()
        {
            $rowCounterInputId = static::resolveRowCounterInputId(static::getTreeType());
            return ZurmoHtml::ajax(array(
                    'type'     => 'POST',
                    'data'     => 'js:$("#' . $this->form->getId() . '").serialize()',
                    'url'      => 'js:$.param.querystring("' . $this->getAddAttributeUrl() . '",
                                        "nodeId=" + event.currentTarget.id + "&rowNumber=" + $(\'#' . $rowCounterInputId . '\').val())',
                    'beforeSend' => 'js:function()
                    {
                        $(".ui-overlay-block").fadeIn(50);
                        $(this).makeLargeLoadingSpinner(true, ".ui-overlay-block"); //- add spinner to block anything else
                    }',
                    'success' => 'js:function(data)
                    {
                        var ul = $(".droppable-dynamic-rows-container.' . static::getTreeType() . '").parent().find(".dynamic-rows").find("ul:first");
                        $(\'#' . $rowCounterInputId . '\').val(parseInt($(\'#' . $rowCounterInputId . '\').val()) + 1);
                        ul.append(data);
                        ' . $this->getReportAttributeRowAddOrRemoveExtraScript() . '
                        $(".' . static::getZeroComponentsClassName() . '").hide();
                        $(this).makeLargeLoadingSpinner(false, ".ui-overlay-block");
                        $(".ui-overlay-block").fadeOut(50);
                        window.scrollTo(0, ul.find("li:last-child > div").offset().top);
                        ul.find("li:last-child > div").addClass("glow").animate({backgroundColor:"#f0f0f0"}, 2000);
                    }'
            ));
        }

        /**
         * Override in child class as needed
         */
        protected function getReportAttributeRowAddOrRemoveExtraScript()
        {
        }

        /**
         * @return bool
         */
        protected function isListContentSortable()
        {
            return false;
        }
    }
?>
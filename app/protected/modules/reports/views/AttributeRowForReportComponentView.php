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

    class AttributeRowForReportComponentView extends View
    {
        protected $rowNumber;

        protected $inputPrefixData;

        protected $attribute;

        protected $hasTrackableStructurePosition;

        public function __construct($rowNumber, $inputPrefixData, $attribute, $hasTrackableStructurePosition)
        {
            assert('is_int($rowNumber)');
            assert('is_array($inputPrefixData)');
            assert('is_string($attribute)');
            assert('is_bool($hasTrackableStructurePosition)');
            $this->rowNumber                          = $rowNumber;
            $this->inputPrefixData                    = $inputPrefixData;
            $this->attribute                          = $attribute;
            $this->hasTrackableStructurePosition      = $hasTrackableStructurePosition;
        }

        public function render()
        {
            return $this->renderContent();
        }

        protected function renderContent()
        {
            $content  = '<div>';
            if($this->hasTrackableStructurePosition)
            {
                $content .= $this->renderReportAttributeRowNumberLabel();
                $content .= $this->renderHiddenStructurePositionInput();
            }
            $content .= $this->renderAttributeContent();
            $content .= '</div>';
            $content .= ZurmoHtml::link('_', '#', array('class' => 'remove-report-attribute-row-link'));
            $content  =  ZurmoHtml::tag('div', array('class' => 'report-attribute-row'), $content);
            return ZurmoHtml::tag('li', array(), $content);
        }

        protected function renderReportAttributeRowNumberLabel()
        {
            return ZurmoHtml::tag('span', array('class' => 'report-attribute-row-number-label'),
                                          ($this->rowNumber + 1) . '.');
        }

        protected function renderHiddenStructurePositionInput()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                                            array_merge($this->inputPrefixData, array('structurePosition')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                                            array_merge($this->inputPrefixData, array('structurePosition')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId, 'class' => 'structure-position');
            return ZurmoHtml::hiddenField($hiddenInputName, ($this->rowNumber + 1), $idInputHtmlOptions);
        }

        protected function renderAttributeContent()
        {
            //todo: [[0][attributeIndexOrDerivedType]
            //then you can make [0]['myAttribute'] and its content determined by the element
            $content = 'input attribute: ' . $this->attribute . "<BR>";
            return $content;
        }
    }
?>
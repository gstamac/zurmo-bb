<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class DecimalAttributeForm extends MaxLengthAttributeForm
    {
        public $precisionLength = 2;

        public function __construct(RedBeanModel $model = null, $attributeName = null)
        {
            $this->maxLength = 18;
            parent::__construct($model, $attributeName);
            if ($model !== null)
            {
                $validators = $model->getValidators($attributeName);
                foreach ($validators as $validator)
                {
                    if ($validator instanceof RedBeanModelNumberValidator)
                    {
                        if ($validator->precision !== null)
                        {
                            $this->precisionLength = $validator->precision;
                        }
                    }
                }
            }
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('maxLength',        'numerical', 'min' => 1, 'max' => 18),
                array('precisionLength',  'length',    'min' => 1, 'max' => 2),
                array('precisionLength',  'numerical', 'min' => 1, 'max' => 6),
                array('precisionLength',  'numerical', 'integerOnly' => true),
                array('defaultValue',  'numerical'),
            ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                'precisionLength' => Zurmo::t('DesignerModule', 'Precision'),
            ));
        }

        public static function getAttributeTypeDisplayName()
        {
            return Zurmo::t('DesignerModule', 'Decimal');
        }

        public static function getAttributeTypeDisplayDescription()
        {
            return Zurmo::t('DesignerModule', 'A decimal field');
        }

        public function getAttributeTypeName()
        {
            return 'Decimal';
        }

        public function getModelAttributePartialRule()
        {
            return array('type', 'type' => 'float');
        }
    }
?>

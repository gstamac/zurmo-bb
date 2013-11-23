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
     * Adapter class to get attributes from
     * a model as an array.
     */
    class ModelAttributesAdapter
    {
        protected $model;

        public function __construct(RedBeanModel $model)
        {
            assert('$model !== null');
            $this->model = $model;
        }

        public function getModel()
        {
            return $this->model;
        }

        /**
         * Returns HAS_ONE relation attributes
         * and non-relation attributes in an array
         * mapping attribute names to 'attributeLabel' to the
         * attribute label.  Also returns 'isRequired' and 'isAudited' information.
         */
        public function getAttributes()
        {
            $attributes = array();
            ModelAttributeCollectionUtil::populateCollection(
                $attributes,
                'id',
                $this->model->getAttributeLabel('id'),
                'Text'
            );
            $modelMetadata = $this->model->getMetadata();
            foreach ($this->model->attributeNames() as $attributeName)
            {
                if (!$this->model->isRelation($attributeName) ||
                    $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE ||
                    $this->model->getRelationType($attributeName) == RedBeanModel::HAS_MANY_BELONGS_TO)
                {
                    if ($this->model instanceof Item)
                    {
                        $isAudited = $this->model->isAttributeAudited($attributeName);
                    }
                    else
                    {
                        $isAudited = false;
                    }

                    $customFieldName = null;
                    if ($this->model->isRelation($attributeName) &&
                        $this->model->{$attributeName} instanceof BaseCustomField)
                    {
                        foreach ($modelMetadata as $modelClassName => $modelClassMetadata)
                        {
                            if (isset($modelMetadata[$modelClassName]['customFields']) &&
                                isset($modelMetadata[$modelClassName]['customFields'][$attributeName]))
                            {
                                $customFieldName = $modelMetadata[$modelClassName]['customFields'][$attributeName];
                            }
                        }
                    }

                    ModelAttributeCollectionUtil::populateCollection(
                        $attributes,
                        $attributeName,
                        $this->model->getAttributeLabel($attributeName),
                        ModelAttributeToDesignerTypeUtil::getDesignerType($this->model, $attributeName),
                        $this->model->isAttributeRequired($attributeName),
                        $this->model->isAttributeReadOnly($attributeName),
                        $isAudited,
                        $customFieldName
                    );
                }
            }
            return $attributes;
        }

        /**
         * Returns standard attributes in same
         * format as getAttributes returns.
         */
        public function getStandardAttributes()
        {
            $attributes = $this->getAttributes();
            $defaultAttributeNames = $this->getStandardAttributeNames();
            foreach ($attributes as $attributeName => $notUsed)
            {
                if (!in_array($attributeName, $defaultAttributeNames))
                {
                    unset($attributes[$attributeName]);
                }
            }
            return $attributes;
        }

        /**
         * Given an attributeName, is this a default attribute on the model
         */
        public function isStandardAttribute($attributeName)
        {
            $defaultAttributeNames = $this->getStandardAttributeNames();
            if (in_array($attributeName, $defaultAttributeNames))
            {
                return true;
            }
            return false;
        }

        /**
         * Returns custom attributes in same
         * format as getAttributes returns.
         */
        public function getCustomAttributes()
        {
            $attributes = $this->getAttributes();
            $defaultAttributeNames = $this->getStandardAttributeNames();
            foreach ($attributes as $attributeName => $notUsed)
            {
                if (in_array($attributeName, $defaultAttributeNames))
                {
                    unset($attributes[$attributeName]);
                }
            }
            return $attributes;
        }

        private function getStandardAttributeNames()
        {
            $defaultAttributeNames = array('id');
            $metadata = $this->model->getDefaultMetadata();
            foreach ($metadata as $className => $perClassMetadata)
            {
                foreach ($perClassMetadata as $key => $value)
                {
                    if ($key == 'members')
                    {
                        $defaultAttributeNames = array_merge($defaultAttributeNames, $value);
                    }
                    elseif ($key == 'relations')
                    {
                        $defaultAttributeNames = array_merge($defaultAttributeNames, array_keys($value));
                    }
                }
            }
            return $defaultAttributeNames;
        }

        public function setAttributeMetadataFromForm(AttributeForm $attributeForm)
        {
            $modelClassName  = get_class($this->model);
            $attributeName   = $attributeForm->attributeName;
            $attributeLabels = $attributeForm->attributeLabels;
            $defaultValue    = $attributeForm->defaultValue;
            $elementType     = $attributeForm->getAttributeTypeName();
            $partialTypeRule = $attributeForm->getModelAttributePartialRule();

            //should we keep this here with (boolean)?
            $isRequired      = (boolean)$attributeForm->isRequired;
            $isAudited       = (boolean)$attributeForm->isAudited;
            if (!$attributeForm instanceof DropDownAttributeForm)
            {
                if ($defaultValue === '')
                {
                    $defaultValue = null;
                }
                if ($attributeForm instanceof MaxLengthAttributeForm)
                {
                    if ($attributeForm->maxLength != null)
                    {
                        $maxLength = (int)$attributeForm->maxLength;
                    }
                    else
                    {
                        $maxLength = null;
                    }
                }
                else
                {
                    $maxLength = null;
                }
                if ($attributeForm instanceof MinMaxValueAttributeForm)
                {
                    if ($attributeForm->minValue != null)
                    {
                        $minValue = (int)$attributeForm->minValue;
                    }
                    else
                    {
                        $minValue = null;
                    }
                    if ($attributeForm->maxValue != null)
                    {
                        $maxValue = (int)$attributeForm->maxValue;
                    }
                    else
                    {
                        $maxValue = null;
                    }
                }
                else
                {
                    $minValue = null;
                    $maxValue = null;
                }
                if ($attributeForm instanceof DecimalAttributeForm)
                {
                    $precision = (int)$attributeForm->precisionLength;
                }
                else
                {
                    $precision = null;
                }
                ModelMetadataUtil::addOrUpdateMember($modelClassName,
                                                     $attributeName,
                                                     $attributeLabels,
                                                     $defaultValue,
                                                     $maxLength,
                                                     $minValue,
                                                     $maxValue,
                                                     $precision,
                                                     $isRequired,
                                                     $isAudited,
                                                     $elementType,
                                                     $partialTypeRule);
                $this->resolveDatabaseSchemaForModel($modelClassName);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @param string $attributeName
         */
        public function removeAttributeMetadata($attributeName)
        {
            assert('is_string($attributeName) && $attributeName != ""');
            $modelClassName = get_class($this->model);
            ModelMetadataUtil::removeAttribute($modelClassName, $attributeName);
        }

        public function resolveDatabaseSchemaForModel($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            Yii::app()->gameHelper->muteScoringModelsOnSave();
            $messageLogger = new MessageLogger();
            RedBeanModelsToTablesAdapter::generateTablesFromModelClassNames(array($modelClassName), $messageLogger);
            Yii::app()->gameHelper->unmuteScoringModelsOnSave();
            if ($messageLogger->isErrorMessagePresent())
            {
                throw new FailedDatabaseSchemaChangeException($messageLogger->printMessages(true, true));
            }
        }

        /**
         * Given a standard attribute, check if by default, this attribute is required. This means the default metadata
         * has this attribute has being required, regardless of any customziation to that metadata.
         * @param string $attributeName
         * @throws NotSupportedException
         * @return boolean
         */
        public function isStandardAttributeRequiredByDefault($attributeName)
        {
            assert('is_string($attributeName)');
            if (!$this->isStandardAttribute($attributeName))
            {
                throw new NotSupportedException();
            }
            $modelClassName  = get_class($this->model);
            $modelClassName  = $modelClassName::getAttributeModelClassName($attributeName);
            $metadata        = $modelClassName::getDefaultMetadata();
            if (isset($metadata[$modelClassName]['rules']))
            {
                foreach ($metadata[$modelClassName]['rules'] as $validatorMetadata)
                {
                    assert('isset($validatorMetadata[0])');
                    assert('isset($validatorMetadata[1])');
                    if ($validatorMetadata[0] == $attributeName && $validatorMetadata[1] == 'required')
                    {
                        return true;
                    }
                }
            }
            return false;
        }
    }
?>

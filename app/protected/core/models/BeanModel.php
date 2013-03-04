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

    abstract class BeanModel extends ObservableComponent
    {
        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:1 and that the class on the side of the relationship where this is not a column in that
         * model's table.  Example: model X HAS_ONE Y.  There will be a y_id on the x table.  But in Y you would have
         * HAS_ONE_BELONGS_TO X and there would be no column in the y table.
         */
        const HAS_ONE_BELONGS_TO = 0;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:M and that the class on the M side of the
         * relation.
         * Note: Currently if you have a relation that is set to HAS_MANY_BELONGS_TO, then that relation name
         * must be the strtolower() same as the related model class name.  This is the current support for this
         * relation type.  If something different is set, an exception will be thrown.
         */
        const HAS_MANY_BELONGS_TO = 1;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:1.
         */
        const HAS_ONE    = 2;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:M and that the class is on the 1 side of the
         * relation.
         */
        const HAS_MANY   = 3;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is M:N and that the class on the either side of the
         * relation.
         */
        const MANY_MANY  = 4;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a 1:1 or 1:M relation is one in which the left side of the relation
         * owns the model or models on the right side, meaning that if the model
         * is deleted it owns the related models and they are deleted along with it.
         * If not specified the related model is independent and is not deleted.
         */
        const OWNED     = true;

        /**
         * @see const OWNED for more information.
         * @var boolean
         */
        const NOT_OWNED = false;

        /**
         * @see RedBeanModel::$lastClassInBeanHeirarchy
         */
        protected static $lastClassInBeanHeirarchy = 'BeanModel';

        private static   $attributeNamesToClassNames;

        private static   $relationNameToRelationTypeModelClassNameAndOwns;

        private static   $attributeNamesNotBelongsToOrManyMany;

        /**
         * Can the class have a bean.  Some classes do not have beans as they are just used for modeling purposes
         * and do not need to store persistant data.
         * @var boolean
         */
        private static $canHaveBean = true;

        /**
         * @returns boolean
         */
        public static function getCanHaveBean()
        {
            return self::$canHaveBean;
        }

        /**
         * Implement in children classes
         * @throws NotImplementedException
         */
        public static function getMetadata()
        {
            throw new NotImplementedException();
        }

        /**
         * Static alternative to using isAttribute which is a concrete method.
         * @param $attributeName
         * @return bool
         */
        public static function isAnAttribute($attributeName)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            return $attributeName == 'id' || array_key_exists($attributeName, self::getAttributeNamesToClassNamesForModel());
        }

        /**
         * This method is needed to interpret when the attributeName is 'id'.  Since id is not an attribute
         * on the model, we manaully check for this and return the appropriate class name.
         * @param string $attributeName
         * @return the model class name for the attribute.  This could be a casted up model class name.
         */
        public static function resolveAttributeModelClassName($attributeName)
        {
            assert('is_string($attributeName)');
            if ($attributeName == 'id')
            {
                return get_called_class();
            }
            return self::getAttributeModelClassName($attributeName);
        }

        /**
         * Returns the model class name for an
         * attribute name defined by the extending class's getMetadata() method.
         * For use by RedBeanModelDataProvider. Is unlikely to be of any
         * use to an application.
         */
        public static function getAttributeModelClassName($attributeName)
        {
            assert('self::isAnAttribute($attributeName, get_called_class())');
            $attributeNamesToClassNames = self::getAttributeNamesToClassNamesForModel();
            return $attributeNamesToClassNames[$attributeName];
        }

        /**
         * Returns true if the named attribute is one of the
         * relation names defined by the extending
         * class's getMetadata() method.
         */
        public static function isRelation($attributeName)
        {
            assert('self::isAnAttribute($attributeName, get_called_class())');
            return array_key_exists($attributeName, static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel());
        }

        /**
         * Returns true if the named attribute is one of the
         * relation names defined by the extending
         * class's getMetadata() method, and specifies RedBeanModel::OWNED.
         */
        public static function isOwnedRelation($attributeName)
        {
            assert('self::isAnAttribute($attributeName, get_called_class())');
            $relationAndOwns = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
            return array_key_exists($attributeName, $relationAndOwns) &&
                $relationAndOwns[$attributeName][2];
        }

        /**
         * Returns the relation type
         * relation name defined by the extending class's getMetadata() method.
         */
        public static function getRelationType($relationName)
        {
            assert('self::isRelation($relationName, get_called_class())');
            $relationAndOwns = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
            return $relationAndOwns[$relationName][0];
        }

        /**
         * Returns the model class name for a
         * relation name defined by the extending class's getMetadata() method.
         * For use by RedBeanModelDataProvider. Is unlikely to be of any
         * use to an application.
         */
        public static function getRelationModelClassName($relationName)
        {
            assert('self::isRelation($relationName, get_called_class())');
            $relationAndOwns = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
            return $relationAndOwns[$relationName][1];
        }

        /**
         * Given an attribute return the column name.
         * @param string $attributeName
         */
        public static function getColumnNameByAttribute($attributeName)
        {
            assert('is_string($attributeName)');
            if (self::isRelation($attributeName))
            {
                $modelClassName = get_called_class();
                $columnName = $modelClassName::getForeignKeyName($modelClassName, $attributeName);
            }
            else
            {
                $columnName = strtolower($attributeName);
            }
            return $columnName;
        }

        /**
         * Static implementation of attributeNames()
         */
        public static function getAttributeNames()
        {
            return array_keys(static::getAttributeNamesToClassNamesForModel());
        }

        /**
         * Static implementation of generateAttributeLabel()
         */
        public static function generateAnAttributeLabel($attributeName)
        {
            assert('self::isAnAttribute($attributeName, get_called_class())');
            return ucfirst(preg_replace('/([A-Z0-9])/', ' \1', $attributeName));
        }

        public static function getAbbreviatedAttributeLabel($attributeName)
        {
            return static::getAbbreviatedAttributeLabelByLanguage($attributeName, Yii::app()->language);
        }

        /**
         * Public for message checker only.
         */
        public static function getUntranslatedAbbreviatedAttributeLabels()
        {
            return static::untranslatedAbbreviatedAttributeLabels();
        }

        /**
         * Array of untranslated abbreviated attribute labels.
         */
        protected static function untranslatedAbbreviatedAttributeLabels()
        {
            return array();
        }

        protected static function untranslatedAttributeLabels()
        {
            return array();
        }

        /**
         * Given an attributeName and a language, retrieve the translated attribute label. Attempts to find a customized
         * label in the metadata first, before falling back on the standard attribute label for the specified attribute.
         * @return string - translated attribute label
         */
        protected static function getAbbreviatedAttributeLabelByLanguage($attributeName, $language)
        {
            assert('is_string($attributeName)');
            assert('is_string($language)');
            $labels = static::untranslatedAbbreviatedAttributeLabels();
            if (isset($labels[$attributeName]))
            {
                return ZurmoHtml::tag('span', array('title' => static::generateAnAttributeLabel($attributeName)),
                    Zurmo::t('Default', $labels[$attributeName],
                        LabelUtil::getTranslationParamsForAllModules(), null, $language));
            }
            else
            {
                return null;
            }
        }

        protected static function getMixedInModelClassNames()
        {
            return array();
        }

        private static function mapMetadataForAllClassesInHeirarchy()
        {
            self::$attributeNamesToClassNames[get_called_class()]                      = array();
            self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()] = array();
            self::$attributeNamesNotBelongsToOrManyMany[get_called_class()]            = array();
            foreach (array_reverse(RuntimeUtil::getClassHierarchy(get_called_class(), static::$lastClassInBeanHeirarchy)) as $modelClassName)
            {
                if ($modelClassName::getCanHaveBean())
                {
                    self::mapMetadataByModelClassName($modelClassName);
                }
            }
            foreach(static::getMixedInModelClassNames() as $modelClassName)
            {
                if ($modelClassName::getCanHaveBean())
                {
                    self::mapMetadataByModelClassName($modelClassName);
                }
            }
        }

        private static function mapMetadataByModelClassName($modelClassName)
        {
            assert('is_string($modelClassName)');
            assert('$modelClassName != ""');
            $metadata = static::getMetadata();
            if (isset($metadata[$modelClassName]))
            {
                if (isset($metadata[$modelClassName]['members']))
                {
                    foreach ($metadata[$modelClassName]['members'] as $memberName)
                    {

                        self::$attributeNamesToClassNames[get_called_class()][$memberName] = $modelClassName;
                        self::$attributeNamesNotBelongsToOrManyMany[get_called_class()][]  = $memberName;
                    }
                }
            }
            if (isset($metadata[$modelClassName]['relations']))
            {
                foreach ($metadata[$modelClassName]['relations'] as $relationName => $relationTypeModelClassNameAndOwns)
                {
                    assert('in_array(count($relationTypeModelClassNameAndOwns), array(2, 3, 4))');

                    $relationType           = $relationTypeModelClassNameAndOwns[0];
                    $relationModelClassName = $relationTypeModelClassNameAndOwns[1];
                    if ($relationType == self::HAS_MANY_BELONGS_TO &&
                        strtolower($relationName) != strtolower($relationModelClassName))
                    {
                        $label = 'Relations of type HAS_MANY_BELONGS_TO must have the relation name ' .
                            'the same as the related model class name. Relation: {relationName} ' .
                            'Relation model class name: {relationModelClassName}';
                        throw new NotSupportedException(Zurmo::t('Core', $label,
                            array('{relationName}' => $relationName,
                                '{relationModelClassName}' => $relationModelClassName)));
                    }
                    if (count($relationTypeModelClassNameAndOwns) >= 3 &&
                        $relationTypeModelClassNameAndOwns[2] == self::OWNED)
                    {
                        $owns = true;
                    }
                    else
                    {
                        $owns = false;
                    }
                    if (count($relationTypeModelClassNameAndOwns) == 4 && $relationType != self::HAS_MANY)
                    {
                        throw new NotSupportedException();
                    }
                    if (count($relationTypeModelClassNameAndOwns) == 4)
                    {
                        $relationPolyOneToManyName = $relationTypeModelClassNameAndOwns[3];
                    }
                    else
                    {
                        $relationPolyOneToManyName = null;
                    }
                    assert('in_array($relationType, array(self::HAS_ONE_BELONGS_TO, self::HAS_MANY_BELONGS_TO, ' .
                        'self::HAS_ONE, self::HAS_MANY, self::MANY_MANY))');
                    self::$attributeNamesToClassNames[get_called_class()][$relationName] = $modelClassName;
                    self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()][$relationName] =
                            array($relationType,
                                  $relationModelClassName,
                                  $owns,
                                  $relationPolyOneToManyName);
                    if (!in_array($relationType, array(self::HAS_ONE_BELONGS_TO, self::HAS_MANY_BELONGS_TO, self::MANY_MANY)))
                    {
                        self::$attributeNamesNotBelongsToOrManyMany[get_called_class()][] = $relationName;
                    }
                }
            }
        }

        protected static function getAttributeNamesToClassNamesForModel()
        {
            //todo: confirm empty array comes back true on isset. we need that in order for this to work
            if(!isset(self::$attributeNamesToClassNames[get_called_class()]))
            {
                self::mapMetadataForAllClassesInHeirarchy();
            }
            return self::$attributeNamesToClassNames[get_called_class()];
        }

        protected static function getAttributeNamesNotBelongsToOrManyManyForModel()
        {
            //todo: confirm empty array comes back true on isset. we need that in order for this to work
            if(!isset(self::$attributeNamesNotBelongsToOrManyMany[get_called_class()]))
            {
                self::mapMetadataForAllClassesInHeirarchy();
            }
            return self::$attributeNamesNotBelongsToOrManyMany[get_called_class()];
        }

        protected static function getRelationNameToRelationTypeModelClassNameAndOwnsForModel()
        {
            //todo: confirm empty array comes back true on isset. we need that in order for this to work
            if(!isset(self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()]))
            {
                self::mapMetadataForAllClassesInHeirarchy();
            }
            return self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()];
        }

        protected static function forgetBeanModel($modelClassName)
        {
            if(isset(self::$attributeNamesToClassNames[$modelClassName]))
            {
                unset(self::$attributeNamesToClassNames[$modelClassName]);
            }
            if(isset(self::$relationNameToRelationTypeModelClassNameAndOwns[$modelClassName]))
            {
                unset(self::$relationNameToRelationTypeModelClassNameAndOwns[$modelClassName]);
            }
            if(isset(self::$attributeNamesNotBelongsToOrManyMany[$modelClassName]))
            {
                unset(self::$attributeNamesNotBelongsToOrManyMany[$modelClassName]);
            }
        }

        protected static function forgetAllBeanModels()
        {
            self::$attributeNamesToClassNames                      = null;
            self::$relationNameToRelationTypeModelClassNameAndOwns = null;
            self::$attributeNamesNotBelongsToOrManyMany            = null;
        }
    }
?>
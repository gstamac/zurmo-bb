<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class RelatedModelNameOrIdValueTypeBatchAttributeValueDataAnalyzer extends IdValueTypeBatchAttributeValueDataAnalyzer
    {
        protected function ensureTypeValueIsValid($type)
        {
            assert('$type == RelatedModelValueTypeMappingRuleForm::ZURMO_USER_ID ||
                    $type == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID ||
                    $type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME');
        }

        protected function analyzeByValue($value)
        {
            $modelClassName = $this->attributeModelClassName;
            if($this->type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                return $this->resolveFoundIdByValue($value);
            }
            elseif(RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME)
            {
                $modelClassName = $this->attributeModelClassName;
                $sql = 'select name from ' . $modelClassName::getTableName($modelClassName) .
                " where name = lower(' . DatabaseCompatibilityUtil::lower($value) . ') limit 1";
                $ids =  R::getCol($sql);
                if(count($ids) == 0)
                {
                    return false;
                }
                return true;
            }
            else
            {
                return $this->resolveFoundExternalSystemIdByValue($value);
            }
        }

        protected function getMessageByFoundAndUnfoundCount($found, $unfound)
        {
            if($this->type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                $label   = '{found} record(s) will be updated ';
                $label  .= 'and {unfound} record(s) will be skipped during import.';
            }
            else
            {
                $label   = '{found} record(s) will be updated and ';
                $label  .= '{unfound} record(s) will be created during the import.';
            }
            return Yii::t('Default', $label, array('{found}' => $found, '{unfound}' => $unfound));
        }
    }
?>
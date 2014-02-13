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
     * Class to work with POST data and adapting that into a EmailTemplate object
     */
    class DataToEmailTemplateUtil
    {
        protected static $data;

        protected static $emailTemplate;

        /**
         * @param EmailTemplate static::$emailTemplate
         * @param array $postData
         * @param string$wizardFormClassName
         */
        public static function resolveEmailTemplateByWizardPostData(EmailTemplate $emailTemplate, array $postData, $wizardFormClassName)
        {
            assert('is_array($postData)');
            assert('is_string($wizardFormClassName)');
            static::$data                   = ArrayUtil::getArrayValue($postData, $wizardFormClassName);
            static::$emailTemplate          = $emailTemplate;
            static::resolveMetadataMembers();
            static::resolveOwner();
            static::resolveExplicitReadWritePermissions();
            static::resolveFileAttachments();
        }

        protected static function resolveMetadataMembers()
        {
            $metadata               = static::$emailTemplate->getMetadata();
            $members                = $metadata['EmailTemplate']['members'];
            foreach ($members as $member)
            {
                if (isset(static::$data[$member]))
                {
                    $postDataValue  = static::$data[$member];
                    $originalValue  = static::$emailTemplate->$member;
                    if ($member == 'serializedData')
                    {
                        static::resolveSerializedDataForTemplateByPostData(static::$data, static::$emailTemplate);
                        continue;
                    }
                    else if ($postDataValue != $originalValue)
                    {
                        if ($member == 'isDraft')
                        {
                            $postDataValue = (bool)$postDataValue;
                        }
                        static::$emailTemplate->$member = $postDataValue;
                    }
                }
            }
        }

        protected static function resolveSerializedDataForTemplateByPostData()
        {
            $unserializedData   = array();
            $postUnserializedData = static::$data['serializedData'];
            $templateUnserializedData   = unserialize(static::$emailTemplate->serializedData);
            if (empty($templateUnserializedData))
            {
                $templateUnserializedData = array('baseTemplateId' => null);
            }

            if (static::hasBaseTemplateIdChanged($postUnserializedData['baseTemplateId'], $templateUnserializedData['baseTemplateId']))
            {
                // baseTemplateId has changed.
                $baseTemplateModel  = EmailTemplate::getById(intval($postUnserializedData['baseTemplateId']));
                $unserializedData   = unserialize($baseTemplateModel->serializedData);
                unset($unserializedData['thumbnailUrl']);
                $unserializedData['baseTemplateId'] = $postUnserializedData['baseTemplateId'];
            }
            else if ($templateUnserializedData != $postUnserializedData)
            {
                // baseTemplateId remains same, probably a post from canvas.
                $unserializedData     = CMap::mergeArray($templateUnserializedData, $postUnserializedData);
            }

            if (!empty($unserializedData))
            {
                static::$emailTemplate->serializedData  = serialize($unserializedData);
            }
            // we don't need this as we "continue" in the invoker if block but still...
            unset(static::$data['serializedData']);
        }

        protected static function hasBaseTemplateIdChanged($postBaseTemplateId, $savedBaseTemplateId)
        {
            return ((empty($savedBaseTemplateId) && !empty($postBaseTemplateId)) ||
                    (!empty($postBaseTemplateId) && $savedBaseTemplateId != $postBaseTemplateId));
        }

        protected static function resolveOwner()
        {
            if (static::$data['ownerId'] && static::$data['ownerId'] != static::$emailTemplate->owner->id)
            {
                $owner                  = User::getById((int)static::$data['ownerId']);
                static::$emailTemplate->owner   = $owner;
            }
        }

        protected static function resolveExplicitReadWritePermissions()
        {
            if (isset(static::$data['explicitReadWriteModelPermissions']))
            {
                ExplicitReadWriteModelPermissionsUtil::resolveByPostDataAndModelThenMake(static::$data, static::$emailTemplate);
            }
        }

        protected static function resolveFileAttachments()
        {
            FileModelUtil::resolveModelsHasManyFilesFromPost(static::$emailTemplate, 'files', 'filesIds');
        }
    }
?>
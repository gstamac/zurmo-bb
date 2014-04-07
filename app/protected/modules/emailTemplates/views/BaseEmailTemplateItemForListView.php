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

    /**
     * Class BaseEmailTemplateItemForListView
     * View of the BaseTemplate item for the @see SelectBaseTemplateForEmailTemplateWizardView
     */
    class BaseEmailTemplateItemForListView extends ItemForListView
    {
        const USE_LINK_CLASS_NAME   = 'use-template';

        const PREVIEW_LINK_CLASS_NAME   = 'preview-template';

        protected $useClass = self::USE_LINK_CLASS_NAME;

        protected $previewClass = self::PREVIEW_LINK_CLASS_NAME;

        public function __construct($data)
        {
            parent::__construct($data);
            if (isset($data['useClass']))
            {
                $this->useClass = $data['useClass'];
            }
            if (isset($data['previewClass']))
            {
                $this->previewClass = $data['previewClass'];
            }
        }

        public function renderItem()
        {
            $content  = null;
            $content .= $this->renderName();
            $content .= $this->renderUseLink();
            $content .= $this->renderPreviewLink();
            return ZurmoHtml::tag('li', array('class' => 'base-template-selection', 'data-value' => $this->model->id), $content);
        }

        protected function renderName()
        {
            $icon = $this->resolveThumbnail();
            $name = ZurmoHtml::tag('h4', array('class' => 'name'), $this->model->name);
            return $icon . $name;
        }

        protected function renderPreviewLink()
        {
            $htmlOptions    = $this->htmlOptionsForPreviewLink();
            $previewLabel   = Zurmo::t('EmailTemplatesModule', 'Preview');
            $previewSpan    = ZurmoHtml::tag('span', array('class' => 'z-label'), $previewLabel);
            return ZurmoHtml::link($previewSpan, '#', $htmlOptions);
        }

        protected function htmlOptionsForPreviewLink()
        {
            return array(
                'class' => 'secondary-button ' . $this->previewClass,
            );
        }

        protected function renderUseLink()
        {
            $htmlOptions = $this->htmlOptionsForUseLink();
            $useLabel    = Zurmo::t('EmailTemplatesModule', 'Use');
            $useSpan     = ZurmoHtml::tag('span', array('class' => 'z-label'), $useLabel);
            return ZurmoHtml::link($useSpan, '#', $htmlOptions);
        }

        protected function htmlOptionsForUseLink()
        {
            return array(
                'class' => 'z-button ' . $this->useClass,
            );

        }

        protected function resolveThumbnail()
        {
            $unserializedData   = CJSON::decode($this->model->serializedData);
            $icon               = ArrayUtil::getArrayValue($unserializedData, 'icon');
            if (!empty($icon))
            {
                return ZurmoHtml::icon($icon);
            }
            else
            {
                return ZurmoHtml::icon('icon-user-template');
            }
        }
    }
?>
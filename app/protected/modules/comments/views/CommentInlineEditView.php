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
     * An inline edit view for a comment.
     *
     */
    class CommentInlineEditView extends InlineEditView
    {
        protected $viewContainsFileUploadElement = true;

        public function __construct(RedBeanModel $model, $controllerId, $moduleId, $saveActionId, $urlParameters, $uniquePageId)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($saveActionId)');
            assert('is_array($urlParameters)');
            assert('is_string($uniquePageId) || $uniquePageId == null');
            $this->model              = $model;
            $this->modelClassName     = get_class($model);
            $this->controllerId       = $controllerId;
            $this->moduleId           = $moduleId;
            $this->saveActionId       = $saveActionId;
            $this->urlParameters      = $urlParameters;
            $this->uniquePageId       = $uniquePageId;
        }

        public function getFormName()
        {
            if ($this->model->id > 0)
            {
                return "comment-inline-edit-form" . $this->model->id;
            }
            return "comment-inline-edit-form";
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'SaveButton', 'label' => "eval:Zurmo::t('CommentsModule', 'Comment')"),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'Files'
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'description', 'type' => 'MentionableTextArea', 'rows' => 2),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'Files',
                                                      'showMaxSize'   => false),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Override to change the editableTemplate to place the label above the input.
         * @see DetailsView::resolveElementDuringFormLayoutRender()
         */
        protected function resolveElementDuringFormLayoutRender(& $element)
        {
            if ($element->getAttribute() == 'description')
            {
                $element->editableTemplate = '<td colspan="{colspan}">{content}{error}</td>';
            }
            elseif ($element instanceOf FilesElement)
            {
                $element->editableTemplate = FilesElement::getEditableTemplateForInlineEdit();
            }
            else
            {
                $element->editableTemplate = '<td colspan="{colspan}">{label}<br/>{content}{error}</td>';
            }
        }

        /**
         * Override to allow the comment thread, if it exists to be refreshed.
         * (non-PHPdoc)
         * @see InlineEditView::renderConfigSaveAjax()
         */
        protected function renderConfigSaveAjax($formName)
        {
            // Begin Not Coding Standard
            return ZurmoHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $formName . '").serialize()',
                    'url'  =>  $this->getValidateAndSaveUrl(),
                    'update' => '#' . $this->uniquePageId,
                    'complete' => "function(XMLHttpRequest, textStatus){
                        //find if there is a comment thread to refresh
                        $('.hiddenCommentRefresh').click();}"
                ));
            // End Not Coding Standard
        }

        protected function doesLabelHaveOwnCell()
        {
            return false;
        }
    }
?>

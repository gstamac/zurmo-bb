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
     * Displays the comments list for related model along with input text area
     */
    class CommentsElement extends Element implements DerivedElementInterface
    {
        /**
         * Relation name for comments in the related model
         * @var string
         */
        protected $relatedModelRelationName;

        /**
         * @var string
         */
        protected $moduleId;

        /**
         * Constructs the element specifying the model and attribute.
         * In the case of needing to show editable information, a form is
         * also provided.
         * @param $form Optional. If supplied an editable element will be rendered.
         * @param $params Can have additional parameters for use
         */
        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            assert('$attribute == null || is_string($attribute)');
            assert('is_array($params)');
            assert('is_string($params["moduleId"])');

            $this->model     = $model;
            $this->attribute = $attribute;
            $this->form      = $form;
            $this->params    = $params;

            if (isset($params['relatedModelRelationName']))
            {
                $this->relatedModelRelationName = $params['relatedModelRelationName'];
            }
            else
            {
                $this->relatedModelRelationName = 'comments';
            }

            $this->moduleId = $params["moduleId"];
        }

        protected function getCommentsWrappingCssClass()
        {
            return 'model-comments-activity';
        }

        /**
         * @throws NotImplementedException
         */
        protected function renderControlEditable()
        {
            throw new NotImplementedException();
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            $wrappingCssClass = $this->getCommentsWrappingCssClass();
            $content  = $this->getFormattedAttributeLabel();
            $content .= $this->renderRelatedModelCommentsContent();
            $content .= $this->renderRelatedModelCreateCommentContent();
            $content  = ZurmoHtml::tag('div', array('class' => $wrappingCssClass), $content);
            return $content;
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        /**
         * @return string
         */
        protected function renderLabel()
        {
            return null;
        }

        /**
         * Renders related model comments
         * @return string
         */
        protected function renderRelatedModelCommentsContent()
        {
            $getParams    = array('relatedModelId'           => $this->model->id,
                                  'relatedModelClassName'    => get_class($this->model),
                                  'relatedModelRelationName' => $this->relatedModelRelationName);
            $pageSize     = 5;
            $commentsData = Comment::getCommentsByRelatedModelTypeIdAndPageSize(get_class($this->model),
                                                                                $this->model->id, ($pageSize + 1));
            $view         = new CommentsForRelatedModelView('default', 'comments', $commentsData, $this->model, $pageSize, $getParams);
            $content      = $view->render();
            return $content;
        }

        /**
         * Renders related model create comment form
         * @return string
         */
        protected function renderRelatedModelCreateCommentContent()
        {
            $content       = '';//ZurmoHtml::tag('h2', array(), Zurmo::t('CommentsModule', 'Add Comment'));
            $comment       = new Comment();
            if ($this->moduleId == 'tasks')
            {
                $uniquePageId  = 'CommentForTaskInlineEditForModelView';;
            }
            else
            {
                $uniquePageId  = 'CommentInlineEditForModelView';
            }

            $redirectUrl   = Yii::app()->createUrl('/' . $this->moduleId . '/default/inlineCreateCommentFromAjax',
                                                    array('id' => $this->model->id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('relatedModelId'           => $this->model->id,
                                   'relatedModelClassName'    => get_class($this->model),
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.

            if ($this->moduleId == 'tasks')
            {
                $inlineView    = new CommentForTaskInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                    $urlParameters, $uniquePageId);
                $htmlOptions = array('id' => 'CommentForTaskInlineEditForModelView');
            }
            else
            {
                $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                    $urlParameters, $uniquePageId);
                $htmlOptions = array('id' => 'CommentInlineEditForModelView');
            }
            $content      .= ZurmoHtml::tag('h2', array(), Zurmo::t('CommentsModule', 'Add Comment'));
            $content      .= $inlineView->render();
            return ZurmoHtml::tag('div', $htmlOptions, $content);
        }

        /**
         * Used for inline comments edit
         * Get view and set parameters for comment inline edit
         * @param $id
         * @param $relatedModelId
         * @param $relatedModelClassName
         * @param $relatedModelRelationName
         * @param null $uniquePageId
         * @return CommentInlineEditView
         * @throws NotFoundException
         */
        public static function getRelatedModelCommentInlineEditView($id, $relatedModelId, $relatedModelClassName, $relatedModelRelationName, $uniquePageId = null)
        {
            $comment = Comment::getById($id);
            $redirectUrl   = Yii::app()->createUrl('/comments/default/inlineEditSave',
                array('id'           => $id,
                      'uniquePageId' => $uniquePageId));
            $urlParameters = array('id' => $comment->id,
                                   'relatedModelId'           => (int)$relatedModelId,
                                   'relatedModelClassName'    => $relatedModelClassName,
                                   'relatedModelRelationName' => $relatedModelRelationName,
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.
            $uniquePageId  = 'CommentInlineEditForExistingModelView' . $uniquePageId;
            if ($relatedModelClassName == 'Task')
            {
                $inlineView    = new CommentForTaskInlineEditView($comment, 'default', 'comments', 'inlineEditSave',
                    $urlParameters, $uniquePageId);
            }
            else
            {
                $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineEditSave',
                    $urlParameters, $uniquePageId);
            }
            return $inlineView;
        }
    }
?>

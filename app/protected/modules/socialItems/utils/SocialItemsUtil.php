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

    /**
     * Helper class for social module processes
     */
    class SocialItemsUtil
    {
        /**
         * Renders and returns string content of summary content for the given model.
         * @param RedBeanModel $model
         * @param mixed $redirectUrl
         * @param string $ownedByFilter
         * @param string $viewModuleClassName
         * @return string content
         */
        public static function renderItemAndCommentsContent(SocialItem $model, $redirectUrl)
        {
            assert('is_string($redirectUrl) || $redirectUrl == null');
            $content  = '<div class="social-item">';
            //todo: use user's avatar (owner)
            $content .= '<em class="'.get_class($model).'"></em>';
            $content .= '<strong>'. DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                    $model->latestDateTime, 'long', null) . '</strong><br/>';
            $content .= ZurmoHtml::tag('span', array(), strval($model->owner) . ' ' . $model->description);
            $content .= static::renderItemFileContent($model);

            $content .= '<div>';
            $content .= static::renderCommentsContent($model);
            $content .= static::renderCreateCommentContent($model);
            $content .= '</div>';

            $content .= '</div>';
            return $content;
        }

        private static function renderItemFileContent(SocialItem $model)
        {
            return ZurmoHtml::tag('span', array(), FileModelDisplayUtil::
                                                   renderFileDataDetailsWithDownloadLinksContent($model, 'files'));
        }

        public static function makeUniquePageIdByModel(SocialItem $model)
        {
            return 'CreateCommentForSocialItem-' . $model->id;
        }

        private static function renderCommentsContent(SocialItem $model)
        {
            $getParams    = array('uniquePageId'             => self::makeUniquePageIdByModel($model),
                                  'relatedModelId'           => $model->id,
                                  'relatedModelClassName'    => 'SocialItem',
                                  'relatedModelRelationName' => 'comments');
            $pageSize     = 5;
            $commentsData = Comment::getCommentsByRelatedModelTypeIdAndPageSize('SocialItem',
                                                                                $model->id, ($pageSize + 1));
            $view         = new CommentsForRelatedModelView('default',
                                                            'comments',
                                                            $commentsData,
                                                            $model,
                                                            $pageSize,
                                                            $getParams,
                                                            self::makeUniquePageIdByModel($model));
            $content      = $view->render();
            return $content;
        }

        private static function renderCreateCommentContent(SocialItem $model)
        {
            $content       = Yii::t('Default', 'Comment');
            $comment       = new Comment();
            $uniquePageId  = self::makeUniquePageIdByModel($model);
            $redirectUrl   = Yii::app()->createUrl('/socialItems/default/inlineCreateCommentFromAjax',
                                                    array('id' => $model->id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('uniquePageId'             => $uniquePageId,
                                   'relatedModelId'           => $model->id,
                                   'relatedModelClassName'    => 'SocialItem',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.

            $inlineView    = new CommentForSocialItemInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                      $urlParameters, $uniquePageId);
            $content      .= $inlineView->render();
            return ZurmoHtml::tag('div', array('id' => $uniquePageId), $content);
        }
    }
?>
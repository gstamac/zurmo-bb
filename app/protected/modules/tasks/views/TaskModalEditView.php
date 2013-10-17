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
     * Modal window for creating and editing a task
     */
    class TaskModalEditView extends SecuredEditView
    {
        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'        => 'SaveButton'),
                            array('type'        => 'TaskDeleteLink'),
                            array('type'        => 'ModalCancelLink',
                                  'htmlOptions' => 'eval:static::resolveHtmlOptionsForCancel()'
                            )
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'ActivityItems',
                        'DerivedExplicitReadWriteModelPermissions',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime'
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_FIRST,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'description', 'type' => 'TextArea'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'status', 'type' => 'TaskStatusDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'requestedByUser', 'type' => 'User'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'owner', 'type' => 'User'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'dueDateTime', 'type' => 'DateTime'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'completed', 'type' => 'CheckBox'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'completedDateTime', 'type' => 'DateTime'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'ActivityItems'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'project', 'type' => 'Project'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null',
                                                      'type' => 'DerivedExplicitReadWriteModelPermissions'),
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
          * @return string
          */
         protected function getNewModelTitleLabel()
         {
             return null;
         }

        /**
         * @return string
         */
        protected static function getFormId()
        {
            return 'task-modal-edit-form';
        }

        /**
         * @return array
         */
        protected static function resolveHtmlOptionsForCancel()
        {
            return array(
                'onclick' => '$("#ModalView").parent().dialog("close");'
            );

        }

        /**
         * Resolves ajax validation option for save button
         * @return array
         */
        protected function resolveActiveFormAjaxValidationOptions()
        {

            $id       = Yii::app()->request->getParam('id');
            $sourceId = null;
            if(GetUtil::resolveParamFromRequest('modalTransferInformation') != null)
            {
                $relationAttributeName  = GetUtil::resolveModalTransferInformationParamFromRequest('relationAttributeName');
                $relationModelId        = GetUtil::resolveModalTransferInformationParamFromRequest('relationModelId');
                $relationModuleId       = GetUtil::resolveModalTransferInformationParamFromRequest('relationModuleId');
                $portletId              = GetUtil::resolveModalTransferInformationParamFromRequest('portletId');
                $uniqueLayoutId         = GetUtil::resolveModalTransferInformationParamFromRequest('uniqueLayoutId');
                $sourceId               = GetUtil::resolveModalTransferInformationParamFromRequest('sourceId');
                $params                 = array('id' => $id);
                $url = Yii::app()->createUrl('tasks/default/modalSaveFromRelation',
                            array_merge(array('relationAttributeName' => $relationAttributeName,
                                              'relationModelId'       => $relationModelId,
                                              'relationModuleId'      => $relationModuleId,
                                              'portletId'             => $portletId,
                                              'uniqueLayoutId'        => $uniqueLayoutId,
                                              'sourceId'              => $sourceId
                                              ), $params
                                            ));
            }
            else
            {
                $url = Yii::app()->createUrl('tasks/default/modalSave', array('id' => $id));
            }
            return array('enableAjaxValidation' => true,
                        'clientOptions' => array(
                            'beforeValidate'    => 'js:$(this).beforeValidateAction',
                            'afterValidate'     => 'js:function(form, data, hasError){
                                                        if(hasError)
                                                        {
                                                            form.find(".attachLoading:first").removeClass("loading");
                                                            form.find(".attachLoading:first").removeClass("loading-ajax-submit");
                                                        }
                                                        else
                                                        {
                                                        ' . $this->renderConfigSaveAjax($this->getFormId(), $url, $sourceId) . '
                                                        }
                                                        return false;
                                                    }',
                            'validateOnSubmit'  => true,
                            'validateOnChange'  => false,
                            'inputContainer'    => 'td'
                        )
            );
        }

        protected function renderConfigSaveAjax($formId, $url, $sourceId)
        {
            // Begin Not Coding Standard
            $options = array(
                'type' => 'POST',
                'data' => 'js:$("#' . $formId . '").serialize()',
                'url'  =>  $url,
                'update' => '#ModalView',
            );
            if($sourceId != null)
            {
                $options['complete'] = "function(XMLHttpRequest, textStatus){
                                        //Refresh underlying KanbanBoard
                                        $.fn.yiiGridView.update('" . $sourceId. "');
                                        }";
            }
            // End Not Coding Standard
            return ZurmoHtml::ajax($options);
        }

        protected function renderRightSideFormLayoutForEdit($form)
        {
            return null;
        }

        public static function getDesignerRulesType()
        {
            return 'TaskModalEditView';
        }
    }
?>
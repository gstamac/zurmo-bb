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
     * Action bar view for the workflow search and list user interface. Provides buttons like create, and links to
     * queues.
     */
    class SecuredActionBarForWorkflowsSearchAndListView extends SecuredActionBarForSearchAndListView
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
                            array('type'  => 'WorkflowCreateLink',
                                'htmlOptions' => array('class' => 'icon-create'),
                            ),
                            array(
                                'type'            => 'WorkflowsLink',
                                'htmlOptions'     => array( 'class' => 'icon-workflows' )
                            ),
                            array(
                                'type'            => EmailTemplatesForWorkflowLinkActionElement::getType(),
                                'htmlOptions'     => array( 'class' => 'icon-email-templates' )
                            ),
                            array(
                                'type'            => 'ByTimeWorkflowInQueuesLink',
                                'htmlOptions'     => array( 'class' => 'icon-by-time-workflow-in-queues' )
                            ),
                            array(
                                'type'            => 'WorkflowMessageInQueuesLink',
                                'htmlOptions'     => array( 'class' => 'icon-by-workflow-message-in-queues' )
                            ),
                            array(
                                'type'            => 'WorkflowManageOrderLink',
                                'htmlOptions'     => array( 'class' => 'icon-by-workflow-manage-order' )
                            ),
                            array('type'          => 'MassDeleteLink',
                                'htmlOptions'     => array( 'class' => 'icon-delete'),
                                                            'listViewGridId' => 'eval:$this->listViewGridId',
                                                            'pageVarName' => 'eval:$this->pageVarName'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Override to only show MassDeleteLink if in the byTime or message queue views
         * @param ActionElement $element
         * @param array $elementInformation
         * @return bool
         */
        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            if ($elementInformation['type'] == 'MassDeleteLink' &&
                'ByTimeWorkflowInQueue'  != get_class($this->model) &&
                'WorkflowMessageInQueue' != get_class($this->model))

            {
                return false;
            }
            return true;
        }
    }
?>
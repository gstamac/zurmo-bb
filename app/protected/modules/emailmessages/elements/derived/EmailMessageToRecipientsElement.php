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
     * Display email message content.
     */
    class EmailMessageToRecipientsElement extends Element implements DerivedElementInterface
    {
        protected function renderControlNonEditable()
        {
            assert('$this->model instanceof EmailMessage');
            return Yii::app()->format->html(EmailMessageMashableActivityRules::
                        getRecipientsContent($this->model->recipients, EmailMessageRecipient::TYPE_TO));
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof EmailMessage');
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModelElement");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.MultiSelectAutoComplete', array(
                'name'        => $this->getNameForIdField(),
                'id'          => $this->getIdForIdField(),
                'jsonEncodedIdsAndLabels'   => CJSON::encode($this->getExistingPeopleRelationsIdsAndLabels()),
                'sourceUrl'   => Yii::app()->createUrl('users/default/autoCompleteForMultiSelectAutoComplete'),
                'htmlOptions' => array(
                    'disabled' => $this->getDisabledValue(),
                    ),
                'hintText' => Yii::t('Default', 'Type a User\'s name'),
                'onAdd'    => $this->getOnAddContent(),
                'onDelete' => $this->getOnDeleteContent(),
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['ModelElement'];
            return $content;
        }

        protected function renderLabel()
        {
            return Yii::t('Default', 'To');
        }

        public static function getDisplayName()
        {
            return Yii::t('Default', 'To Recipients');
        }

        public static function getModelAttributeNames()
        {
            return array();
        }

        protected function getNameForIdField()
        {
                return 'ToRecipientsForm[itemIds]';
        }

        protected function getIdForIdField()
        {
            return 'ToRecipientsForm_item_ids';
        }

        protected function getOnAddContent()
        {
        }

        protected function getOnDeleteContent()
        {
        }

        protected function getExistingPeopleRelationsIdsAndLabels()
        {
            $existingPeople = array(
                                array(  'id'       => $this->model->owner->getClassId('Item'),
                                        'name'     => strval($this->model->owner),
                                        'readonly' => true));
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Contact');
            /*
            foreach ($this->model->conversationParticipants as $participant)
            {
                try
                {
                    $contact = $participant->person->castDown(array($modelDerivationPathToItem));
                    if (get_class($contact) == 'Contact')
                    {
                        $existingPeople[] = array('id' => $contact->getClassId('Item'),
                                                    'name' => strval($contact));
                    }
                    else
                    {
                        throw new NotFoundException();
                    }
                }
                catch (NotFoundException $e)
                {
                    $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
                    try
                    {
                        $user = $participant->person->castDown(array($modelDerivationPathToItem));
                        //Owner is always added first.
                        if (get_class($user) == 'User' && $user->id != $this->model->owner->id)
                        {
                            $readOnly = false;
                            $existingPeople[] = array('id'       => $user->getClassId('Item'),
                                                      'name'     => strval($user),
                                                      'readonly' => $readOnly);
                        }
                    }
                    catch (NotFoundException $e)
                    {
                        //This means the item is not a recognized or expected supported model.
                        throw new NotSupportedException();
                    }
                }
            }
             */
            return $existingPeople;
        }
    }
?>
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
     * Override to accomodate numbers coming from the database. If the value is null for the phone, the value
     * will show as 0. This is only an issue if you remove an existing phone value from an attribute.
     * @see http://groups.google.com/group/redbeanorm/browse_thread/thread/e6a0a9d29838d973/90d12a0146544a0b
     */
    class PhoneElement extends TextElement
    {
        protected function renderControlEditable()
        {
            assert('empty($this->model->{$this->attribute}) || is_string($this->model->{$this->attribute}) ||
            is_integer($this->model->{$this->attribute})');
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            $htmlOptions['disabled'] = $this->getDisabledValue();
            return $this->form->textField($this->model, $this->attribute, $htmlOptions);
        }

        protected function renderControlNonEditable()
        {
            $content = Yii::app()->format->text($this->model->{$this->attribute});
            $id = $this->getEditableInputId($this->attribute, 'CallableItem');
            return Yii::app()->phoneHelper->resolvePhoneNumberContentForDialing($content, $id, $this->model);
        }
    }
?>

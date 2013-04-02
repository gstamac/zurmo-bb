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
     * Test dateTime attribute types for all various operatorTypes and important scenarios
     *
     * #1 - Test each operator type against attribute on model
     */
    class WorkflowTriggersUtilForDateTimeTest extends WorkflowTriggersUtilBaseTest
    {

        public function testTimeTriggerBeforeSaveEquals()
        {
            $workflow = self::makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, 500);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'something';
            //At this point the model has not changed, so it should not fire
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime   = '2007-07-01 00:02:00';
            //At this point the model has changed so it should fire
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            //Even though it changed, it changed to null, so it should not fire
            $model->date   = null;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $model->dateTime   = '2007-07-03 00:02:00';
            $model->dateTime   = '0000-00-00 00:00:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTimeTriggerBeforeSaveEquals
         */
        public function testTimeTriggerBeforeSaveEqualsWithANonTimeTrigger()
        {
            $workflow = self::makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, 500);
            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'lastName';
            $trigger->value                       = 'Green';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'something';
            //At this point the value has changed, but the normal trigger is not satisfied
            $model->dateTime   = '2007-07-01 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //Now the normal trigger is satisfied
            $model->lastName = 'Green';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTimeTriggerBeforeSaveEqualsWithANonTimeTrigger
         */
        public function testTriggerBeforeSaveOn()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'On', '2007-07-01 00:02:00');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->dateTime = '2007-07-01 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '2007-07-02 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '2007-07-01 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveOn
         */
        public function testTriggerBeforeSaveBetween()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'Between', '2007-07-01 00:02:00',
                        'WorkflowsTestModule', 'WorkflowModelTestItem', '2007-07-06');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->dateTime = '2007-07-02 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '2007-07-10 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '2007-07-03 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBetween
         */
        public function testTriggerBeforeSaveAfter()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'After', '2007-07-01 00:02:00');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->dateTime     = '2007-07-22 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime     = '2007-06-28 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model           = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime     = '2007-09-24 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveAfter
         */
        public function testTriggerBeforeSaveBefore()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'Before', '2007-07-01 00:02:00');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->dateTime = '2007-06-03 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '2007-07-05 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '2007-06-01 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBefore
         */
        public function testTriggerBeforeSaveBecomesOn()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'Becomes On', '2007-07-01 00:02:00');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->dateTime = '2007-07-01 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->dateTime = '2007-07-05 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->dateTime = '2007-07-03 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'becomes' '2007-07-01 00:02:00'
            $model->dateTime = '2007-07-01 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBecomesOn
         */
        public function testTriggerBeforeSaveWasOn()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'Was On', '2007-07-01 00:02:00');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->dateTime = '2007-07-01 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->dateTime = '2007-06-03 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->dateTime = '2007-07-01 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'was' '2007-07-01 00:02:00' and is now '2007-06-03 00:02:00'
            $model->dateTime = '2007-06-03 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveWasOn
         */
        public function testTriggerBeforeSaveChanges()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'Changes', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->dateTime = '2007-06-03 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->dateTime = '2007-07-01 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveChanges
         */
        public function testTriggerBeforeSaveDoesNotChange()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'Does Not Change', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->dateTime = '2007-06-03 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->dateTime = '2007-07-01 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotChange
         */
        public function testTriggerBeforeSaveIsEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'Is Empty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '2007-06-03 00:02:00';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = null;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveIsEmpty
         */
        public function testTriggerBeforeSaveIsNotEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('dateTime', 'Is Not Empty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '2007-06-03 00:02:00';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = null;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->dateTime = '';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }
    }
?>
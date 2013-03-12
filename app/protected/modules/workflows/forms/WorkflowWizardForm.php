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
     * Base class for all workflow wizard form models.  Manages the interaction between the Workflow object and the
     * user interface.
     */
    abstract class WorkflowWizardForm extends WizardForm
    {
        //todO: refactor this class with ReportWizardForm into a base class that we can move methods and properties into
        const MODULE_VALIDATION_SCENARIO            = 'ValidateForModule';

        const TIME_TRIGGER_VALIDATION_SCENARIO      = 'ValidateForTimeTrigger';

        const TRIGGERS_VALIDATION_SCENARIO          = 'ValidateForTriggers';

        const ACTIONS_VALIDATION_SCENARIO           = 'ValidateForActions';

        const EMAIL_ALERTS_VALIDATION_SCENARIO      = 'ValidateForEmailAlerts';

        const GENERAL_DATA_VALIDATION_SCENARIO      = 'ValidateForGeneralData';

        public $description;

        /**
         * @var string
         */
        public $moduleClassName;

        /**
         * Name of report
         * @var string
         */
        public $name;

        /**
         * When to trigger the workflow Workflow::TRIGGER_ON_NEW, Workflow::TRIGGER_ON_EXISTING,
         * Workflow::TRIGGER_ON_NEW_AND_EXISTING
         * @var string
         */
        public $triggerOn;

        /**
         * Type of workflow
         * @var string
         */
        public $type;

        /**
         * @var string
         */
        public $triggersStructure;

        /**
         * Corresponds to the selection of a time trigger attribute for By-time workflow rules.  This shows in a dropdown
         * for picking from the available attributes.
         * @var string
         */
        public $timeTriggerAttribute;

        /**
         * @var object TimeTriggerForWorkflowForm
         */
        public $timeTrigger;

        /**
         * @var array
         */
        public $triggers     = array();

        /**
         * @var array
         */
        public $actions      = array();

        public $emailAlerts  = array();

        public function rules()
        {
            return array(
                array('description', 	      'type',              'type' => 'string'),
                array('name', 			      'type',        	   'type' => 'string'),
                array('name', 			      'length',   		   'max' => 64),
                array('name', 			      'required', 		   'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
                array('moduleClassName',      'type',     		   'type' => 'string'),
                array('moduleClassName',      'length',            'max' => 64),
                array('moduleClassName',      'required', 		   'on' => self::MODULE_VALIDATION_SCENARIO),
                array('triggerOn', 		      'type',     		   'type' => 'string'),
                array('triggerOn', 			  'length',   		   'max' => 15),
                array('triggerOn', 			  'required', 		   'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
                array('type', 		          'type',     		   'type' => 'string'),
                array('type', 			      'length',   		   'max' => 15),
                array('type', 			      'required'),
                array('timeTrigger', 		  'validateTimeTrigger', 'on' => self::TIME_TRIGGER_VALIDATION_SCENARIO),
                array('triggersStructure', 	  'validateTriggersStructure', 'on' => self::TRIGGERS_VALIDATION_SCENARIO),
                array('triggers',             'validateTriggers',  'on' => self::TRIGGERS_VALIDATION_SCENARIO),
                array('actions',              'validateActions',   'on' => self::ACTIONS_VALIDATION_SCENARIO),
                array('emailAlerts',          'validateEmailAlerts', 'on' => self::EMAIL_ALERTS_VALIDATION_SCENARIO),
                array('timeTriggerAttribute', 'type',                'type' => 'string'),
            );
        }

        public function attributeLabels()
        {
            return array(
                'name' => Zurmo::t('WorkflowsModule', 'Name'),
            );
        }

        /**
         * @return bool
         */
        public function validateTimeTrigger()
        {
            $passedValidation = true;
            if($this->timeTrigger != null)
            {
                $validated = $this->timeTrigger->validate();
                if(!$validated)
                {
                    foreach($this->timeTrigger->getErrors() as $attribute => $error)
                    {
                        $this->addError( ComponentForWorkflowForm::TYPE_TIME_TRIGGER . '_' . $attribute, $error);
                    }
                    $passedValidation = false;
                }
            }
            return $passedValidation;
        }

        /**
         * @return bool
         */
        public function validateTriggers()
        {
            return $this->validateComponent(ComponentForWorkflowForm::TYPE_TRIGGERS, 'triggers');
        }

        /**
         * Validates if the trigger structure is valid.
         */
        public function validateTriggersStructure()
        {
            if(count($this->triggers) > 0)
            {
                //todo: this isn't for SQL, but it still uses same logic, so we have to refactor this check.
                if(null != $errorMessage = SQLOperatorUtil::
                           resolveValidationForATemplateSqlStatementAndReturnErrorMessage($this->triggersStructure,
                           count($this->triggers)))
                {
                    $this->addError('triggersStructure', $errorMessage);
                }
            }
        }

        /**
         * @return bool
         */
        public function validateActions()
        {
            return $this->validateComponent(ComponentForWorkflowForm::TYPE_ACTIONS, 'actions');
        }

        /**
         * @return bool
         */
        public function validateEmailAlerts()
        {
            return $this->validateComponent(ComponentForWorkflowForm::TYPE_EMAIL_ALERTS, 'emailAlerts');
        }

        /**
         * @return array
         */
        public function getTriggerOnDataAndLabels()
        {
            $baseCurrencyCode = Yii::app()->currencyHelper->getBaseCode();
            return array(
                Workflow::TRIGGER_ON_NEW              => Zurmo::t('WorkflowsModule', 'New Records Only'),
                Workflow::TRIGGER_ON_EXISTING         => Zurmo::t('WorkflowsModule', 'Existing Records Only'),
                Workflow::TRIGGER_ON_NEW_AND_EXISTING => Zurmo::t('WorkflowsModule', 'Both New and Existing Records'),
            );
        }
    }
?>
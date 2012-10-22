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

    abstract class ReportWizardView extends View
    {
        protected $model;

        abstract protected function registerClickFlowScript();

        abstract protected function renderContainingViews(ZurmoActiveForm $form);

        abstract protected function renderConfigSaveAjax($formName);

        public function __construct(ReportWizardForm $model)
        {
            $this->model = $model;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public static function getFormId()
        {
            return 'edit-form';
        }

        protected function renderContent()
        {
            $content  = $this->renderForm();
            $this->registerScripts();
            return $content;
        }

        protected function renderForm()
        {
            $content  = '<div class="wrapper">';
            $content .= $this->renderTitleContent();
            $content .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'ZurmoActiveForm',
                                                                array('id'                   => static::getFormId(),
                                                                      'action'               => $this->getFormActionUrl(),
                                                                      'enableAjaxValidation' => true,
                                                                      'clientOptions'        => $this->getClientOptions())
                                                                );
            $content .= $formStart;
            $content .= static::renderValidationScenarioInputContent();
            $content .= $this->renderContainingViews($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div></div>';
            return $content;
        }

        protected function getClientOptions()
        {
            return array(
                        'validateOnSubmit'  => true,
                        'validateOnChange'  => false,
                        'beforeValidate'    => 'js:beforeValidateAction',
                        'afterValidate'     => 'js:afterValidateAjaxAction',
                        'afterValidateAjax' => $this->renderConfigSaveAjax(static::getFormId()),
                    );
        }

        protected function getFormActionUrl()
        {
            return Yii::app()->createUrl('reports/default/save', array('type' => $this->model->type));
        }

        protected function registerScripts()
        {
            $this->registerClickFlowScript();
        }

        protected static function renderValidationScenarioInputContent()
        {
            $idInputHtmlOptions  = array('id' => static::getValidationScenarioInputId());
            $hiddenInputName     = 'validationScenario';
            return ZurmoHtml::hiddenField($hiddenInputName, static::getStartingValidationScenario(), $idInputHtmlOptions);
        }

        protected static function getStartingValidationScenario()
        {
            return ModuleForReportWizardView::VALIDATION_SCENARIO;
        }

        protected static function getValidationScenarioInputId()
        {
            return 'componentType';
        }
    }
?>
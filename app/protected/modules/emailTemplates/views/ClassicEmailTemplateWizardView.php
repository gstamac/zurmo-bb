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

    class ClassicEmailTemplateWizardView extends EmailTemplateWizardView
    {
        /**
         * @return string
         */
        public function getTitle()
        {
            $title = parent::getTitle() .  ' - ';
            if($this->model->builtType == EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY)
            {
                $title .= Zurmo::t('EmailTemplatesModule', 'Plain Text');
            }
            elseif($this->model->builtType == EmailTemplate::BUILT_TYPE_PASTED_HTML)
            {
                $title .= Zurmo::t('EmailTemplatesModule', 'HTML');
            }
            else
            {
                throw new NotSupportedException();
            }
            return $title;
        }

        protected function resolveContainingViews(WizardActiveForm $form)
        {
            $views              = array();
            $views[]            = new GeneralDataForEmailTemplateWizardView($this->model, $form);
            $views[]            = new ContentForEmailTemplateWizardView($this->model, $form, true);
            return $views;
        }

        protected function renderGeneralDataNextPageLinkScript($formName)
        {
            return "
                    if (linkId == '" . GeneralDataForEmailTemplateWizardView::getNextPageLinkId() . "')
                    {
                        " . $this->getSaveAjaxString($formName, false, GeneralDataForEmailTemplateWizardView::resolveAdditionalAjaxOptions($formName)) . "
                        $('#" . static::getValidationScenarioInputId() . "').val('" .
                                        BuilderEmailTemplateWizardForm::PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO. "');
                        $('#GeneralDataForEmailTemplateWizardView').hide();
                        " . $this->renderTreeViewAjaxScriptContentForMergeTagsView() . "
                        $('#ContentForEmailTemplateWizardView').show();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('100%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').next().addClass('current-step');
                    }
                    $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading');
                    $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading-ajax-submit');
                    $('#" . $formName . "').find('.attachLoadingTarget').removeClass('attachLoadingTarget');
                    ";
        }

        protected function renderPreGeneralDataNextPageLinkScript($formName)
        {
            return "
                    if (linkId == '" . ContentForEmailTemplateWizardView::getNextPageLinkId() . "')
                    {
                        " . $this->getSaveAjaxString($formName, true, ContentForEmailTemplateWizardView::resolveAdditionalAjaxOptions($formName)) . "
                    }
                    ";
        }

        protected function registerPostGeneralDataPreviousLinkScript()
        {
            Yii::app()->clientScript->registerScript('clickflow.contentPreviousLink', "
                $('#" . ContentForEmailTemplateWizardView::getPreviousPageLinkId() . "').unbind('click').bind('click', function()
                    {
                        $('#" . static::getValidationScenarioInputId() . "').val('" . BuilderEmailTemplateWizardForm::GENERAL_DATA_VALIDATION_SCENARIO . "');
                        $('#GeneralDataForEmailTemplateWizardView').show();
                        $('#ContentForEmailTemplateWizardView').hide();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('50%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step').prev().addClass('current-step');
                        return false;
                    }
                );");
        }
    }
?>
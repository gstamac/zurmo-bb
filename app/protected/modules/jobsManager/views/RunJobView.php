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
     * View for running a job from the browser.
     */
    class RunJobView extends View
    {
        protected $controllerId;

        protected $moduleId;

        protected $type;

        protected $timeLimit;

        protected $messageLoggerClassName;

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param string $type
         * @param int $timeLimit
         */
        public function __construct($controllerId, $moduleId, $type, $timeLimit, $messageLoggerClassName)
        {
            assert('is_string($controllerId) && $controllerId != ""');
            assert('is_string($moduleId) && $moduleId != ""');
            assert('is_string($type)');
            assert('is_int($timeLimit)');
            assert('is_string($messageLoggerClassName)');
            $this->controllerId = $controllerId;
            $this->moduleId     = $moduleId;
            $this->type         = $type;
            $this->timeLimit    = $timeLimit;
            $this->messageLoggerClassName    = $messageLoggerClassName;
        }

        protected function getJobLabel()
        {
            $jobClassName = $this->type . 'Job';
            return $jobClassName::getDisplayName();
        }

        protected function renderContent()
        {
            $this->registerRunJobInBackgroundScript();
            $imagePath = Yii::app()->themeManager->baseUrl . '/default/images/ajax-loader.gif';
            $progressBarImageContent = ZurmoHtml::image($imagePath, 'Progress Bar');
            $content  = '<div class="wrapper">';
            $content .= ZurmoHtml::tag('h1', array(), $this->getJobLabel());
            $content .= '<div class="left-column full-width">';
            $content .= '<div id="complete-table" style="display:none;">';
            $content .= ZurmoHtml::tag('h3', array(), Zurmo::t('JobsManagerModule', 'The job has completed running.'));
            $content .= '</div>';
            $content .= '<div id="progress-table" class="progress-bar">';
            $content .= Zurmo::t('JobsManagerModule', 'Job is running. Please wait.');
            $content .= '<br/>';
            $content .= $progressBarImageContent;
            $content .= '</div>';
            $content .= '<div id="logging-table">';
            $content .= ZurmoHtml::tag('h3', array(), Zurmo::t('JobsManagerModule', 'Job Output:'));
            $content .= ZurmoHtml::tag('ol', array(), '');
            $content .= '</div>';
            $content .= $this->renderButtonsContent();
            $content .= '</div>';
            $content .= '</div>';
            return $content;
        }

        protected function renderButtonsContent()
        {
            $jobClassName           = $this->type . "Job";
            $messageLoggerClassName = $jobClassName::getDefaultMessageLogger();
            $runAgainUrl   = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/runJob/',
                                                   array('type' => $this->type, 'timeLimit' => $this->timeLimit,
                                                         'messageLoggerClassName' => $messageLoggerClassName));
            $jobManagerUrl = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/list/');
            $content  = ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('JobsManagerModule', 'Run Job Again')),
                                        $runAgainUrl, array('class' => 'z-button'));
            if (Yii::app()->jobQueue->isEnabled())
            {
                $queueJobUrl   = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/queueJob/',
                                 array('type' => $this->type));
                $content .= ZurmoHtml::link(ZurmoHtml::wrapLabel(Yii::app()->jobQueue->getQueueJobLabel()),
                                            $queueJobUrl, array('class' => 'secondary-button'));
            }
            $content .= ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('JobsManagerModule', 'Job Manager')),
                                        $jobManagerUrl, array('class' => 'secondary-button'));
            $content = '<div class="float-bar"><div class="view-toolbar-container clearfix dock"><div class="form-toolbar">'
                       . $content . '</div></div></div>';
            return $content;
        }

        protected function registerRunJobInBackgroundScript()
        {
            $jobUrl   = Yii::app()->createUrl('jobsManager/default/runAjaxJob/',
                array('type' => $this->type, 'timeLimit' => $this->timeLimit, 'messageLoggerClassName' => $this->messageLoggerClassName));
            // Begin Not Coding Standard
            $script = <<<EOD
    $(function()
    {
        $.ajax(
        {
            url : '$jobUrl',
            type : 'GET',
            data : {
            },
            dataType: 'html',
            success : function(data)
            {
                $('#logging-table ol').append(data);
                $("#progress-table").hide();
                $("#complete-table").show();
            },
            error : function(jqXHR, textStatus, errorThrown)
            {
                $('#logging-table ol').append('The application has encountered an unknown error. Please check logs for more details!');
            }
        }
        );
    }
    );
EOD;
            // End Not Coding Standard
            Yii::app()->getClientScript()->registerScript('RunJobInBackgroundScript', $script);
        }
    }
?>

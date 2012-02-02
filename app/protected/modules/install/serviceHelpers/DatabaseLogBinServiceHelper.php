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
     * Check settings related to log_bin global option.
     * It is required log_bin to be turned off, or if it is turned on, then
     * option log_bin_trust_function_creators must be turned on too.
     * For more details: http://dev.mysql.com/doc/refman/5.1/en/binary-log.html
     */
    class DatabaseLogBinServiceHelper extends ServiceHelper
    {
        protected $required = true;
        protected $form;

        public function __construct($form)
        {
            assert('$form instanceof InstallSettingsForm');
            $this->form = $form;
        }

        protected function checkService()
        {
            $passed = true;
            $logBinValue = null;
            if (!InstallUtil::checkDatabaseLogBinValue('mysql',
                                                       $this->form->databaseHostname,
                                                       $this->form->databaseUsername,
                                                       $this->form->databasePassword,
                                                       $this->minimumUploadRequireBytes,
                                                       $logBinValue))
            {
                $logBinTrustFunctionCreatorsValue = null;

                if (!InstallUtil::checkDatabaseLogBinTrustFunctionCreatorsValue(
                                                       'mysql',
                                                       $this->form->databaseHostname,
                                                       $this->form->databaseUsername,
                                                       $this->form->databasePassword,
                                                       $this->minimumUploadRequireBytes,
                                                       $logBinTrustFunctionCreatorsValue))
                {
                        $this->message  = Yii::t('Default', 'Database log_bin option is turned on and log_bin_trust_function_creators is turned off.') . ' ';
                        $this->message .= Yii::t('Default', 'You must either to turn of log_bin, or to le log_bin turned on, but to turn on log_bin_trust_function_creators option.');
                        $passed = false;
                }
                else
                {
                    $this->message = Yii::t('Default', 'Database log_bin option is turned on and log_bin_trust_function_creators is turned on, and pass requirements.');
                    $passed = true;
                }
            }
            else
            {
                $this->message = Yii::t('Default', 'Database log_bin option is turned off and pass requirements.');
            }
            return $passed;
        }
    }
?>
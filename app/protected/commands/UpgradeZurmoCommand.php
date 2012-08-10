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
     * UpdateZurmoCommand update Zurmo version.
     */
    class UpgradeZurmoCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc updgradeZurmo <username> <action>

    DESCRIPTION
      This command runs a Zurmo upgrade.

    PARAMETERS
     * username: username to log in as and run the import processes. Typically 'super'.
                  This user must be a super administrator.
     * action: define upgrade phase(possible options: "runPart1" or "runPart2")
EOD;
    }

    /**
     * Execute the action.
     * @param array command line parameters specific for this command
     */
    public function run($args)
    {
        set_time_limit(0);
        if (!isset($args[0]))
        {
            $this->usageError('A username must be specified.');
        }
        try
        {
            Yii::app()->user->userModel = User::getByUsername($args[0]);
        }
        catch (NotFoundException $e)
        {
            $this->usageError('The specified username does not exist.');
        }
        $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
        if (!$group->users->contains(Yii::app()->user->userModel))
        {
            $this->usageError('The specified user is not a super administrator.');
        }

        if (!isset($args[1]))
        {
            $this->usageError('You must specify action.');
        }
        else
        {
            $upgradeStep = $args[1];
        }

        if ($upgradeStep != 'runPart1' && $upgradeStep != 'runPart2')
        {
            $this->usageError('Invalid step/action. Valid values are "runPart1" and "runPart2".');
        }

        try
        {
            $template        = "{message}\n";
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(0);
            $messageLogger = new MessageLogger($messageStreamer);

            if ($upgradeStep == 'runPart1')
            {
                $messageLogger->addInfoMessage("Starting zurmo upgrade process.");
                $this->runPart1($messageLogger);
                $messageLogger->addInfoMessage(Yii::t('Default', 'Zurmo upgrade phase 1 completed.'));
                $messageLogger->addInfoMessage(Yii::t('Default', 'Please execute next command: "./zurmoc upgradeZurmo super reloadAppAndCompleteUpgrade" to complete upgrade process.'));
            }
            elseif ($upgradeStep == 'runPart2')
            {
                if (UpgradeUtil::isUpgradeStateValid())
                {
                    $messageLogger->addInfoMessage("Starting zurmo upgrade process - phase 2.");
                    $this->runPart2($messageLogger);
                    $messageLogger->addInfoMessage(Yii::t('Default', 'Zurmo upgrade completed.'));
                    UpgradeUtil::unsetUpgradeState();
                }
                else
                {
                    $message = 'Upgrade state is older then one day, so please run phase one of upgrade first.';
                    throw new NotSupportedException($message);
                }
            }
        }
        catch (Exception $e)
        {
            $messageLogger->addErrorMessage(Yii::t('Default', 'An error occur during upgrade: ') . $e->getMessage());
            UpgradeUtil::unsetUpgradeState();
        }
    }

    protected function runPart1($messageLogger)
    {
        $messageLogger->addInfoMessage(Yii::t('Default', 'This is Zurmo upgrade process. Please backup files/database before you continue.'));

        $message = "Are you sure you want to upgrade Zurmo?";
        $confirm = $this->confirm($message);

        if ($confirm)
        {
            UpgradeUtil::runPart1($messageLogger);
        }
        else
        {
            $messageLogger->addInfoMessage(Yii::t('Default', 'Upgrade process halted.'));
        }
    }

    protected function runPart2($messageLogger)
    {
            UpgradeUtil::runPart2($messageLogger);
    }

    /**
     * Prompt user by Yes or No
     * @param string $message an optional message to show at prompting.
     * @param bool $printYesNo If is true shows " [yes|no] " at prompting
     * @return bool True if user respond Yes, otherwise, return False
     */
    public function confirm($message = null, $printYesNo = true)
    {
        if($message !== null)
        {
            echo $message;
        }
        if($printYesNo)
        {
            echo ' [yes|no] ';
        }
        return !strncasecmp(trim(fgets(STDIN)), 'y', 1);
    }
}
?>
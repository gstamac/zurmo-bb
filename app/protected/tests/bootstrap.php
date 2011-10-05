<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    require_once('testRoots.php');

    chdir(COMMON_ROOT);

    if (!is_file(INSTANCE_ROOT . '/protected/config/debugTest.php'))
    {
        copy(INSTANCE_ROOT . '/protected/config/debugDIST.php', INSTANCE_ROOT . '/protected/config/debugTest.php');
    }
    if (!is_file(INSTANCE_ROOT . '/protected/config/perInstanceTest.php'))
    {
        copy(INSTANCE_ROOT . '/protected/config/perInstanceDIST.php', INSTANCE_ROOT . '/protected/config/perInstanceTest.php');
    }

    $debug          = INSTANCE_ROOT . '/protected/config/debugTest.php';

    $yiit   = COMMON_ROOT   . "/../yii/framework/yiit.php";
    $config = INSTANCE_ROOT . "/protected/config/test.php";

    require_once(COMMON_ROOT   . "/version.php");
    require_once($debug);
    require_once($yiit);
    require_once(COMMON_ROOT . '/protected/extensions/zurmoinc/framework/components/WebApplication.php');
    require_once(COMMON_ROOT . '/protected/tests/WebTestApplication.php');
    Yii::createApplication('WebTestApplication', $config);
?>

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

    class GameBadgeTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetGameBadgeById()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            $gameBadge             = new GameBadge();
            $gameBadge->person     = $user;
            $gameBadge->type       = 'SomeType';
            $gameBadge->grade      = 1;
            $this->assertTrue($gameBadge->save());
            $id = $gameBadge->id;
            unset($gameBadge);
            $gameBadge = GameBadge::getById($id);
            $this->assertEquals('SomeType',  $gameBadge->type);
            $this->assertEquals(1,           $gameBadge->grade);
            $this->assertEquals($user,       $gameBadge->person);
        }

        /**
         * @depends testCreateAndGetGameBadgeById
         */
        public function testGetAllByPersonIndexedByType()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $gameBadges                 = GameBadge::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(1, count($gameBadges));
            $gameBadge = $gameBadges['SomeType'];
            $this->assertEquals('SomeType',                    $gameBadge->type);
            $this->assertEquals(1,                             $gameBadge->grade);
            $this->assertEquals(Yii::app()->user->userModel,   $gameBadge->person);
        }

        /**
         * @depends testGetAllByPersonIndexedByType
         */
        public function testProcessBonusPoints()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');

            //Testing a badge that does not give bonus points.
            $gameBadge             = new GameBadge();
            $gameBadge->person     = Yii::app()->user->userModel;
            $gameBadge->type       = 'SomeTypeZ';
            $gameBadge->grade      = 1;
            $this->assertTrue($gameBadge->save());
            GameBadge::processBonusPoints($gameBadge, Yii::app()->user->userModel);

            //todo: test a badge that does give bonus points.
        }
    }
?>

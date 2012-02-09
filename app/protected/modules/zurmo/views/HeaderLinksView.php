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

    class HeaderLinksView extends View
    {
        protected function renderContent()
        {
            $metadata = MenuUtil::getAccessibleHeaderMenuByCurrentUser();
            foreach ($metadata as $menuItem)
            {
                $links[$menuItem['label']] = Yii::app()->createUrl($menuItem['route']);
            }

            $content  = '<div><ul>';
            $content .= static::renderNotificationsLinkContent();
            $content .= '<li>' . Yii::t('Default', 'Welcome') . ', <b>' . Yii::app()->user->firstName . '</b></li>';
            foreach ($links as $label => $link)
            {
                $content .= "<li><a href=\"$link\">$label</a></li>";
            }
            $content .= '</ul></div>';
            return $content;
        }

        protected function renderNotificationsLinkContent()
        {
            $label    = Yii::t('Default', 'Notifications');
            $link     = Yii::app()->createUrl('notifications/default');
            $content  = null;
            $count    = Notification::getUnreadCountByUser(Yii::app()->user->userModel);
            if ($count > 0)
            {
                $content  = ' <span class="notifications-link-unread"> ' . Yii::t('Default', '{count} unread', array('{count}' => $count)) . '</span>&#160;';
            }
            $content  .= "<a href=\"$link\">$label</a>";
            return '<li><span class="notifications-link">' . $content . '</span></li>';
        }
    }
?>

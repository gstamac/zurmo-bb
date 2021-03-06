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
     * The base View for a modal view.
     * Modal views are inline display popups
     */
    class ModalView extends View
    {
        /**
         * TODO
         */
        public function __construct(CController $controller, View $view)
        {
            $this->view       = $view;
            $this->controller = $controller;
        }

        public function render()
        {
            $content = parent::render();
            Yii::app()->clientScript->scriptMap = array(
                'jquery-ui.min.js' => false,
            );
            Yii::app()->getClientScript()->render($content);
            return $content;
        }

        /**
         * Renders content for a view.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            return $this->view->render();
        }

        public static function getAjaxOptionsForModalLink($title, $containerId = 'modalContainer', $height = 'auto',
                                                          $width = 600, $position = array('my'=>'center top+25','at'=>'center top+25'), 
                                                          $class = "''", $extraCloseScript = null)// Not Coding Standard
        {
            assert('is_string($containerId)');
            assert('is_string($title)');
            assert('$height == "auto" || is_int($height)');
            assert('is_int($width)');
            assert('is_string($position) || is_array($position)');
            assert('is_string($class) || $class == null');
            assert('is_string($extraCloseScript) || $extraCloseScript == null');
            return array(
                    'beforeSend' => static::getAjaxBeforeSendOptionForModalLinkContent($title, $containerId,
                                                                                       $height, $width, $position,
                                                                                       $class, $extraCloseScript),
                    'update'     => '#' . $containerId);
        }

        public static function getAjaxBeforeSendOptionForModalLinkContent($title,
                                                                          $containerId = 'modalContainer',
                                                                          $height = 'auto',
                                                                          $width = 600,
                                                                          $position = array('my'=>'center top+25','at'=>'center top+25'), // Not Coding Standard
                                                                          $class = "''", // Not Coding Standard
                                                                          $extraCloseScript = null)
        {
            assert('is_string($containerId)');
            assert('is_string($title)');
            assert('$height == "auto" || is_int($height)');
            assert('is_int($width)');
            assert('is_string($position) || is_array($position)');
            assert('is_string($class) || $class == null');
            assert('is_string($extraCloseScript) || $extraCloseScript == null');
            if ($height == 'auto')
            {
                $heightContent = "'auto'";
            }
            else
            {
                $heightContent = $height;
            }
            if (is_array($position))
            {
                $position = CJSON::encode($position);
            }
            else
            {
                $position = "'" . $position . "'";
            }
            $modalTitle = CJavaScript::quote($title);
            
            // Begin Not Coding Standard
            return "js:function(){
                jQuery('#{$containerId}').html('');
                $(this).makeLargeLoadingSpinner(true, '#{$containerId}');
                jQuery('#{$containerId}').dialog({
                    'title' : '{$modalTitle}',
                    'autoOpen' : true,
                    'modal' : true,
                    'position' : {$position},
                    'dialogClass' : {$class},
                    'height' : {$heightContent},
                    'closeText':'',
                    'open': function( event, ui ) {
                        jQuery('#{$containerId}').parent().addClass('openingModal');
                        // prevent dialog scrolls to top under Chrome, IE
                        $('.ui-dialog-titlebar-close').mousedown(function(ev) {
                            $('#{$containerId}').dialog('close');
                        });
                        $('.ui-dialog .ui-dialog-content').css('height', 'auto');
                        $('.ui-dialog .ui-dialog-content').css('display', 'block');
                        $('.ui-dialog-titlebar-close .ui-button-text').remove();
                        $('#{$containerId}').dialog('moveToTop');
                        var zIndex = jQuery('#{$containerId}').parent().css('zIndex');
                        zIndex = zIndex - 1;
                        $('.ui-widget-overlay').css('zIndex', zIndex);
                    },
                    'close': function( event, ui ) { jQuery('#{$containerId}').parent().removeClass('openingModal');
                                                     var zIndex = jQuery('#{$containerId}').parent().css('zIndex');
                                                     zIndex = zIndex - 2;
                                                     $('.ui-widget-overlay').css('zIndex', zIndex);
                                                     $('#{$containerId}').dialog('destroy');
                                                     $('#{$containerId}').css('display', 'none');
                                                     " . $extraCloseScript . "
                                                     }
                });
                return true;
            }";
            // End Not Coding Standard
        }
    }
?>

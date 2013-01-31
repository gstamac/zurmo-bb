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
     * A view that displays a list of supported languages in the application.
     *
     */
    class LanguagesCollectionView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $languagesList;

        protected $messageBoxContent;

        const LANGUAGE_STATUS_ACTIVE   = 1;
        const LANGUAGE_STATUS_INACTIVE = 2;

        public function __construct($controllerId, $moduleId, $messageBoxContent = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('$messageBoxContent == null || is_string($messageBoxContent)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->messageBoxContent      = $messageBoxContent;
        }

        public function getTitle()
        {
            return Zurmo::t('ZurmoModule', 'Languages');
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function renderContent()
        {
            $content  = ZurmoHtml::openTag('div');
            $content .= $this->renderTitleContent();
            $content .= $this->renderMessageBoxContent();
            $content .= ZurmoHtml::openTag('ul', array('class' => 'configuration-list'));
            $content .= $this->renderLanguagesList(self::LANGUAGE_STATUS_ACTIVE);
            $content .= $this->renderLanguagesList(self::LANGUAGE_STATUS_INACTIVE);
            $content .= ZurmoHtml::closeTag('ul');
            $content .= ZurmoHtml::closeTag('div');
            $this->registerJavaScript();
            return $content;
        }

        protected function registerJavaScript()
        {
            $commonErrorMessage = Zurmo::t('ZurmoModule', 'Unexpected error during the AJAX call');
            $script = <<<EOD
$(document).on('click', ".action-button", function() {
    var _parent = $(this).parent();
    var _ajaxAction = $(this).attr('ajaxaction');
    var _languageCode = $(this).attr('languagecode');

    if (_parent.hasClass('loading-ajax'))
    {
        return false;
    }

    if ($.inArray(_ajaxAction, ["activate", "inactivate", "update"]) == -1)
    {
        return false;
    }

    _parent.addClass('loading-ajax');
    $(this).addClass('loading');
    attachLoadingSpinner(_parent.attr('id'), true);

    $.ajax({
        'url':'/app/index.php/zurmo/language/' + _ajaxAction + '/languageCode/' + _languageCode,
        'cache':false,
        'success':function(html) {
            _parent.replaceWith(html);
        },
        'error':function(jqXHR, textStatus, errorThrown) {
            $('#FlashMessageBar').jnotifyAddMessage({
                text: '$commonErrorMessage',
                permanent: false,
                showIcon: true,
            });
            attachLoadingSpinner(_parent.attr('id'));
            $(this).removeClass('loading');
            _parent.removeClass('loading-ajax');
        }
    });

    return false;
});
EOD;
            $cs = Yii::app()->getClientScript();
            $cs->registerScript(
                'my-hello-world-1',
                $script,
                CClientScript::POS_END
            );
        }

        protected function renderMessageBoxContent()
        {
            if (empty($this->messageBoxContent))
            {
                return;
            }

            return ZurmoHtml::tag('div', array(), $this->messageBoxContent);
        }

        protected function renderLanguagesList($languageStatus)
        {
            $languagesList = $this->getLanguagesList($languageStatus);

            if (empty($languagesList))
            {
                return;
            }

            $content = '';
            foreach ($languagesList as $languageCode => $languageData)
            {
                $content .= $this->renderLanguageRow($languageCode, $languageData);
            }

            return $content;
        }

        public function renderLanguageRow($languageCode, $languageData=null)
        {

            if (!$languageData)
            {
                $languageData = $this->getLanguageDataByLanguageCode($languageCode);
            }

            $content = ZurmoHtml::openTag(
                'li',
                array('id'=>'language-row-' . $languageCode)
            );
            $content .= ZurmoHtml::tag('h4', array(), $languageData['label']);
            if ($languageData['active'])
            {
                $metaData = Yii::app()->languageHelper->getActiveLanguageMetaData($languageCode);
                if (!empty($metaData) && isset($metaData['lastUpdate']))
                {
                    $content .= ' - ' . Zurmo::t(
                        'ZurmoModule', 'Last updated on {date}',
                        array('{date}'=>DateTimeUtil::convertTimestampToDbFormatDateTime($metaData['lastUpdate']))
                    );
                }

                if ($languageCode != Yii::app()->sourceLanguage)
                {
                    $content .= $this->renderUpdateButton($languageCode, $languageData);
                    $content .= $this->renderInactivateButton($languageCode, $languageData);
                }
            }
            else
            {
                $content .= $this->renderActivateButton($languageCode, $languageData);
            }
            $content .= ZurmoHtml::closeTag('li');

            return $content;
        }

        protected function renderUpdateButton($languageCode, $languageData)
        {
            assert('is_string($languageCode)');
            assert('is_array($languageData)');
            return ZurmoHtml::link(
                $this->renderButtonSpinnerSpans() .
                ZurmoHtml::tag(
                    'span',
                    array('class'=>'z-label'),
                    Zurmo::t('ZurmoModule', 'Update')
                ),
                '#',
                $this->renderButtonHtml('update', $languageCode, $languageData)
            );
        }

        protected function renderInactivateButton($languageCode, $languageData)
        {
            assert('is_string($languageCode)');
            assert('is_array($languageData)');
            return ZurmoHtml::link(
                $this->renderButtonSpinnerSpans() .
                ZurmoHtml::tag(
                    'span',
                    array('class'=>'z-label'),
                    Zurmo::t('ZurmoModule', 'Deactivate')
                ),
                '#',
                $this->renderButtonHtml('inactivate', $languageCode, $languageData)
            );
        }

        protected function renderActivateButton($languageCode, $languageData)
        {
            assert('is_string($languageCode)');
            assert('is_array($languageData)');
            return ZurmoHtml::link(
                $this->renderButtonSpinnerSpans() . 
                ZurmoHtml::tag(
                    'span',
                    array('class'=>'z-label'),
                    Zurmo::t('ZurmoModule','Activate')
                ),
                '#',
                $this->renderButtonHtml('activate', $languageCode, $languageData)
            );
        }

        protected function renderButtonHtml($action, $languageCode, $languageData)
        {
            assert('in_array($action, array("activate", "inactivate", "update"))');
            $buttonHtml = array(
                'ajaxaction' => $action,
                'languagecode' => $languageCode,
                'class' => 'action-button attachLoading z-button green-button'
            );
            if ($action == 'inactivate' && !$languageData['canInactivate'])
            {
                $buttonHtml['class'] .= ' disabled';
            }

            return $buttonHtml;
        }

        protected function renderButtonSpinnerSpans()
        {
            return ZurmoHtml::tag('span', array('class'=>'z-spinner'), '') .
                    ZurmoHtml::tag('span', array('class'=>'z-icon'), '');
        }

        protected function getLanguagesList($languageStatus=null)
        {
            if (is_array($this->languagesList) && !empty($this->languagesList))
            {
                switch ($languageStatus)
                {
                    case self::LANGUAGE_STATUS_ACTIVE:
                        return $this->languagesList[self::LANGUAGE_STATUS_ACTIVE];
                        break;
                    case self::LANGUAGE_STATUS_INACTIVE:
                        return $this->languagesList[self::LANGUAGE_STATUS_INACTIVE];
                        break;
                    case null:
                        return $this->languagesList;
                        break;
                }
            }

            $languagesList = array(
                self::LANGUAGE_STATUS_ACTIVE   => array(),
                self::LANGUAGE_STATUS_INACTIVE => array()
            );
            $languagesData = self::getLanguagesData();
            foreach ($languagesData as $languageCode => $languageData)
            {
                if ($languageData['active'])
                {
                    $status = self::LANGUAGE_STATUS_ACTIVE;
                }
                else
                {
                    $status = self::LANGUAGE_STATUS_INACTIVE;
                }

                $languagesList[$status][$languageCode] = $languageData;
            }

            $languagesList[self::LANGUAGE_STATUS_ACTIVE] = ArrayUtil::subValueSort(
                $languagesList[self::LANGUAGE_STATUS_ACTIVE],
                'label',
                'asort'
            );
            $languagesList[self::LANGUAGE_STATUS_INACTIVE] = ArrayUtil::subValueSort(
                $languagesList[self::LANGUAGE_STATUS_INACTIVE],
                'label',
                'asort'
            );

            $this->languagesList = $languagesList;
            return $this->getLanguagesList($languageStatus);
        }

        public static function getLanguageDataByLanguageCode($languageCode)
        {
            $languagesData = self::getLanguagesData();
            if (isset($languagesData[$languageCode]))
            {
                return $languagesData[$languageCode];
            }

            return false;
        }

        public static function getLanguagesData()
        {
            $activeLanguages    = Yii::app()->languageHelper->getActiveLanguages();
            $languagesData       = array();
            foreach (Yii::app()->languageHelper->getSupportedLanguagesData() as $language => $label)
            {
                $languagesData[$language] = array('label'         => $label,
                                                 'active'        => in_array($language, $activeLanguages),
                                                 'canInactivate' =>
                                                        Yii::app()->languageHelper->canInactivateLanguage($language));
            }
            return $languagesData;
        }

        public static function renderFlashMessage($text, $permanent = false, $showIcon = true)
        {
            assert('is_string($text) && !empty($text)');
            assert('is_bool($permanent)');
            assert('is_bool($showIcon)');
            $messageConfig = array(
                'text' => $text,
                'permanent' => $permanent,
                'showIcon' => $showIcon
            );

            return sprintf(
                "<script type=\"text/javascript\">" .
                "$('#FlashMessageBar').jnotifyAddMessage(%s);" .
                "</script>",
                json_encode($messageConfig)
            );
        }
    }
?>
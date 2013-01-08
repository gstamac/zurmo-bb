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
     * Controller Class for managing Ldap authentication.
     *
     */
    class ZurmoLdapController extends ZurmoModuleController
    {       
        public function actionConfigurationEditLdap()
        {
            $configurationForm = LdapConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);            
            if (isset($_POST[$postVariableName]))
            {                                  
                $configurationForm->setAttributes($_POST[$postVariableName]);                
                if ($configurationForm->validate())
                {
                    LdapConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Ldap configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new LdapConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }
        
        public function actionTestConnection()
        {
            $configurationForm = LdapConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]) || (isset($_POST['LdapConfigurationForm'])))
            {
                if (isset($_POST[$postVariableName]))
                {
                    $configurationForm->setAttributes($_POST[$postVariableName]);
                }
                else
                {
                    $configurationForm->host                  = $_POST['LdapConfigurationForm']['ldapHost'];
                    $configurationForm->port                  = $_POST['LdapConfigurationForm']['ldapPort'];
                    $configurationForm->bindRegisteredDomain  = $_POST['LdapConfigurationForm']['ldapBindRegisteredDomain'];
                    $configurationForm->bindPassword          = $_POST['LdapConfigurationForm']['ldapBindPassword'];
                    $configurationForm->baseDomain            = $_POST['LdapConfigurationForm']['ldapBaseDomain'];
                    $configurationForm->baseDomain            = $_POST['LdapConfigurationForm']['ldapTurnOn'];                    
                }
                if ($configurationForm->host != null && $configurationForm->port != null && 
                    $configurationForm->bindRegisteredDomain != null && $configurationForm->bindPassword != null &&
      				$configurationForm->baseDomain != null	)
                {
                    $authenticationHelper = new ZurmoAuthenticationHelper;
                    $authenticationHelper->ldapHost     = $configurationForm->host;
                    $authenticationHelper->ldapPort     = $configurationForm->port;
                    $authenticationHelper->ldapBindRegisteredDomain = $configurationForm->bindRegisteredDomain;
                    $authenticationHelper->ldapBindPassword = $configurationForm->bindPassword;
                    $authenticationHelper->ldapBaseDomain = $configurationForm->baseDomain;
					$authenticationHelper->ldapTurnOn = $configurationForm->ldapTurnOn;
                    
                    $host = $configurationForm->host;             
                    $port = $configurationForm->port;                
                    $bindRegisteredDomain = $configurationForm->bindRegisteredDomain;
                    $bindPassword = $configurationForm->bindPassword;         
                    $baseDomain = $configurationForm->baseDomain;           
                    $testConnectionResults = LdapTestHelper::testConnectionLdap($authenticationHelper,$host,$port,
                                                                      $bindRegisteredDomain,$bindPassword,$baseDomain);  
                    $messageContent = Yii::t('Default', $testConnectionResults) . "\n";                                                                      
                }
                else
                {
                    $messageContent = Yii::t('Default', 'All fields are required') . "\n";
                }
                Yii::app()->getClientScript()->setToAjaxMode();
                $messageView = new TestLdapConnectionView($messageContent);
                $view = new ModalView($this, $messageView);				
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
	
?>
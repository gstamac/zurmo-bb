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
        
    class DividedMenu extends CMenu
    {                            
        protected function renderMenu($items)
	{                     
		if(count($items))
		{                    
                    if (count($items) > 1)
                    {
                        throw new NotSupportedException;
                    }                             
                    if ($this->isButtonDivided($items[0]))
                    {
                        $class = 'split-button';
                    }
                    else
                    {
                        $class = 'default-button';
                    }
                                        
                    if(empty($this->htmlOptions['class']))
                    {
                        $this->htmlOptions['class'] = $class;
                    }
                    else
                    {
			$this->htmlOptions['class'] .= ' ' . $class;
                    }
                    echo ZurmoHtml::openTag('div', $this->htmlOptions)."\n";                    
                    $this->renderMenuRecursive($items);
                    echo ZurmoHtml::closeTag('div');                
		}
	}
        
        protected function renderMenuRecursive($items)
	{
		$item = $items[0];		
                $options=isset($item['itemOptions']) ? $item['itemOptions'] : array();
                $class=array();
                if($item['active'] && $this->activeCssClass!='')
                        $class[]=$this->activeCssClass;                
                if($class!==array())
                {
                        if(empty($options['class']))
                                $options['class']=implode(' ',$class);
                        else
                                $options['class'].=' '.implode(' ',$class);
                }
                
                if (isset($item['itemOptions']['iconClass']))
                {
                    $icon = ZurmoHtml::tag('i', array('class' => $item['itemOptions']['iconClass']), null);                                        
                }
                else
                {
                    $icon = null;
                }
                
                if (!isset($item['dynamicLabel']))
                {
                    $item['dynamicLabel'] = null;
                }                
                
                if(isset($item['url']))
		{
			$label = $this->linkLabelWrapper===null ? $item['label'] : CHtml::tag($this->linkLabelWrapper, $this->linkLabelWrapperHtmlOptions, $item['label']);                        
                        $label = ZurmoHtml::tag('span', array('class' => 'button-label'), $label);                                        
			echo CHtml::link($icon . $label . $item['dynamicLabel'],$item['url'], array('class' => 'button-action'));
                        $spanForTrigger = null;
		}
		else
                {
                    $spanForTrigger  = $icon;
                    $spanForTrigger .= CHtml::tag('span',isset($item['linkOptions']) ? $item['linkOptions'] : array(), $item['label']);
                    $spanForTrigger .= $item['dynamicLabel'];
                }
                                                                
                if(isset($item['items']) && count($item['items']))
                {                           
                    $label = ZurmoHtml::tag('i', array('class' => 'icon-trigger'), null);                    
                    if (isset($spanForTrigger))
                    {
                        echo ZurmoHtml::link($spanForTrigger . $label, null, array('class' => 'button-action-trigger'));                            				
                    }
                    else
                    {
                        echo ZurmoHtml::link($label, null, array('class' => 'button-trigger'));                            				
                    }    
                    echo ZurmoHtml::openTag('ul', array('class' => 'button-actions'));
                    foreach ($item['items'] as $item)
                    {
                        echo ZurmoHtml::openTag('li');
                        echo $this->renderMenuItem($item);
                        echo ZurmoHtml::closeTag('li');
                    }
                    echo ZurmoHtml::closeTag('ul');
                }		
	}     
        
        public function run()
	{
            $this->registerScripts();
            $this->renderMenu($this->items);
	}
        
        protected function registerScripts()
        {
            $script = "
                
                    $('.button-triggerm, .button-action-trigger').click(
                                function(){
                                    $('.button-actions', $(this).parent()).show().addClass('stay-open');
                                }
                            );                    
                ";
             Yii::app()->clientScript->registerScript('DividedMenu', $script);
        }          
        
        protected function isButtonDivided($item)
        {
            if (count($item['items']) && isset($item['url']))
            {
                return true;
            }
            return false;
        }
    }
?>

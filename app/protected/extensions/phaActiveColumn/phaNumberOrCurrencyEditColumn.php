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

    /**
     * Column adapter for editable column for numbers or currency
     */
    class phaNumberOrCurrencyEditColumn extends phaAbsActiveColumn {

        /**
         * @var array Additional HTML attributes. See details {@link CHtml::inputField}
         */
        public $htmlEditFieldOptions = array();

        /**
         * Renders the data cell content.
         * This method evaluates {@link value} or {@link name} and renders the result.
         *
         * @param integer $row the row number (zero-based)
         * @param mixed $data the data associated with the row
         */
        protected function renderDataCellContent($row,$data) {

            if($this->value!==null)
                $value=$this->evaluateExpression($this->value,array('data'=>$data,'row'=>$row));
            elseif($this->name!==null)
                $value = CHtml::value($data, $this->name);

            $valueId = $data->{$this->modelId};
            $this->htmlEditFieldOptions['itemId'] = $valueId;
            $this->htmlEditFieldOptions['style']  = 'width:50px;';
            $fieldUID = $this->getViewDivClass();

            echo CHtml::tag('div', array(
                'valueid' => $valueId,
                'id' => $fieldUID  .'-' . $valueId,
                'class' => $fieldUID  . ' editable-cell'
            ), $value);

            echo CHtml::openTag('div', array(
                'style' => 'display: none;',
                'id' => $this->getFieldDivClass() . $data->{$this->modelId},
            ));
            echo CHtml::textField($this->name.'[' . $valueId . ']', $value, $this->htmlEditFieldOptions);
            echo CHtml::closeTag('div');
        }

        /**
         * @return string Name of div's class for view value
         */
        protected function getViewDivClass( ) {
            return 'viewValue-' . $this->id;
        }

        /**
         * @return string Name of div's class for edit field
         */
        protected function getFieldDivClass( ) {
            return 'field-' . $this->id . '-';
        }

        /**
         * Initializes the column.
         *
         * @see CDataColumn::init()
         */
        public function init() {

            parent::init();

            $cs=Yii::app()->getClientScript();

            $liveClick ='
            phaACActionUrls["'.$this->id.'"]="' . $this->buildActionUrl() . '";
            jQuery(".'. $this->getViewDivClass() . '").live("click", function(e){
                phaACOpenEditField(this, "' . $this->id . '");
                return false;
            });';

            $script ='
            var phaACOpenEditItem = 0;
            var phaACOpenEditGrid = "";
            var phaACActionUrls = [];
            function phaACOpenEditField(itemValue, gridUID, grid ) {
                phaACHideEditField( phaACOpenEditItem, phaACOpenEditGrid );
                var id   = $(itemValue).attr("valueid");
                phaACOpenEditItem = id;
                $("#viewValue-" + gridUID + "-"+id).hide();
                var inputValue = $("#field-" + gridUID + "-" + phaACOpenEditItem+" input").val();
                var modifiedInputValue = inputValue.replace(/,/g,"");
                inputValue = modifiedInputValue;

                console.log(inputValue);
                var matches;
                if(!$.isNumeric(inputValue.charAt(0)))
                {
                    matches = inputValue.match(/([0-9]+.[0-9]*)/);
                    inputValue = matches[1];
                }

                $("#field-" + gridUID + "-" + phaACOpenEditItem+" input").val(inputValue);
                $("#field-" + gridUID + "-" + id).show();
                $("#field-" + gridUID + "-" + id+" input")
                    .focus()
                    .keydown(function(event) {
                        switch (event.keyCode) {
                           case 27:
                           case 9:
                              //phaACHideEditField(phaACOpenEditItem, gridUID);
                              phaACEditFieldSend(itemValue, gridUID);
                              break;
                           case 13:
                              phaACEditFieldSend(itemValue, gridUID);
                              break;
                           default: break;
                        }
                    })
                    .blur(function(){
                        //phaACHideEditField(phaACOpenEditItem, gridUID);
                        phaACEditFieldSend(itemValue, gridUID);
                    });


                phaACOpenEditGrid = gridUID;
            }
            function phaACHideEditField( itemId, gridUID ) {
                var clearVal = $("#viewValue-" + gridUID + "-"+itemId).text();
                $("#field-" + gridUID + "-" + itemId+" input").val( clearVal );
                $("#field-" + gridUID + "-" + itemId).hide();
                $("#field-" + gridUID + "-" + itemId+" input").unbind("keydown");
                $("#viewValue-" + gridUID + "-" + itemId).show();
                phaACOpenEditItem=0;
                phaACOpenEditGrid = "";
            }
            function phaACEditFieldSend( itemValue, gridUID ) {
                var passedValue = $("#field-"+phaACOpenEditGrid+"-"+phaACOpenEditItem+" input").val();
                $("#viewValue-" + gridUID + "-"+phaACOpenEditItem).html(passedValue);
                $("#field-" + gridUID + "-" + phaACOpenEditItem).hide();
                $("#field-" + gridUID + "-" + phaACOpenEditItem+" input").unbind("keydown");
                $("#viewValue-" + gridUID + "-" + phaACOpenEditItem).show();
                var id = $(itemValue).parents(".cgrid-view").attr("id");
                $.ajax({
                        type: "GET",
                        dataType: "json",
                        url: phaACActionUrls[gridUID],
                        cache: false,
                        data: {
                            item: phaACOpenEditItem,
                            value: passedValue
                        },
                        success: function(data){
                          $("#"+id).yiiGridView.update( id );
                        }
                    });
            }
            ';

            $cs->registerScript(__CLASS__.'#active_column-edit', $script);
            $cs->registerScript(__CLASS__.$this->grid->id.'#active_column_click-'.$this->id, $liveClick);
        }
    }
?>
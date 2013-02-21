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

    class MashableInboxListView extends ListView
    {
        protected $rowsAreSelectable = true;

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'MashableInboxSummary'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),

            );
            return $metadata;
        }

        protected function getCGridViewLastColumn()
        {
            return array();
        }

        /**
         * Overrides the parent implementation to prefix the value of selectable checkBox with modelClassName
         */
         protected function getCGridViewColumns()
         {
            $columns = array();
            if ($this->rowsAreSelectable)
            {
                $checked = 'in_array($data->id, array(' . implode(',', $this->selectedIds) . '))'; // Not Coding Standard
                $checkBoxHtmlOptions = array();
                $firstColumn = array(
                    'class'               => 'CheckBoxColumn',
                    'checked'             => $checked,
                    'id'                  => $this->gridId . $this->gridIdSuffix . '-rowSelector', // Always specify this as -rowSelector.
                    'checkBoxHtmlOptions' => $checkBoxHtmlOptions,
                    'value'               => 'get_class($data). "_" . $data->id',
                );
                array_push($columns, $firstColumn);
            }

            $metadata = $this->getResolvedMetadata();
            foreach ($metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    foreach ($row['cells'] as $cell)
                    {
                        foreach ($cell['elements'] as $columnInformation)
                        {
                            $columnClassName = $columnInformation['type'] . 'ListViewColumnAdapter';
                            $columnAdapter  = new $columnClassName($columnInformation['attributeName'], $this, array_slice($columnInformation, 1));
                            $column = $columnAdapter->renderGridViewData();
                            if (!isset($column['class']))
                            {
                                $column['class'] = 'DataColumn';
                            }
                            array_push($columns, $column);
                        }
                    }
                }
            }
            $menuColumn = $this->getGridViewMenuColumn();
            if ($menuColumn == null)
            {
                $lastColumn = $this->getCGridViewLastColumn();
                if (!empty($lastColumn))
                {
                    array_push($columns, $lastColumn);
                }
            }
            else
            {
                array_push($columns, $menuColumn);
            }
            return $columns;
        }
    }
?>
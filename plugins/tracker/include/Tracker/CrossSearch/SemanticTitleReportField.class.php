<?php

/**
 * Copyright (c) Enalean, 2012. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__).'/../Tracker_Report_Field.class.php';

class Tracker_CrossSearch_SemanticTitleReportField implements Tracker_Report_Field {

    private $id = 'semantic_title';
    
    public function isUsed() {
        return true;
    }
    
    public function fetchCriteria(Tracker_Report_Criteria $criteria) {
        $html  = '';
        $html .= '<label for="tracker_report_criteria_" title="#">'. $this->getLabel();
        $html .= '</label>';
        $html .= '<br />';
        $html .= '<input type="text" name="criteria[semantic_title]" id="tracker_report_criteria_semantic_title" value="" />';
        
        return $html;
    }
    
    public function getLabel() {
        return 'Title';        
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function fetchChangesetValue($artifact_id, $changeset_id, $value, $from_aid = null) {
        return 'Qux';
    }
    
}

?>

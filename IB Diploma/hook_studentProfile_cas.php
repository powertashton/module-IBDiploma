<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

@session_start();

//Module includes
include './modules/IB Diploma/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/hook_studentProfile_cas.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    if (enroled($guid, $gibbonPersonID, $connection2) == false) {
        //Acess denied
        $page->addError(__('The selected student is not enroled in the IB Diploma programme.'));
    } else {
        try {
            $data = array('gibbonPersonID' => $gibbonPersonID);
            $sql = 'SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID ORDER BY approval, name';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError(__('Commitments cannot be displayed.'));
        }

        if ($result->rowCount() < 1) {
            $page->addError(__('There are no commitments to display.'));
        } else {
            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo "<th style='vertical-align: bottom'>";
            echo 'Name';
            echo '</th>';
            echo "<th style='vertical-align: bottom'>";
            echo 'Status';
            echo '</th>';
            echo "<th style='vertical-align: bottom'>";
            echo 'Timing';
            echo '</th>';
            echo "<th style='vertical-align: bottom'>";
            echo 'Supervisor';
            echo '</th>';
            echo '</tr>';

            $count = 0;
            $rowNum = 'odd';
            $intended = array();
            $complete = array();
            while ($row = $result->fetch()) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                echo '<td>';
                echo $row['name'];
                echo '</td>';
                echo '<td>';
                if ($row['approval'] == 'Pending' or $row['approval'] == 'Not Approved') {
                    echo $row['approval'];
                } else {
                    echo $row['status'];
                }
                echo '</td>';
                echo '<td>';
                if (substr($row['dateStart'], 0, 4) == substr($row['dateEnd'], 0, 4)) {
                    if (substr($row['dateStart'], 5, 2) == substr($row['dateEnd'], 5, 2)) {
                        echo date('F', mktime(0, 0, 0, substr($row['dateStart'], 5, 2))).' '.substr($row['dateStart'], 0, 4);
                    } else {
                        echo date('F', mktime(0, 0, 0, substr($row['dateStart'], 5, 2))).' - '.date('F', mktime(0, 0, 0, substr($row['dateEnd'], 5, 2))).' '.substr($row['dateStart'], 0, 4);
                    }
                } else {
                    echo date('F', mktime(0, 0, 0, substr($row['dateStart'], 5, 2))).' '.substr($row['dateStart'], 0, 4).' - '.date('F', mktime(0, 0, 0, substr($row['dateEnd'], 5, 2))).' '.substr($row['dateEnd'], 0, 4);
                }
                echo '</td>';
                echo '<td>';
                echo $row['supervisorName'];
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}

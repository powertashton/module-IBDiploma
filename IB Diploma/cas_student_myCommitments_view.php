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
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_myCommitments_view.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this page.'));
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {
        //Proceed!
        //Get class variable
        $ibDiplomaCASCommitmentID = $_GET['ibDiplomaCASCommitmentID'];
        if ($ibDiplomaCASCommitmentID == '') {
            echo "<div class='warning'>";
            echo 'Commitment has not been specified .';
            echo '</div>';
        }
        //Check existence of and access to this class.
        else {
            try {
                $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID'], 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                $sql = 'SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError($e->getMessage());
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The specified commitment could not be loaded.'));
            } else {
                $values = $result->fetch();

                echo '<h1>';
                echo $values['name'].'<br>';
                echo '</h1>';

                echo "<div style='width:510px; float: left; font-size: 115%; margin-top: -5px'>";
                try {
                    $dataReflections = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID'], 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                    $sqlReflections = 'SELECT * FROM ibDiplomaCASReflection WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID ORDER BY timestamp';
                    $resultReflections = $connection2->prepare($sqlReflections);
                    $resultReflections->execute($dataReflections);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }

                if ($resultReflections->rowCount() < 1) {
                    echo "<div class='warning'>";
                    echo 'There are no reflections to display in this commitment';
                    echo '</div>';
                } else {
                    while ($valuesReflections = $resultReflections->fetch()) {
                        echo '<h3>';
                        echo $valuesReflections['title'].'<br/>';
                        echo "<span style='font-size: 55%; font-weight: normal; font-style: italic; margin-top: 5px'>".dateConvertBack(substr($valuesReflections['timestamp'], 0, 10)).' at '.substr($valuesReflections['timestamp'], 11, 5).'</span>';
                        echo '</h3>';
                        echo '<p>';
                        echo $valuesReflections['reflection'];
                        echo '</p>';
                    }
                }
                echo '</div>';

                //Details
                echo "<div style='width:430px; float: right; font-size: 115%; padding-top: 14px'>";
                echo "<table class='blank' cellspacing='0' style='width: 420px; float: left;'>";
                echo '<tr>';
                echo "<td style='width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Status</span><br/>";
                if ($values['approval'] == 'Pending' or $values['approval'] == 'Not Approved') {
                    echo $values['approval'];
                } else {
                    echo $values['status'];
                }
                echo '</td>';
                echo "<td style='width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Start Date</span><br/>";
                echo dateConvertBack($guid, $values['dateStart']);
                echo '</td>';
                echo "<td style='width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>End Date</span><br/>";
                echo dateConvertBack($guid, $values['dateEnd']);
                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Supervisor</span><br/>";
                echo $values['supervisorName'];
                echo '</td>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Phone</span><br/>";
                echo $values['supervisorPhone'];
                echo '</td>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Email</span><br/>";
                echo $values['supervisorEmail'];
                echo '</td>';
                echo '</tr>';
                if ($values['description'] != '') {
                    echo '<tr>';
                    echo "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>";
                    echo "<span style='font-size: 115%; font-weight: bold'>Description</span><br/>";
                    echo $values['description'];
                    echo '</td>';
                    echo '</tr>';
                }
                if ($values['goals'] != '') {
                    echo '<tr>';
                    echo "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>";
                    echo "<span style='font-size: 115%; font-weight: bold'>Goals</span><br/>";
                    echo $values['goals'];
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            }
        }
    }
}

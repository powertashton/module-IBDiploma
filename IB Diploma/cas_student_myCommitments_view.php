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
    echo "<div class='error'>";
    echo 'You do not have access to this page.';
    echo '</div>';
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Acess denied
        echo "<div class='error'>";
        echo 'You are not enroled in the IB Diploma programme.';
        echo '</div>';
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
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo 'The specified commitment could not be loaded.';
                echo '</div>';
            } else {
                $row = $result->fetch();

                echo '<h1>';
                echo $row['name'].'<br>';
                echo '</h1>';

                echo "<div style='width:510px; float: left; font-size: 115%; margin-top: -5px'>";
                try {
                    $dataReflections = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID'], 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                    $sqlReflections = 'SELECT * FROM ibDiplomaCASReflection WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID ORDER BY timestamp';
                    $resultReflections = $connection2->prepare($sqlReflections);
                    $resultReflections->execute($dataReflections);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($resultReflections->rowCount() < 1) {
                    echo "<div class='warning'>";
                    echo 'There are no reflections to display in this commitment';
                    echo '</div>';
                } else {
                    while ($rowReflections = $resultReflections->fetch()) {
                        echo '<h3>';
                        echo $rowReflections['title'].'<br/>';
                        echo "<span style='font-size: 55%; font-weight: normal; font-style: italic; margin-top: 5px'>".dateConvertBack(substr($rowReflections['timestamp'], 0, 10)).' at '.substr($rowReflections['timestamp'], 11, 5).'</span>';
                        echo '</h3>';
                        echo '<p>';
                        echo $rowReflections['reflection'];
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
                if ($row['approval'] == 'Pending' or $row['approval'] == 'Not Approved') {
                    echo $row['approval'];
                } else {
                    echo $row['status'];
                }
                echo '</td>';
                echo "<td style='width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Start Date</span><br/>";
                echo dateConvertBack($guid, $row['dateStart']);
                echo '</td>';
                echo "<td style='width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>End Date</span><br/>";
                echo dateConvertBack($guid, $row['dateEnd']);
                echo '</td>';
                echo '</tr>';
                echo '<tr>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Supervisor</span><br/>";
                echo $row['supervisorName'];
                echo '</td>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Phone</span><br/>";
                echo $row['supervisorPhone'];
                echo '</td>';
                echo "<td style='padding-top: 15px; width: 33%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Email</span><br/>";
                echo $row['supervisorEmail'];
                echo '</td>';
                echo '</tr>';
                if ($row['description'] != '') {
                    echo '<tr>';
                    echo "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>";
                    echo "<span style='font-size: 115%; font-weight: bold'>Description</span><br/>";
                    echo $row['description'];
                    echo '</td>';
                    echo '</tr>';
                }
                if ($row['goals'] != '') {
                    echo '<tr>';
                    echo "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>";
                    echo "<span style='font-size: 115%; font-weight: bold'>Goals</span><br/>";
                    echo $row['goals'];
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            }
        }
    }
}

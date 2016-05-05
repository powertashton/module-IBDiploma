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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_iboCheck_full.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this page.';
    echo '</div>';
} else {
    $gibbonPersonID = $_GET['gibbonPersonID'];
    if ($gibbonPersonID == '') { echo "<div class='error'>";
        echo 'You have not specified a student.';
        echo '</div>';
    } else {
        try {
            $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'gibbonPersonID' => $gibbonPersonID);
            $sql = "SELECT gibbonPerson.gibbonPersonID, gibbonStudentEnrolment.gibbonYearGroupID, gibbonStudentEnrolment.gibbonRollGroupID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber=:sequenceEnd AND gibbonPerson.gibbonPersonID=:gibbonPersonID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo 'The specified student does not exist, or you do not have access to them.';
            echo '</div>';
        } else {
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
                    $data = array('gibbonPersonID' => $gibbonPersonID, 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                    $sql = 'SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='warning'>";
                    echo 'Commitment does not exist or you do not have access to it.';
                    echo '</div>';
                } else {
                    $row = $result->fetch();

                    echo '<h1>';
                    echo $row['name'].'<br>';
                    echo '</h1>';

                    echo "<div style='width:510px; float: left; font-size: 115%; margin-top: -5px'>";
                    try {
                        $dataReflections = array('gibbonPersonID' => $gibbonPersonID, 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
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
                    echo "<td colspan=3'>";
                    echo "<h2 style='margin-top: 0px'>";
                    echo 'General Information';
                    echo '</h2>';
                    echo '</td>';
                    echo '</tr>';
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
                    echo dateConvertBack($row['dateStart']);
                    echo '</td>';
                    echo "<td style='width: 33%; vertical-align: top'>";
                    echo "<span style='font-size: 115%; font-weight: bold'>End Date</span><br/>";
                    echo dateConvertBack($row['dateEnd']);
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
                    echo '<tr>';
                    echo '<td colspan=3>';
                    echo '<h2>';
                    echo 'Supervisor';
                    echo '</h2>';
                    echo '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo "<td style='width: 33%; vertical-align: top'>";
                    echo "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>";
                    echo $row['supervisorName'];
                    echo '</td>';
                    echo "<td style='width: 33%; vertical-align: top'>";
                    echo "<span style='font-size: 115%; font-weight: bold'>Phone</span><br/>";
                    echo $row['supervisorPhone'];
                    echo '</td>';
                    echo "<td style='15px; width: 33%; vertical-align: top'>";
                    echo "<span style='font-size: 115%; font-weight: bold'>Email</span><br/>";
                    echo $row['supervisorEmail'];
                    echo '</td>';
                    echo '</tr>';

					//Print feedback if there is any
					try {
						$dataFeedback = array('ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
						$sqlFeedback = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='Y'";
						$resultFeedback = $connection2->prepare($sqlFeedback);
						$resultFeedback->execute($dataFeedback);
					} catch (PDOException $e) {
						echo "<div class='error'>".$e->getMessage().'</div>';
					}

                    if ($resultFeedback->rowCount() == 1) {
                        $rowFeedback = $resultFeedback->fetch();
                        echo '<tr>';
                        echo '<td colspan=3>';
                        echo '<h2>';
                        echo 'Feedback';
                        echo '</h2>';
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>";
                        echo "<span style='font-size: 115%; font-weight: bold'>Evaluation</span><br/>";
                        echo $rowFeedback['evaluation'];
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>";
                        echo "<span style='font-size: 115%; font-weight: bold'>Attendance</span><br/>";
                        echo $rowFeedback['attendance'];
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '</div>';
                }
            }
        }
    }
}

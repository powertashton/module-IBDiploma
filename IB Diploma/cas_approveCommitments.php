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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_approveCommitments.php') == false) {

    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    $role = staffCASRole($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role == false) {
        echo "<div class='error'>";
        echo 'You are not enroled in the IB Diploma programme.';
        echo '</div>';
    } else {
        echo "<div class='trail'>";
        echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > </div><div class='trailEnd'>Approve CAS Commitments</div>";
        echo '</div>';

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        try {
            if ($role == 'Coordinator') {
                $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber']);
                $sql = "SELECT ibDiplomaCASCommitment.*, gibbonPerson.gibbonPersonID, gibbonStudentEnrolment.gibbonYearGroupID, gibbonStudentEnrolment.gibbonRollGroupID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) JOIN ibDiplomaCASCommitment ON (ibDiplomaCASCommitment.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND approval='Pending'";
            } else {
                $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'advisor' => $_SESSION[$guid]['gibbonPersonID']);
                $sql = "SELECT ibDiplomaCASCommitment.*, gibbonPerson.gibbonPersonID, gibbonStudentEnrolment.gibbonYearGroupID, gibbonStudentEnrolment.gibbonRollGroupID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) JOIN ibDiplomaCASCommitment ON (ibDiplomaCASCommitment.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor AND approval='Pending'";
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='success'>";
            echo 'There are no commitments pending action. Have a rest, take it easy!';
            echo '</div>';
        } else {
            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo "<th style='vertical-align: bottom'>";
            echo 'Student';
            echo '</th>';
            echo "<th style='vertical-align: bottom'>";
            echo 'Name';
            echo '</th>';
            echo "<th style='vertical-align: bottom'>";
            echo 'Status';
            echo '</th>';
            echo "<th style='vertical-align: bottom'>";
            echo 'Actions';
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
                echo formatName('', $row['preferredName'], $row['surname'], 'Student', true, true);
                echo '</td>';
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
                echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/cas_adviseStudents_full.php&gibbonPersonID='.$row['gibbonPersonID'].'&ibDiplomaCASCommitmentID='.$row['ibDiplomaCASCommitmentID']."&width=1000&height=550'><img title='View' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_right.png'/></a> ";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cas_approveCommitmentsProcess.php?address='.$_GET['q'].'&job=approve&ibDiplomaCASCommitmentID='.$row['ibDiplomaCASCommitmentID']."'><img title='Approve' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick.png'/></a> ";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cas_approveCommitmentsProcess.php?address='.$_GET['q'].'&job=reject&ibDiplomaCASCommitmentID='.$row['ibDiplomaCASCommitmentID']."'><img title='Reject' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconCross.png'/></a> ";
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}

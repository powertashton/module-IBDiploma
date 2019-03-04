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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_iboCheck.php') == false) {

    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > </div><div class='trailEnd'>IBO CAS Check</div>";
    echo '</div>';
    try {
        $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber']);
        $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber=:sequenceEnd ORDER BY start.sequenceNumber DESC, surname, preferredName";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) { echo "<div class='error'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) { echo "<div class='error'>";
        echo 'There are no students to display.';
        echo '</div>';
    } else {
        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo 'Name';
        echo '</th>';
        echo '<th>';
        echo 'Roll<br/>Group';
        echo '</th>';
        echo '<th>';
        echo 'End';
        echo '</th>';
        echo '<th>';
        echo 'CAS Advisor';
        echo '</th>';
        echo '<th>';
        echo 'Status<br/>';
        echo '</th>';
        echo '<th>';
        echo 'Actions';
        echo '</th>';
        echo '</tr>';

        $count = 0;
        $rowNum = 'odd';
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
            echo $row['rollGroup'];
            echo '</td>';
            echo '<td>';
            echo '<b>'.$row['end'].'</b>';
            echo '</td>';
            echo '<td>';
            if ($row['gibbonPersonIDCASAdvisor'] != '') {
                try {
                    $dataAdvisor = array('gibbonPersonID' => $row['gibbonPersonIDCASAdvisor']);
                    $sqlAdvisor = "SELECT surname, preferredName FROM gibbonPerson WHERE gibbonPersonID=:gibbonPersonID AND status='Full'";
                    $resultAdvisor = $connection2->prepare($sqlAdvisor);
                    $resultAdvisor->execute($dataAdvisor);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }
                if ($resultAdvisor->rowCount() == 1) {
                    $rowAdvisor = $resultAdvisor->fetch();
                    echo formatName('', $rowAdvisor['preferredName'], $rowAdvisor['surname'], 'Staff', true, true);
                }
            }
            echo '</td>';
            echo '<td>';
            if ($row['casStatusSchool'] == 'At Risk') {
                echo "<img title='At Risk' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconCross.png'/>";
            } elseif ($row['casStatusSchool'] == 'On Task') {
                echo "<img title='On Task' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick.png'/>";
            } elseif ($row['casStatusSchool'] == 'Excellence') {
                echo "<img title='Excellence' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/like_on_small.png'/>";
            } elseif ($row['casStatusSchool'] == 'Incomplete') {
                echo "<img title='Incomplete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconCross.png'/> Incomplete";
            } elseif ($row['casStatusSchool'] == 'Complete') {
                echo "<img title='Complete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick.png'/> Complete";
            }
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/cas_iboCheck_details.php&gibbonPersonID='.$row['gibbonPersonID']."'><img title='Details' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_right.png'/></a> ";
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

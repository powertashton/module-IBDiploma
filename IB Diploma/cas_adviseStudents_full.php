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

use Gibbon\Tables\DataTable;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_adviseStudents_full.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this page.'));
} else {
    $role = staffCASRole($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role == false) { $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {
        $gibbonPersonID = $_GET['gibbonPersonID'];
        if ($gibbonPersonID == '') {
            $page->addError(__('You have not specified a student.'));
        } else {
            try {
                if ($role == 'Coordinator') {
                    $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName";
                } else {
                    $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'advisor' => $_SESSION[$guid]['gibbonPersonID'], 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError($e->getMessage());
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The specified student does not exist, or you do not have access to them.'));
            } else {
                //Get class variable
                $ibDiplomaCASCommitmentID = $_GET['ibDiplomaCASCommitmentID'];
                if ($ibDiplomaCASCommitmentID == '') {
                    echo "<div class='warning'>";
                    echo 'Commitment has not been specified .';
                    echo '</div>';
                }
                //Check existence of and access to this commitment.
                else {
                    try {
                        $data = array('gibbonPersonID' => $gibbonPersonID, 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                        $sql = 'SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $page->addError($e->getMessage());
                    }

                    if ($result->rowCount() != 1) {
                        echo "<div class='warning'>";
                        echo 'Commitment does not exist or you do not have access to it.';
                        echo '</div>';
                    } else {
                        $values = $result->fetch();

                        $table = DataTable::createDetails('details');
                        $table->setTitle($values['name']);
                        
                        $table->addColumn('approval', __('Approval'));
                        $table->addColumn('status', __('Status'));
                        $table->addColumn('dateStart', __('Start Date'))
                            ->format(function ($row) use ($guid) {
                            return dateConvertBack($guid, $row['dateStart']);
                            });
                        $table->addColumn('dateEnd', __('End Date'))
                            ->format(function ($row) use ($guid) {
                            return dateConvertBack($guid, $row['dateEnd']);
                            });
                        $table->addColumn('supervisorName', __('Supervisor'));
                        $table->addColumn('supervisorPhone', __('Phone'));    
                        $table->addColumn('supervisorEmail', __('Email'))->addClass('col-span-2'); 
                        if ($values['description'] != '') {
                            $table->addColumn('description', __('Description'))->addClass('col-span-4');
                        }
                        if ($values['goals'] != '') {
                            $table->addColumn('goals', __('Goals'))->addClass('col-span-4');
                        }
                        $table->addMetaData('gridClass', 'grid-cols-4'); 
                        echo $table->render([$values]);
                        
                        //TODO: TURN THIS INTO NICE FEEDBACK TABLE
                        try {
                            $dataFeedback = array('ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                            $sqlFeedback = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='Y'";
                            $resultFeedback = $connection2->prepare($sqlFeedback);
                            $resultFeedback->execute($dataFeedback);
                        } catch (PDOException $e) {
                            $page->addError($e->getMessage());
                        }

                        if ($resultFeedback->rowCount() == 1) {
                            $valuesFeedback = $resultFeedback->fetch();
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
                            echo $valuesFeedback['evaluation'];
                            echo '</td>';
                            echo '</tr>';
                            echo '<tr>';
                            echo "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>";
                            echo "<span style='font-size: 115%; font-weight: bold'>Attendance</span><br/>";
                            echo $valuesFeedback['attendance'];
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        echo '</div>';
                        //TODO: TURN THIS INTO NICE REFLECTION TABLE
                        echo "<div style='width:510px; float: left; font-size: 115%; margin-top: -5px'>";
                        try {
                            $dataReflections = array('gibbonPersonID' => $gibbonPersonID, 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
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
                                echo "<span style='font-size: 55%; font-weight: normal; font-style: italic; margin-top: 5px'>".$valuesReflections['timestamp'].'</span>';
                                echo '</h3>';
                                echo '<p>';
                                echo $valuesReflections['reflection'];
                                echo '</p>';
                            }
                        }
                        echo '</div>';
                        
                    }
                }
            }
        }
    }
}

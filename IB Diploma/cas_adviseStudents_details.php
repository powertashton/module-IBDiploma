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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_adviseStudents_details.php') == false) {

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
        $gibbonPersonID = $_GET['gibbonPersonID'];
        if ($gibbonPersonID == '') {
            echo "<div class='error'>";
            echo 'You have not specified a student.';
            echo '</div>';
        } else {
            try {
                if ($role == 'Coordinator') {
                    $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, image_240, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName";
                } else {
                    $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'advisor' => $_SESSION[$guid]['gibbonPersonID'], 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, image_240, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName";
                }
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
                $row = $result->fetch();
                $image_240 = $row['image_240'];

                echo "<div class='trail'>";
                echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/cas_adviseStudents.php'>Advise CAS Students</a> > </div><div class='trailEnd'>Advise Student</div>";
                echo '</div>';

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
                echo '<tr>';
                echo "<td style='width: 34%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>";
                echo formatName('', $row['preferredName'], $row['surname'], 'Student', true, true);
                echo '</td>';
                echo "<td style='width: 34%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Roll Group</span><br/>";
                try {
                    $dataDetail = array('gibbonRollGroupID' => $row['gibbonRollGroupID']);
                    $sqlDetail = 'SELECT * FROM gibbonRollGroup WHERE gibbonRollGroupID=:gibbonRollGroupID';
                    $resultDetail = $connection2->prepare($sqlDetail);
                    $resultDetail->execute($dataDetail);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }
                if ($resultDetail->rowCount() == 1) {
                    $rowDetail = $resultDetail->fetch();
                    echo '<i>'.$rowDetail['name'].'</i>';
                }
                echo '</td>';
                echo "<td style='width: 34%; vertical-align: top'>";
                $casStatusSchool = $row['casStatusSchool'];
                echo "<span style='font-size: 115%; font-weight: bold'>CAS Status</span><br/>";
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
                echo '</tr>';
                echo '</table>';

                $subpage = null;
                if (isset($_GET['subpage'])) {
                    $subpage = $_GET['subpage'];
                }
                if ($subpage == '') {
                    $subpage = 'Overview';
                }

                echo '<h2>';
                echo $subpage;
                echo '</h2>';

                if ($subpage == 'Overview') {
                    try {
                        $data = array('gibbonPersonID' => $gibbonPersonID);
                        $sql = 'SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID ORDER BY approval, name';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($result->rowCount() < 1) {
                        echo "<div class='error'>";
                        echo 'There are no commitments to display.';
                        echo '</div>';
                    } else {
                        echo "<table cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo "<th style='vertical-align: bottom'>";
                        echo 'Commitment';
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
                        echo "<th style='vertical-align: bottom'>";
                        echo "<span title='Supervisor Feedback'>Feedback</span>";
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
                            if ($row['supervisorEmail'] != '') {
                                echo "<a href='mailto:".$row['supervisorEmail']."'>".$row['supervisorName'].'</a>';
                            } else {
                                echo $row['supervisorName'];
                            }
                            echo '</td>';
                            echo '<td>';
                            try {
                                $dataFeedback = array('ibDiplomaCASCommitmentID' => $row['ibDiplomaCASCommitmentID']);
                                $sqlFeedback = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='Y'";
                                $resultFeedback = $connection2->prepare($sqlFeedback);
                                $resultFeedback->execute($dataFeedback);
                            } catch (PDOException $e) {
                                echo "<div class='error'>".$e->getMessage().'</div>';
                            }

                            if ($resultFeedback->rowCount() == 1) {
                                echo "<img title='Supervisor Feedback Complete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick.png'/>";
                            } else {
                                try {
                                    $dataFeedback = array('ibDiplomaCASCommitmentID' => $row['ibDiplomaCASCommitmentID']);
                                    $sqlFeedback = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='N'";
                                    $resultFeedback = $connection2->prepare($sqlFeedback);
                                    $resultFeedback->execute($dataFeedback);
                                } catch (PDOException $e) {
                                    echo "<div class='error'>".$e->getMessage().'</div>';
                                }

                                if ($resultFeedback->rowCount() > 0) {
                                    echo "<img title='Supervisor Feedback Requested' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick_light.png'/>";
                                }
                            }
                            echo '</td>';
                            echo '<td>';
                            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/cas_adviseStudents_full.php&gibbonPersonID=$gibbonPersonID&ibDiplomaCASCommitmentID=".$row['ibDiplomaCASCommitmentID']."&width=1000&height=550'><img title='View' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_right.png'/></a> ";
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                } elseif ($subpage == 'Reflection') {
                    try {
                        $data = array('gibbonPersonID' => $gibbonPersonID);
                        $sql = 'SELECT * FROM ibDiplomaCASReflection WHERE gibbonPersonID=:gibbonPersonID ORDER BY timestamp';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    echo "<div class='linkTop'>";
                    echo 'Filter Commitment: ';
                    ?>
						<select name="searchInput" class="searchInput" style='float: none; width: 100px'>
							<option selected value=''>All</option>
							<option selected value='General'>General CAS</option>
							<?php
                            try {
                                $dataSelect = array('gibbonPersonID' => $gibbonPersonID);
                                $sqlSelect = 'SELECT DISTINCT ibDiplomaCASCommitment.ibDiplomaCASCommitmentID, name FROM ibDiplomaCASReflection JOIN ibDiplomaCASCommitment ON (ibDiplomaCASCommitment.ibDiplomaCASCommitmentID=ibDiplomaCASReflection.ibDiplomaCASCommitmentID) WHERE ibDiplomaCASReflection.gibbonPersonID=:gibbonPersonID ORDER BY timestamp';
                                $resultSelect = $connection2->prepare($sqlSelect);
                                $resultSelect->execute($dataSelect);
                            } catch (PDOException $e) {
                                echo "<div class='error'>".$e->getMessage().'</div>';
                            }
                    while ($rowSelect = $resultSelect->fetch()) {
                        echo "<option value='".$rowSelect['ibDiplomaCASCommitmentID']."'>".htmlPrep($rowSelect['name']).'</option>';
                    }
                    ?>
						</select>
					<?php
                    echo '</div>';

                    if ($result->rowCount() < 1) {
                        echo "<div class='error'>";
                        echo 'There are no reflections to display.';
                        echo '</div>';
                    } else {
                        echo "<table cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo "<th style='vertical-align: bottom'>";
                        echo 'Commitment';
                        echo '</th>';
                        echo "<th style='vertical-align: bottom'>";
                        echo 'Date';
                        echo '</th>';
                        echo "<th style='vertical-align: bottom'>";
                        echo 'Title';
                        echo '</th>';
                        echo "<th style='vertical-align: bottom'>";
                        echo 'Actions';
                        echo '</th>';
                        echo '</tr>';
                        echo "<tbody class='body'>";
                        $count = 0;
                        $rowNum = 'odd';
                        while ($row = $result->fetch()) {
                            ++$count;

                            $class = $row['ibDiplomaCASCommitmentID'];
                            if ($class == '') {
                                $class = 'General';
                            }
                            echo "<tr class='$class'>";
                            echo '<td>';
                            if (is_null($row['ibDiplomaCASCommitmentID'])) {
                                echo '<b><i>General CAS</i></b>';
                            } else {
                                try {
                                    $dataCommitment = array('ibDiplomaCASCommitmentID' => $row['ibDiplomaCASCommitmentID']);
                                    $sqlCommitment = 'SELECT * FROM ibDiplomaCASCommitment WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID';
                                    $resultCommitment = $connection2->prepare($sqlCommitment);
                                    $resultCommitment->execute($dataCommitment);
                                } catch (PDOException $e) {
                                    echo "<div class='error'>".$e->getMessage().'</div>';
                                }
                                if ($resultCommitment->rowCount() == 1) {
                                    $rowCommitment = $resultCommitment->fetch();
                                    echo $rowCommitment['name'];
                                }
                            }
                            echo '</td>';
                            echo '<td>';
                            echo dateConvertBack($guid, substr($row['timestamp'], 0, 10));
                            echo '</td>';
                            echo '<td>';
                            echo $row['title'];
                            echo '</td>';
                            echo '<td>';
                            echo "<script type='text/javascript'>";
                            echo '$(document).ready(function(){';
                            echo "\$(\".comment-$count\").hide();";
                            echo "\$(\".show_hide-$count\").fadeIn(1000);";
                            echo "\$(\".show_hide-$count\").click(function(){";
                            echo "\$(\".comment-$count\").fadeToggle(1000);";
                            echo '});';
                            echo '});';
                            echo '</script>';
                            echo "<a class='show_hide-$count' onclick='false'  href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/page_down.png' alt='Show Comment' onclick='return false;' /></a>";
                            echo '</td>';
                            echo '</tr>';
                            echo "<tr class='comment-$count' id='comment-$count'>";
                            echo "<td style='background-color: #D4F6DC;' colspan=4>";
                            echo $row['reflection'];
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo "</tbody'>";
                        echo '</table>';
                        ?>
						<script type="text/javascript">
							$(document).ready(function() {
								$('.searchInput').val(1);
								$('.body').find("tr:visible:odd").addClass('odd');
								$('.body').find("tr:visible:even").addClass('even');

								$(".searchInput").change(function(){
									$('.body').find("tr").hide() ;
									if ($('.searchInput :selected').val() == "" ) {
										$('.body').find("tr").show() ;
									}
									else {
										$('.body').find('.' + $('.searchInput :selected').val()).show();
									}

									$('.body').find("tr").removeClass('odd even');
									$('.body').find('tr:visible:odd').addClass('odd');
									$('.body').find('tr:visible:even').addClass('even');
								});

							});
						</script>
						<?php

                    }
                } elseif ($subpage == 'CAS Status') {
                    echo '<p>';
                    echo "This field is used to indicate whether or not the student has, in the school's opinion, completed the CAS component of the IB Diploma.";
                    echo '</p>';

                    ?>
					<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cas_adviseStudents_detailsStatusProcess.php' ?>">
						<table class='smallIntBorder' cellspacing='0' style="width: 100%">
							<tr>
								<td>
									<b>Status *</b><br/>
									<span style="font-size: 90%"><i></i></span>
								</td>
								<td class="right">
									<select name="casStatusSchool" id="casStatusSchool" style="width: 302px">
										<option <?php if ($casStatusSchool == '') {
    echo 'selected ';
}
                    ?>value=""></option>
										<option <?php if ($casStatusSchool == 'At Risk') {
    echo 'selected ';
}
                    ?>value="At Risk">At Risk</option>
										<option <?php if ($casStatusSchool == 'On Task') {
    echo 'selected ';
}
                    ?>value="On Task">On Task</option>
										<option <?php if ($casStatusSchool == 'Excellence') {
    echo 'selected ';
}
                    ?>value="Excellence">Excellence</option>
										<option <?php if ($casStatusSchool == 'Complete') {
    echo 'selected ';
}
                    ?>value="Complete">Complete</option>
										<option <?php if ($casStatusSchool == 'Incomplete') {
    echo 'selected ';
}
                    ?>value="Incomplete">Incomplete</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<span style="font-size: 90%"><i>* denotes a required field</i></span>
								</td>
								<td class="right">
									<input type="hidden" name="gibbonPersonID" value="<?php echo $gibbonPersonID ?>">
									<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
									<input type="submit" value="Submit">
								</td>
							</tr>
						</table>
					</form>
					<?php

                } elseif ($subpage == 'Interview 1') {
                    try {
                        $dataInterview = array('gibbonPersonID' => $gibbonPersonID);
                        $sqlInterview = 'SELECT ibDiplomaCASInterview.*, surname, preferredName FROM ibDiplomaCASInterview JOIN gibbonPerson ON (ibDiplomaCASInterview.1_gibbonPersonIDInterviewer=gibbonPerson.gibbonPersonID) WHERE gibbonPersonIDInterviewee=:gibbonPersonID';
                        $resultInterview = $connection2->prepare($sqlInterview);
                        $resultInterview->execute($dataInterview);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($resultInterview->rowCount() > 1) {
                        echo "<div class='error'>";
                        echo 'Interview cannot be displayed.';
                        echo '</div>';
                    } else {
                        if ($resultInterview->rowCount() == 1) {
                            $rowInterview = $resultInterview->fetch();
                        }

                        echo "<form method='post' action='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/cas_adviseStudents_detailsInterview1Process.php'>";
                        echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
                        echo "<tr class='break'>";
                        echo '<td colspan=2>';
                        echo "<h3 class='top'>Commitment Goals</h3>";
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td colspan=2>';
                        echo '<p>';
                        echo 'Work with the interviewee to determine a suitable, brief goal for each approved commitment.';
                        echo '</p>';

                        try {
                            $dataCommitments = array('gibbonPersonID' => $gibbonPersonID);
                            $sqlCommitments = "SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved' ORDER BY name";
                            $resultCommitments = $connection2->prepare($sqlCommitments);
                            $resultCommitments->execute($dataCommitments);
                        } catch (PDOException $e) {
                            echo "<div class='error'>".$e->getMessage().'</div>';
                        }

                        if ($resultCommitments->rowCount() < 1) {
                            echo "<div class='error'>";
                            echo 'There are no commitments to display.';
                            echo '</div>';
                        } else {
                            echo "<table cellspacing='0' style='margin-top: 0px; width: 100%'>";
                            echo "<tr class='head'>";
                            echo "<th style='vertical-align: bottom'>";
                            echo 'Commitment';
                            echo '</th>';
                            echo "<th style='vertical-align: bottom'>";
                            echo 'Timing';
                            echo '</th>';
                            echo "<th style='vertical-align: bottom; width: 310px'>";
                            echo 'Goals';
                            echo '</th>';
                            echo '</tr>';

                            $count = 0;
                            $rowNum = 'odd';
                            while ($rowCommitments = $resultCommitments->fetch()) {
                                if ($count % 2 == 0) {
                                    $rowNum = 'even';
                                } else {
                                    $rowNum = 'odd';
                                }
                                ++$count;

                                                    //COLOR ROW BY STATUS!
                                                    echo "<tr class=$rowNum>";
                                echo '<td>';
                                echo $rowCommitments['name'];
                                echo '</td>';
                                echo '<td>';
                                if (substr($rowCommitments['dateStart'], 0, 4) == substr($rowCommitments['dateEnd'], 0, 4)) {
                                    if (substr($rowCommitments['dateStart'], 5, 2) == substr($rowCommitments['dateEnd'], 5, 2)) {
                                        echo date('F', mktime(0, 0, 0, substr($rowCommitments['dateStart'], 5, 2))).' '.substr($rowCommitments['dateStart'], 0, 4);
                                    } else {
                                        echo date('F', mktime(0, 0, 0, substr($rowCommitments['dateStart'], 5, 2))).' - '.date('F', mktime(0, 0, 0, substr($rowCommitments['dateEnd'], 5, 2))).' '.substr($rowCommitments['dateStart'], 0, 4);
                                    }
                                } else {
                                    echo date('F', mktime(0, 0, 0, substr($rowCommitments['dateStart'], 5, 2))).' '.substr($rowCommitments['dateStart'], 0, 4).' - '.date('F', mktime(0, 0, 0, substr($rowCommitments['dateEnd'], 5, 2))).' '.substr($rowCommitments['dateEnd'], 0, 4);
                                }
                                echo '</td>';
                                echo '<td>';
                                echo "<input type='hidden' name='$count-ibDiplomaCASCommitmentID' value='".$rowCommitments['ibDiplomaCASCommitmentID']."'>";
                                echo "<input type='text' style='width: 302px' name='$count-goals' id='$count-goals' value='".$rowCommitments['goals']."' maxlength=255>";
                                echo '</td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        }

                        echo '</td>';
                        echo '</tr>';

                        echo "<tr class='break'>";
                        echo '<td colspan=2>';
                        echo '<h3>Notes</h3>';
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td colspan=2>';
                        echo 'Use this space to take notes on your conversation with the student. You may wish to consider:';
                        echo "<i><ul style='margin-bottom: 0px'><li>Is there a balance across commitments?</li><li>Are commitments genuine and meaningful?</li><li>Do commitments require student to show persistence and commitment?</li></ul></i><br/>";
                        echo "<textarea name='notes' id='notes' rows=15 style='width:738px; margin-left: 0px'>";
                        if (isset($rowInterview['1_notes'])) {
                            echo $rowInterview['1_notes'];
                        }
                        echo '</textarea>';
                        echo '</td>';
                        echo '</tr>';

                        echo "<tr class='break'>";
                        echo '<td colspan=2>';
                        echo '<h3>General Information</h3>';
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td>';
                        echo '<b>Interviewer *</b><br/>';
                        echo '</td>';
                        echo "<td class='right'>";
                        if ($resultInterview->rowCount() == 1) {
                            echo "<input readonly maxlength=255 value='".formatName('', $rowInterview['preferredName'], $rowInterview['surname'], 'Staff', true, true)."' type='text' style='width: 300px'>";
                        } else {
                            echo "<input readonly maxlength=255 value='".formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', true, true)."' type='text' style='width: 300px'>";
                        }
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td>';
                        echo '<b>Date *</b><br/>';
                        echo "<span style='font-size: 90%'><i>Format: dd/mm/yyyy.</i></span>";
                        echo '</td>';
                        echo "<td class='right'>";
                        $date = '';
                        if (isset($rowInterview['1_date'])) {
                            $date = dateConvertBack(substr($rowInterview['1_date'], 0, 10));
                        }
                        ?>

										<input name="date" id="date" maxlength=10 value="<?php echo $date ?>" type="text" style="width: 300px">
										<script type="text/javascript">
											var date=new LiveValidation('date');
											date.add( Validate.Format, {pattern: /^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i, failureMessage: "Use dd/mm/yyyy." } );
											date.add(Validate.Presence);
										 </script>
										<script type="text/javascript">
											$(function() {
												$( "#date" ).datepicker();
											});
										</script>
										<?php
                                    echo '</td>';
                        echo '</tr>';

                        ?>
								<tr>
									<td>
										<b>CAS Status *</b><br/>
										<span style="font-size: 90%"><i>Update the student's status within the CAS programme.</i></span>
									</td>
									<td class="right">
										<select name="casStatusSchool" id="casStatusSchool" style="width: 302px">
											<option <?php if ($casStatusSchool == 'At Risk') {
    echo 'selected ';
}
                        ?>value="At Risk">At Risk</option>
											<option <?php if ($casStatusSchool == 'On Task') {
    echo 'selected ';
}
                        ?>value="On Task">On Task</option>
											<option <?php if ($casStatusSchool == 'Excellence') {
    echo 'selected ';
}
                        ?>value="Excellence">Excellence</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>
										<span style="font-size: 90%"><i>* denotes a required field</i></span>
									</td>
									<td class="right">
										<input type="hidden" name="count" value="<?php echo $count ?>">
										<input type="hidden" name="gibbonPersonID" value="<?php echo $gibbonPersonID ?>">
										<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
										<input type="submit" value="Submit">
									</td>
								</tr>
							</table>
							<?php
                        echo '</form>';
                    }
                } elseif ($subpage == 'Interview 2') {
                    try {
                        $dataInterview = array('gibbonPersonID' => $gibbonPersonID);
                        $sqlInterview = 'SELECT ibDiplomaCASInterview.*, surname, preferredName FROM ibDiplomaCASInterview JOIN gibbonPerson ON (ibDiplomaCASInterview.1_gibbonPersonIDInterviewer=gibbonPerson.gibbonPersonID) WHERE gibbonPersonIDInterviewee=:gibbonPersonID';
                        $resultInterview = $connection2->prepare($sqlInterview);
                        $resultInterview->execute($dataInterview);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($resultInterview->rowCount() > 1) {
                        echo "<div class='error'>";
                        echo 'Interview cannot be displayed.';
                        echo '</div>';
                    } else {
                        if ($resultInterview->rowCount() == 0) {
                            echo "<div class='error'>";
                            echo 'You have not yet completed Interview 1, and so cannot access Interview 2.';
                            echo '</div>';
                        } else {
                            $rowInterview = $resultInterview->fetch();

                            echo "<form method='post' action='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/cas_adviseStudents_detailsInterview2Process.php'>";
                            echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
                            echo "<tr class='break'>";
                            echo '<td colspan=2>';
                            echo "<h3 class='top'>Outcomes</h3>";
                            echo '</td>';
                            echo '</tr>';
                            echo '<tr>';
                            echo '<td colspan=2>';
                            echo '<p>';
                            echo 'Work with the interviewee to determine which commitments you think <b>might</b> satisfy each of the outcomes listed below. The student should have pre-filled this information before Interview 2.';
                            echo '</p>';
                            echo '</td>';
                            echo '</tr>';

                            ?>
									<style>
										tr.outcome ul.token-input-list-facebook { width: 738px; height: 25px!important; }
										tr.outcome div.token-input-dropdown-facebook  { width: 738px }
									</style>
									<?php
                                    //Get commitment list
                                    try {
                                        $dataList = array('gibbonPersonID' => $gibbonPersonID);
                                        $sqlList = "SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved' ORDER BY name";
                                        $resultList = $connection2->prepare($sqlList);
                                        $resultList->execute($dataList);
                                    } catch (PDOException $e) {
                                        echo "<div class='error'>".$e->getMessage().'</div>';
                                    }

                            $list = '';
                            while ($rowList = $resultList->fetch()) {
                                $list .= '{id: "'.$rowList['ibDiplomaCASCommitmentID'].'", name: "'.$rowList['name'].'"},';
                            }
                            $list = substr($list, 0, -1);
                            for ($i = 1; $i < 9; ++$i) {
                                echo '<tr>';
                                echo '<td colspan=2> ';
                                switch ($i) {
                                                    case 1:
                                                        $title = "<span style='font-weight: bold' title='They are able to see themselves as individuals with various skills and abilities, some more developed than others, and understand that they can make choices about how they wish to move forward.'>Increased their awareness of their own strengths and areas for growth</span>";
                                                        break;
                                                    case 2:
                                                        $title = "<span style='font-weight: bold' title='A new challenge may be an unfamiliar activity, or an extension to an existing one.'>Undertaken new challenges</span>";
                                                        break;
                                                    case 3:
                                                        $title = "<span style='font-weight: bold' title='Planning and initiation will often be in collaboration with others. It can be shown in activities that are part of larger projects, for example, ongoing school activities in the local community, as well as in small student-led activities.'>Planned and initiated activities</span>";
                                                        break;
                                                    case 4:
                                                        $title = "<span style='font-weight: bold' title='Collaboration can be shown in many different activities, such as team sports, playing music in a band, or helping in a kindergarten. At least one project, involving collaboration and the integration of at least two of creativity, action and service, is required.'>Worked collaboratively with others</span>";
                                                        break;
                                                    case 5:
                                                        $title = "<span style='font-weight: bold' title='At a minimum, this implies attending regularly and accepting a share of the responsibility for dealing with problems that arise in the course of activities.'>Shown perseverance and commitment in their activities</span>";
                                                        break;
                                                    case 6:
                                                        $title = "<span style='font-weight: bold' title='Students may be involved in international projects but there are many global issues that can be acted upon locally or nationally (for example, environmental concerns, caring for the elderly).'>Engaged with issues of global importance</span>";
                                                        break;
                                                    case 7:
                                                        $title = "<span style='font-weight: bold' title='Ethical decisions arise in almost any CAS activity (for example, on the sports field, in musical composition, in relationships with others involved in service activities). Evidence of thinking about ethical issues can be shown in various ways, including journal entries and conversations with CAS advisers.'>Considered the ethical implications of their actions</span>";
                                                        break;
                                                    case 8:
                                                        $title = "<span style='font-weight: bold' title='As with new challenges, new skills may be shown in activities that the student has not previously undertaken, or in increased expertise in an established area.'>Developed new skills</span>";
                                                        break;
                                                }
                                echo "<p style='margin-bottom: 3px'><b>Outcome $i</b> - $title</p>";
                                echo '</td>';
                                echo '</tr>';
                                echo "<tr class='outcome' id='outcome".$i."RowIntention'>";
                                echo '<td colspan=2> ';
                                echo "<input type='text' id='outcome".$i."' name='outcome".$i."' />";
                                $prepopulate = '';
                                $where = '';
                                if ($rowInterview["2_outcome$i"] != '') {
                                    $commitments = explode(',', $rowInterview["2_outcome$i"]);
                                    foreach ($commitments as $commitment) {
                                        $where .= 'ibDiplomaCASCommitmentID='.$commitment.' OR ';
                                    }
                                    $where = substr($where, 0, -4);
                                    try {
                                        $sqlPrepopulate = "SELECT * FROM ibDiplomaCASCommitment WHERE $where";
                                        $resultPrepopulate = $connection2->query($sqlPrepopulate);
                                    } catch (PDOException $e) {
                                        echo "<div class='error'>".$e->getMessage().'</div>';
                                    }
                                    while ($rowPrepopulate = $resultPrepopulate->fetch()) {
                                        $prepopulate .= '{id: "'.$rowPrepopulate['ibDiplomaCASCommitmentID'].'", name: "'.$rowPrepopulate['name'].'"},';
                                    }
                                    if ($prepopulate != '') {
                                        $prepopulate = substr($prepopulate, 0, -1);
                                    }
                                }
                                echo "<script type='text/javascript'>";
                                echo '$(document).ready(function() {';
                                echo ' $("#outcome'.$i.'").tokenInput([';
                                echo $list;
                                echo '],';
                                echo '{theme: "facebook",';
                                echo 'hintText: "Start typing a tag...",';
                                echo 'allowCreation: false,';
                                if ($prepopulate != '') {
                                    echo "prePopulate: [$prepopulate],";
                                }
                                echo 'preventDuplicates: true});';
                                echo '});';
                                echo '</script>';

                                echo '</td>';
                                echo '</tr>';
                            }

                            echo "<tr class='break'>";
                            echo '<td colspan=2>';
                            echo '<h3>Notes</h3>';
                            echo '</td>';
                            echo '</tr>';
                            echo '<tr>';
                            echo '<td colspan=2>';
                            echo 'Use this space to take notes on your conversation with the student. You may wish to consider:';
                            echo "<i><ul style='margin-bottom: 0px'><li>How is student progressing?</li><li>Are all outcomes begun?</li><li>Which outcomes require more thought and action?</li></ul></i><br/>";
                            echo "<textarea name='notes' id='notes' rows=15 style='width:738px; margin-left: 0px'>".$rowInterview['2_notes'].'</textarea>';
                            echo '</td>';
                            echo '</tr>';

                            echo "<tr class='break'>";
                            echo '<td colspan=2>';
                            echo '<h3>General Information</h3>';
                            echo '</td>';
                            echo '</tr>';
                            echo '<tr>';
                            echo '<td>';
                            echo '<b>Interviewer *</b><br/>';
                            echo '</td>';
                            echo "<td class='right'>";
                            if (!(is_null($rowInterview['2_gibbonPersonIDInterviewer']))) {
                                echo "<input readonly maxlength=255 value='".formatName('', $rowInterview['preferredName'], $rowInterview['surname'], 'Staff', true, true)."' type='text' style='width: 300px'>";
                            } else {
                                echo "<input readonly maxlength=255 value='".formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', true, true)."' type='text' style='width: 300px'>";
                            }
                            echo '</td>';
                            echo '</tr>';
                            echo '<tr>';
                            echo '<td>';
                            echo '<b>Date *</b><br/>';
                            echo "<span style='font-size: 90%'><i>Format: dd/mm/yyyy.</i></span>";
                            echo '</td>';
                            echo "<td class='right'>";
                            $date = '';
                            if ($rowInterview['2_date'] != '') {
                                $date = dateConvertBack($guid, substr($rowInterview['2_date'], 0, 10));
                            }
                            ?>

											<input name="date" id="date" maxlength=10 value="<?php echo $date ?>" type="text" style="width: 300px">
											<script type="text/javascript">
												var date=new LiveValidation('date');
												date.add( Validate.Format, {pattern: /^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i, failureMessage: "Use dd/mm/yyyy." } );
												date.add(Validate.Presence);
											 </script>
											<script type="text/javascript">
												$(function() {
													$( "#date" ).datepicker();
												});
											</script>
											<?php
                                        echo '</td>';
                            echo '</tr>';

                            ?>
									<tr>
										<td>
											<b>CAS Status *</b><br/>
											<span style="font-size: 90%"><i>Update the student's status within the CAS programme.</i></span>
										</td>
										<td class="right">
											<select name="casStatusSchool" id="casStatusSchool" style="width: 302px">
												<option <?php if ($casStatusSchool == 'At Risk') {
    echo 'selected ';
}
                            ?>value="At Risk">At Risk</option>
												<option <?php if ($casStatusSchool == 'On Task') {
    echo 'selected ';
}
                            ?>value="On Task">On Task</option>
												<option <?php if ($casStatusSchool == 'Excellence') {
    echo 'selected ';
}
                            ?>value="Excellence">Excellence</option>
											</select>
										</td>
									</tr>
									<tr>
										<td>
											<span style="font-size: 90%"><i>* denotes a required field</i></span>
										</td>
										<td class="right">
											<input type="hidden" name="count" value="<?php echo $count ?>">
											<input type="hidden" name="gibbonPersonID" value="<?php echo $gibbonPersonID ?>">
											<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
											<input type="submit" value="Submit">
										</td>
									</tr>
								</table>
								<?php
                            echo '</form>';
                        }
                    }
                } elseif ($subpage == 'Interview 3') {
                    try {
                        $dataInterview = array('gibbonPersonID' => $gibbonPersonID);
                        $sqlInterview = 'SELECT ibDiplomaCASInterview.*, surname, preferredName FROM ibDiplomaCASInterview JOIN gibbonPerson ON (ibDiplomaCASInterview.1_gibbonPersonIDInterviewer=gibbonPerson.gibbonPersonID) WHERE gibbonPersonIDInterviewee=:gibbonPersonID';
                        $resultInterview = $connection2->prepare($sqlInterview);
                        $resultInterview->execute($dataInterview);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($resultInterview->rowCount() > 1) {
                        echo "<div class='error'>";
                        echo 'Interview cannot be displayed.';
                        echo '</div>';
                    } else {
                        if ($resultInterview->rowCount() == 0) {
                            echo "<div class='error'>";
                            echo 'You have not yet completed Interview 1, and so cannot access Interview 2.';
                            echo '</div>';
                        } else {
                            $rowInterview = $resultInterview->fetch();

                            if (is_null($rowInterview['2_date'])) {
                                echo "<div class='error'>";
                                echo 'You have not yet completed Interview 2, and so cannot access Interview 3.';
                                echo '</div>';
                            } else {
                                echo "<form method='post' action='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/cas_adviseStudents_detailsInterview3Process.php'>";
                                echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
                                echo "<tr class='break'>";
                                echo '<td colspan=2>';
                                echo "<h3 class='top'>Outcomes</h3>";
                                echo '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td colspan=2>';
                                echo '<p>';
                                echo 'Work with the interviewee to determine which commitments you think <b>have</b> satisfied each of the outcomes listed below. The student should have pre-filled this information before Interview 3. Use the second box for each outcome to record notes from your discussion.';
                                echo '</p>';
                                echo '</td>';
                                echo '</tr>';

                                ?>
										<style>
											tr.outcome ul.token-input-list-facebook { width: 738px; height: 25px!important; }
											tr.outcome div.token-input-dropdown-facebook  { width: 738px }
										</style>
										<?php
                                        //Get commitment list
                                        try {
                                            $dataList = array('gibbonPersonID' => $gibbonPersonID);
                                            $sqlList = "SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved' ORDER BY name";
                                            $resultList = $connection2->prepare($sqlList);
                                            $resultList->execute($dataList);
                                        } catch (PDOException $e) {
                                            echo "<div class='error'>".$e->getMessage().'</div>';
                                        }

                                $list = '';
                                while ($rowList = $resultList->fetch()) {
                                    $list .= '{id: "'.$rowList['ibDiplomaCASCommitmentID'].'", name: "'.$rowList['name'].'"},';
                                }
                                $list = substr($list, 0, -1);
                                for ($i = 1; $i < 9; ++$i) {
                                    echo '<tr>';
                                    echo '<td colspan=2> ';
                                    switch ($i) {
                                                        case 1:
                                                            $title = "<span style='font-weight: bold' title='They are able to see themselves as individuals with various skills and abilities, some more developed than others, and understand that they can make choices about how they wish to move forward.'>Increased their awareness of their own strengths and areas for growth</span>";
                                                            break;
                                                        case 2:
                                                            $title = "<span style='font-weight: bold' title='A new challenge may be an unfamiliar activity, or an extension to an existing one.'>Undertaken new challenges</span>";
                                                            break;
                                                        case 3:
                                                            $title = "<span style='font-weight: bold' title='Planning and initiation will often be in collaboration with others. It can be shown in activities that are part of larger projects, for example, ongoing school activities in the local community, as well as in small student-led activities.'>Planned and initiated activities</span>";
                                                            break;
                                                        case 4:
                                                            $title = "<span style='font-weight: bold' title='Collaboration can be shown in many different activities, such as team sports, playing music in a band, or helping in a kindergarten. At least one project, involving collaboration and the integration of at least two of creativity, action and service, is required.'>Worked collaboratively with others</span>";
                                                            break;
                                                        case 5:
                                                            $title = "<span style='font-weight: bold' title='At a minimum, this implies attending regularly and accepting a share of the responsibility for dealing with problems that arise in the course of activities.'>Shown perseverance and commitment in their activities</span>";
                                                            break;
                                                        case 6:
                                                            $title = "<span style='font-weight: bold' title='Students may be involved in international projects but there are many global issues that can be acted upon locally or nationally (for example, environmental concerns, caring for the elderly).'>Engaged with issues of global importance</span>";
                                                            break;
                                                        case 7:
                                                            $title = "<span style='font-weight: bold' title='Ethical decisions arise in almost any CAS activity (for example, on the sports field, in musical composition, in relationships with others involved in service activities). Evidence of thinking about ethical issues can be shown in various ways, including journal entries and conversations with CAS advisers.'>Considered the ethical implications of their actions</span>";
                                                            break;
                                                        case 8:
                                                            $title = "<span style='font-weight: bold' title='As with new challenges, new skills may be shown in activities that the student has not previously undertaken, or in increased expertise in an established area.'>Developed new skills</span>";
                                                            break;
                                                    }
                                    echo "<p style='margin-bottom: 3px'><b>Outcome $i</b> - $title</p>";
                                    echo '</td>';
                                    echo '</tr>';
                                    echo "<tr class='outcome' id='outcome".$i."RowIntention'>";
                                    echo '<td colspan=2> ';
                                    echo "<input class='outcome' type='text' id='outcome".$i."' name='outcome".$i."' />";
                                    $prepopulate = '';
                                    $where = '';
                                    if ($rowInterview["3_outcome$i"] != '') {
                                        $commitments = explode(',', $rowInterview["3_outcome$i"]);
                                        foreach ($commitments as $commitment) {
                                            $where .= 'ibDiplomaCASCommitmentID='.$commitment.' OR ';
                                        }
                                        $where = substr($where, 0, -4);
                                        try {
                                            $sqlPrepopulate = "SELECT * FROM ibDiplomaCASCommitment WHERE $where";
                                            $resultPrepopulate = $connection2->query($sqlPrepopulate);
                                        } catch (PDOException $e) {
                                            echo "<div class='error'>".$e->getMessage().'</div>';
                                        }
                                        while ($rowPrepopulate = $resultPrepopulate->fetch()) {
                                            $prepopulate .= '{id: "'.$rowPrepopulate['ibDiplomaCASCommitmentID'].'", name: "'.$rowPrepopulate['name'].'"},';
                                        }
                                        if ($prepopulate != '') {
                                            $prepopulate = substr($prepopulate, 0, -1);
                                        }
                                    }
                                    echo "<script type='text/javascript'>";
                                    echo '$(document).ready(function() {';
                                    echo ' $("#outcome'.$i.'").tokenInput([';
                                    echo $list;
                                    echo '],';
                                    echo '{theme: "facebook",';
                                    echo 'hintText: "Start typing a tag...",';
                                    echo 'allowCreation: false,';
                                    if ($prepopulate != '') {
                                        echo "prePopulate: [$prepopulate],";
                                    }
                                    echo 'preventDuplicates: true});';
                                    echo '});';
                                    echo '</script>';
                                    echo "<textarea name='outcome".$i."Notes' id='outcome".$i."Notes' rows=3 style='width:738px; margin: 0px 0px 10px 0px'>".$rowInterview['3_outcome'.$i.'Notes'].'</textarea>';
                                    echo '</td>';
                                    echo '</tr>';
                                }

                                echo "<tr class='break'>";
                                echo '<td colspan=2>';
                                echo '<h3>Notes</h3>';
                                echo '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td colspan=2>';
                                echo 'Use this space to take notes on your conversation with the student. You may wish to consider:';
                                echo "<i><ul style='margin-bottom: 0px'><li>Are all outcomes satisfactorily completed?</li></ul></i><br/>";
                                echo "<textarea name='notes' id='notes' rows=15 style='width:738px; margin-left: 0px'>".$rowInterview['3_notes'].'</textarea>';
                                echo '</td>';
                                echo '</tr>';

                                echo "<tr class='break'>";
                                echo '<td colspan=2>';
                                echo '<h3>General Information</h3>';
                                echo '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>';
                                echo '<b>Interviewer *</b><br/>';
                                echo '</td>';
                                echo "<td class='right'>";
                                if (!(is_null($rowInterview['3_gibbonPersonIDInterviewer']))) {
                                    echo "<input readonly maxlength=255 value='".formatName('', $rowInterview['preferredName'], $rowInterview['surname'], 'Staff', true, true)."' type='text' style='width: 300px'>";
                                } else {
                                    echo "<input readonly maxlength=255 value='".formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', true, true)."' type='text' style='width: 300px'>";
                                }
                                echo '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>';
                                echo '<b>Date *</b><br/>';
                                echo "<span style='font-size: 90%'><i>Format: dd/mm/yyyy.</i></span>";
                                echo '</td>';
                                echo "<td class='right'>";
                                $date = '';
                                if ($rowInterview['3_date'] != '') {
                                    $date = dateConvertBack($guid, substr($rowInterview['3_date'], 0, 10));
                                }
                                ?>

												<input name="date" id="date" maxlength=10 value="<?php echo $date ?>" type="text" style="width: 300px">
												<script type="text/javascript">
													var date=new LiveValidation('date');
													date.add( Validate.Format, {pattern: /^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i, failureMessage: "Use dd/mm/yyyy." } );
													date.add(Validate.Presence);
												 </script>
												<script type="text/javascript">
													$(function() {
														$( "#date" ).datepicker();
													});
												</script>
												<?php
                                            echo '</td>';
                                echo '</tr>';

                                ?>
										<tr>
											<td>
												<b>CAS Status *</b><br/>
												<span style="font-size: 90%"><i>Update the student's status within the CAS programme.</i></span>
											</td>
											<td class="right">
												<select name="casStatusSchool" id="casStatusSchool" style="width: 302px">
													<option <?php if ($casStatusSchool == 'Complete') {
    echo 'selected ';
}
                                ?>value="Complete">Complete</option>
													<option <?php if ($casStatusSchool == 'Incomplete') {
    echo 'selected ';
}
                                ?>value="Incomplete">Incomplete</option>
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<span style="font-size: 90%"><i>* denotes a required field</i></span>
											</td>
											<td class="right">
												<input type="hidden" name="count" value="<?php echo $count ?>">
												<input type="hidden" name="gibbonPersonID" value="<?php echo $gibbonPersonID ?>">
												<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
												<input type="submit" value="Submit">
											</td>
										</tr>
										<tr>
										</tr>
									</table>
									<?php
                                echo '</form>';
                            }
                        }
                    }
                }

                //Set sidebar
                $_SESSION[$guid]['sidebarExtra'] = getUserPhoto($guid, $image_240, 240);
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].'<br>';
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].'<ul>';
                $style = '';
                if ($subpage == 'Overview') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Overview'>Overview</a></li>";

                $style = '';
                if ($subpage == 'Reflection') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Reflection'>Reflection</a></li>";

                $style = '';
                if ($subpage == 'CAS Status') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=CAS Status'>CAS Status</a></li>";

                $style = '';
                if ($subpage == 'Interview 1') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Interview 1'>Interview 1</a></li>";

                $style = '';
                if ($subpage == 'Interview 2') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Interview 2'>Interview 2</a></li>";

                $style = '';
                if ($subpage == 'Interview 3') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Interview 3'>Interview 3</a></li>";

                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].'</ul>';
            }
        }
    }
}
?>

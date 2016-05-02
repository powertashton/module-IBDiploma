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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_iboCheck_details.php') == false) {

    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    $gibbonPersonID = $_GET['gibbonPersonID'];
    if ($gibbonPersonID == '') {
        echo "<div class='error'>";
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
            $row = $result->fetch();

            echo "<div class='trail'>";
            echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/cas_iboCheck.php'>IBO CAS Check</a> > </div><div class='trailEnd'>Student Details</div>";
            echo '</div>';

            if (isset($_GET['updateReturn'])) {
                $updateReturn = $_GET['updateReturn'];
            } else {
                $updateReturn = '';
            }
            $updateReturnMessage = '';
            $class = 'error';
            if (!($updateReturn == '')) {
                if ($updateReturn == 'fail0') {
                    $updateReturnMessage = 'Update failed because you do not have access to this action.';
                } elseif ($updateReturn == 'fail1') {
                    $updateReturnMessage = 'Update failed because a required parameter was not set.';
                } elseif ($updateReturn == 'fail2') {
                    $updateReturnMessage = 'Update failed due to a database error.';
                } elseif ($updateReturn == 'fail3') {
                    $updateReturnMessage = 'Update failed because your inputs were invalid.';
                } elseif ($updateReturn == 'success0') {
                    $updateReturnMessage = 'Update was successful.';
                    $class = 'success';
                }
                echo "<div class='$class'>";
                echo $updateReturnMessage;
                echo '</div>';
            }

            if (isset($_GET['deleteReturn'])) {
                $deleteReturn = $_GET['deleteReturn'];
            } else {
                $deleteReturn = '';
            }
            $deleteReturnMessage = '';
            $class = 'error';
            if (!($deleteReturn == '')) {
                if ($deleteReturn == 'success0') {
                    $deleteReturnMessage = 'Delete was successful.';
                    $class = 'success';
                }
                echo "<div class='$class'>";
                echo $deleteReturnMessage;
                echo '</div>';
            }

            echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
            echo '<tr>';
            echo "<td style='width: 34%; vertical-align: top'>";
            echo "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>";
            echo formatName('', $row['preferredName'], $row['surname'], 'Student', true, true);
            echo '</td>';
            echo "<td style='width: 33%; vertical-align: top'>";
            echo "<span style='font-size: 115%; font-weight: bold'>Year Group</span><br/>";
            try {
                $dataDetail = array('gibbonYearGroupID' => $row['gibbonYearGroupID']);
                $sqlDetail = 'SELECT * FROM gibbonYearGroup WHERE gibbonYearGroupID=:gibbonYearGroupID';
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
            echo '</tr>';
            echo '<tr>';
            echo "<td style='padding-top: 15px; width: 34%; vertical-align: top' colspan=3>";
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

            echo '<h2>';
            echo 'Commitments';
            echo '</h2>';

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
                echo 'Actions';
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
                    echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/cas_iboCheck_full.php&gibbonPersonID=$gibbonPersonID&ibDiplomaCASCommitmentID=".$row['ibDiplomaCASCommitmentID']."&width=1000&height=550'><img title='View' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_right.png'/></a> ";
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';

                echo '<h2>';
                echo 'Reflections';
                echo '</h2>';
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
					<script type="text/javascript">
					$(document).ready(function() {
						$('.searchInput').val(1);
						$('.body').find("tr:odd").addClass('odd');
						$('.body').find("tr:even").addClass('even');
							
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
                    echo 'Action';
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
                        echo dateConvertBack(substr($row['timestamp'], 0, 10));
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
                        echo "<td style='background-color: #D4F6DC' colspan=4>";
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
            }
        }
    }
}
?>
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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_interview3.php') == false) {

    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Acess denied
        echo "<div class='error'>";
        echo 'You are not enroled in the IB Diploma programme.';
        echo '</div>';
    } else {
        echo "<div class='trail'>";
        echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > </div><div class='trailEnd'>Student: Interview 3</div>";
        echo '</div>';
        echo '<p>';
        echo 'This page allows you to pre-enter information about your outcomes prior to Interview 3. For each of the 8 outcomes below, indicate which commitments you think <b>have</b> satisfied that outcome. In the interview you will be asked to give verbal explanations and evidence (e.g. certificates) of how you met the outcomes.';
        echo '</p>';

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        try {
            $dataInterview = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
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
                echo 'You have not yet completed Interview 1, and so cannot prepare for Interview 2.';
                echo '</div>';
            } else {
                $rowInterview = $resultInterview->fetch();

                if (is_null($rowInterview['2_date'])) {
                    echo "<div class='error'>";
                    echo 'You have not yet completed Interview 2, and so cannot prepare for Interview 3.';
                    echo '</div>';
                } else {
                    ?>
					<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cas_student_interview3Process.php' ?>">
						<table class='smallIntBorder' cellspacing='0' style="width: 100%">
							<style>
								tr.outcome ul.token-input-list-facebook { width: 738px; height: 25px!important; }
								tr.outcome div.token-input-dropdown-facebook  { width: 738px }
							</style>
							<?php
                            //Get commitment list
                            try {
                                $dataList = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
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
                        echo "<tr class='break'>";
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
                        echo "<h3>Outcome $i</h3>";
                        echo "<p>$title</p>";
                        echo '</td>';
                        echo '</tr>';
                        echo "<tr class='outcome' id='outcome".$i."RowIntention'>";
                        echo '<td colspan=2> ';
                        echo "<input type='text' id='outcome".$i."' name='outcome".$i."' />";
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

                        echo '</td>';
                        echo '</tr>';
                    }
                    ?>

							<tr>
								<td>
									<span style="font-size: 90%"><i>* denotes a required field</i></span>
								</td>
								<td class="right">
									<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
									<input type="submit" value="Submit">
								</td>
							</tr>
						</table>
					</form>
					<?php

                }
            }
        }
    }
}
?>

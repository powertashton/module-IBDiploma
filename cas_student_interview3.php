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

@session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_student_interview3.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	if (enroled($guid, $_SESSION[$guid]["gibbonPersonID"], $connection2)==FALSE) {
		//Acess denied
		print "<div class='error'>" ;
			print "You are not enroled in the IB Diploma programme." ;
		print "</div>" ;
	}
	else {
		print "<div class='trail'>" ;
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Student: Interview 3</div>" ;
		print "</div>" ;
		print "<p>" ;
			print "This page allows you to pre-enter information about your outcomes prior to Interview 3. For each of the 8 outcomes below, indicate which commitments you think <b>have</b> satisfied that outcome. In the interview you will be asked to give verbal explanations and evidence (e.g. certificates) of how you met the outcomes." ;
		print "</p>" ;
		
		if (isset($_GET["updateReturn"])) { $updateReturn=$_GET["updateReturn"] ; } else { $updateReturn="" ; }
		$updateReturnMessage ="" ;
		$class="error" ;
		if (!($updateReturn=="")) {
			if ($updateReturn=="fail0") {
				$updateReturnMessage ="Update failed because you do not have access to this action." ;	
			}
			else if ($updateReturn=="fail1") {
				$updateReturnMessage ="Update failed because a required parameter was not set." ;	
			}
			else if ($updateReturn=="fail2") {
				$updateReturnMessage ="Update failed due to a database error." ;	
			}
			else if ($updateReturn=="fail3") {
				$updateReturnMessage ="Update failed because your inputs were invalid." ;	
			}
			else if ($updateReturn=="fail4") {
				$updateReturnMessage ="Update failed some values need to be unique but were not." ;	
			}
			else if ($updateReturn=="fail5") {
				$updateReturnMessage ="Update failed because your attachment could not be uploaded." ;	
			}
			else if ($updateReturn=="success0") {
				$updateReturnMessage ="Update was successful." ;	
				$class="success" ;
			}
			print "<div class='$class'>" ;
				print $updateReturnMessage;
			print "</div>" ;
		} 
		
		try {
			$dataInterview=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]);  
			$sqlInterview="SELECT ibDiplomaCASInterview.*, surname, preferredName FROM ibDiplomaCASInterview JOIN gibbonPerson ON (ibDiplomaCASInterview.1_gibbonPersonIDInterviewer=gibbonPerson.gibbonPersonID) WHERE gibbonPersonIDInterviewee=:gibbonPersonID" ;
			$resultInterview=$connection2->prepare($sqlInterview);
			$resultInterview->execute($dataInterview);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}

		if ($resultInterview->rowCount()>1) {
			print "<div class='error'>" ;
			print "Interview cannot be displayed." ;
			print "</div>" ;
		}
		else {
			if ($resultInterview->rowCount()==0) {
				print "<div class='error'>" ;
				print "You have not yet completed Interview 1, and so cannot prepare for Interview 2." ;
				print "</div>" ;
			}
			else {
				$rowInterview=$resultInterview->fetch() ;
				
				if (is_null($rowInterview["2_date"])) {
					print "<div class='error'>" ;
					print "You have not yet completed Interview 2, and so cannot prepare for Interview 3." ;
					print "</div>" ;
				}
				else {
			
					?>
					<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/cas_student_interview3Process.php" ?>">
						<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
							<style>
								ul.token-input-list-facebook { width: 738px; height: 25px!important; }
								div.token-input-dropdown-facebook  { width: 738px }
							</style>
							<?php
							//Get commitment list
							try {
								$dataList=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]);  
								$sqlList="SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved' ORDER BY name" ; 
								$resultList=$connection2->prepare($sqlList);
								$resultList->execute($dataList);
							}
							catch(PDOException $e) { 
								print "<div class='error'>" . $e->getMessage() . "</div>" ; 
							}
							
							$list="" ;
							while ($rowList=$resultList->fetch()) {
								$list.="{id: \"" . $rowList["ibDiplomaCASCommitmentID"] . "\", name: \"" . $rowList["name"] . "\"}," ;
							}
							$list=substr($list,0,-1) ;
							for ($i=1; $i<9; $i++) {
								print "<tr class='break'>" ;
									print "<td colspan=2> " ;
										switch ($i) {
											case 1:
												$title="<span style='font-weight: bold' title='They are able to see themselves as individuals with various skills and abilities, some more developed than others, and understand that they can make choices about how they wish to move forward.'>Increased their awareness of their own strengths and areas for growth</span>";
												break;
											case 2:
												$title="<span style='font-weight: bold' title='A new challenge may be an unfamiliar activity, or an extension to an existing one.'>Undertaken new challenges</span>";
												break;
											case 3:
												$title="<span style='font-weight: bold' title='Planning and initiation will often be in collaboration with others. It can be shown in activities that are part of larger projects, for example, ongoing school activities in the local community, as well as in small student-led activities.'>Planned and initiated activities</span>";
												break;
											case 4:
												$title="<span style='font-weight: bold' title='Collaboration can be shown in many different activities, such as team sports, playing music in a band, or helping in a kindergarten. At least one project, involving collaboration and the integration of at least two of creativity, action and service, is required.'>Worked collaboratively with others</span>";
												break;
											case 5:
												$title="<span style='font-weight: bold' title='At a minimum, this implies attending regularly and accepting a share of the responsibility for dealing with problems that arise in the course of activities.'>Shown perseverance and commitment in their activities</span>";
												break;
											case 6:
												$title="<span style='font-weight: bold' title='Students may be involved in international projects but there are many global issues that can be acted upon locally or nationally (for example, environmental concerns, caring for the elderly).'>Engaged with issues of global importance</span>";
												break;
											case 7:
												$title="<span style='font-weight: bold' title='Ethical decisions arise in almost any CAS activity (for example, on the sports field, in musical composition, in relationships with others involved in service activities). Evidence of thinking about ethical issues can be shown in various ways, including journal entries and conversations with CAS advisers.'>Considered the ethical implications of their actions</span>";
												break;
											case 8:
												$title="<span style='font-weight: bold' title='As with new challenges, new skills may be shown in activities that the student has not previously undertaken, or in increased expertise in an established area.'>Developed new skills</span>";
												break;
										}
										print "<h3>Outcome $i</h3>" ;
										print "<p>$title</p>" ;
									print "</td>" ;
								print "</tr>" ;
								print "<tr id='outcome" . $i . "RowIntention'>" ;
									print "<td colspan=2> " ;
										print "<input type='text' id='outcome" . $i . "' name='outcome" . $i . "' />" ;
										$prepopulate="" ;
										$where="" ;
										if ($rowInterview["3_outcome$i"]!="") {
											$commitments=explode(",", $rowInterview["3_outcome$i"]) ;
											foreach ($commitments as $commitment) {
												$where.="ibDiplomaCASCommitmentID=" . $commitment . " OR " ;
											}
											$where=substr($where,0,-4) ;
											try {
												$sqlPrepopulate="SELECT * FROM ibDiplomaCASCommitment WHERE $where" ;
												$resultPrepopulate=$connection2->query($sqlPrepopulate);   
											}
											catch(PDOException $e) { 
												print "<div class='error'>" . $e->getMessage() . "</div>" ; 
											}			
											while ($rowPrepopulate=$resultPrepopulate->fetch()) {
												$prepopulate.="{id: \"" . $rowPrepopulate["ibDiplomaCASCommitmentID"] . "\", name: \"" . $rowPrepopulate["name"] . "\"}," ;
											}
											if ($prepopulate!="") {
												$prepopulate=substr($prepopulate,0,-1) ;
											}
										}
										print "<script type='text/javascript'>" ;
										print "$(document).ready(function() {" ;
											print " $(\"#outcome" . $i . "\").tokenInput([" ; 
												print $list ;
											print "]," ; 
												print "{theme: \"facebook\"," ;
												print "hintText: \"Start typing a tag...\"," ;
												print "allowCreation: false," ;
												if ($prepopulate!="") {
													print "prePopulate: [$prepopulate]," ;
												}
												print "preventDuplicates: true});" ;
										print "});" ;
										print "</script>" ;
										
									print "</td>" ;
								print "</tr>" ;
							}
							?>
							
							<tr>
								<td>
									<span style="font-size: 90%"><i>* denotes a required field</i></span>
								</td>
								<td class="right">
									<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
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
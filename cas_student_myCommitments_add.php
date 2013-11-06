<?
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

session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_student_myCommitments_add.php")==FALSE) {
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
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/cas_student_myCommitments.php'>My Commitments</a> > </div><div class='trailEnd'>Add Commitment</div>" ;
		print "</div>" ;
		
		$addReturn = $_GET["addReturn"] ;
		$addReturnMessage ="" ;
		$class="error" ;
		if (!($addReturn=="")) {
			if ($addReturn=="fail0") {
				$addReturnMessage ="Add failed because you do not have access to this action." ;	
			}
			else if ($addReturn=="fail2") {
				$addReturnMessage ="Add failed due to a database error." ;	
			}
			else if ($addReturn=="fail3") {
				$addReturnMessage ="Add failed because your inputs were invalid." ;	
			}
			else if ($addReturn=="fail4") {
				$addReturnMessage ="Add failed because you already have a commitment with the specified name." ;	
			}
			else if ($addReturn=="fail5") {
				$addReturnMessage ="Add succeeded, but there were problems uploading one or more attachments." ;	
			}
			else if ($addReturn=="success0") {
				$addReturnMessage ="Add was successful. You can add another record if you wish." ;	
				$class="success" ;
			}
			print "<div class='$class'>" ;
				print $addReturnMessage;
			print "</div>" ;
		} 
	
		$step=$_GET["step"] ;
		if ($step!=1 AND $step!=2) {
			$step=1 ;
		}
		
		//Step 1
		if ($step==1) {
			?>
			<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/cas_student_myCommitments_add.php&step=2" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr class='break'>
						<td colspan=2> 
							<h3 class='top'>Commitment Source</h3>
						</td>
					</tr>
					
					<script type="text/javascript">
						$(document).ready(function(){
							$("#activityRow").css("display","none");
							
							$(".type1").click(function(){
								if ($('input[name=type1]:checked').val() == "New" ) {
									$("#activityRow").css("display","none");
								} else {
									$("#activityRow").slideDown("fast", $("#activityRow").css("display","table-row")); //Slide Down Effect
								}
							 });
						});
					</script>
					<tr>
						<td> 
							<b>Commitment Type *</b><br/>
						</td>
						<td class="right">
							<input type="radio" name="type1" value="New" class="type1" /> New <b><u>or</u></b>
							<input type="radio" name="type1" value="From School Activity" class="type1" style='margin-left: 3px'/> From School Activity
						</td>
					</tr>
					<tr id="activityRow">
						<td> 
							<b>Choose Activity</b><br/>
						</td>
						<td class="right">
							<select style="width: 302px" name="gibbonActivityID" id="gibbonActivityID">
								<?
								try {
									$dataSelect=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"], "gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"]);  
									$sqlSelect="SELECT * FROM gibbonActivity JOIN gibbonActivityStudent ON (gibbonActivity.gibbonActivityID=gibbonActivityStudent.gibbonActivityID) WHERE active='Y' AND gibbonPersonID=:gibbonPersonID AND gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY name" ;
									$resultSelect=$connection2->prepare($sqlSelect);
									$resultSelect->execute($dataSelect); 
								}
								catch(PDOException $e) { }

								print "<option value='Please select...'>Please select...</option>" ;
								while ($rowSelect=$resultSelect->fetch()) {
									$selected="" ;
									if ($row["gibbonPersonIDCASAdvisor"]==$rowSelect["gibbonPersonID"]) { 
										$selected="selected" ;
									}
									print "<option $selected value='" . $rowSelect["gibbonActivityID"] . "'>" . htmlPrep($rowSelect["name"]) . "</option>" ;
								}
								?>				
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<span style="font-size: 90%"><i>* denotes a required field</i></span>
						</td>
						<td class="right">
							<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="Go">
						</td>
					</tr>
				</table>
			<?
		}
		else {
			$type=$_POST["type1"] ;
			if ($type!="New" AND $type!="From School Activity") {
				$type="New" ;
			}
			if ($type=="From School Activity") {
				$gibbonActivityID=$_POST["gibbonActivityID"] ;
				if ($gibbonActivityID=="") {
					print "<div class='warning'>" ;
						print "You have not specified an activity." ;
					print "</div>" ;
				}
				else {
					try {
						$dataActivity=array("gibbonActivityID"=>$gibbonActivityID);  
						$sqlActivity="SELECT * FROM gibbonActivity WHERE gibbonActivityID=:gibbonActivityID" ;
						$resultActivity=$connection2->prepare($sqlActivity);
						$resultActivity->execute($dataActivity); 
					}
					catch(PDOException $e) { 
						print "<div class='error'>" . $e->getMessage() . "</div>" ; 
					}

					if ($resultActivity->rowCount()!=1) {
						print "<div class='warning'>" ;
							print "The specified activity does not exist." ;
						print "</div>" ;
					}
					else {
						$rowActivity=$resultActivity->fetch() ;
					}
				}
			}
		
			?>
			<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/cas_student_myCommitments_addProcess.php" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr class='break'>
						<td colspan=2> 
							<h3 class='top'>Basic Information</h3>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Name *</b><br/>
							<span style="font-size: 90%"><i>Must be unique.</i></span>
						</td>
						<td class="right">
							<input type='text' style='width: 302px' name='name' id='name' value='<? print $rowActivity["name"] ?>' maxlength=50>
							<script type="text/javascript">
								var name = new LiveValidation('name');
								name.add(Validate.Presence);
							 </script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Status *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select name="status" id="status" style="width: 302px">
								<option value="Planning">Planning</option>
								<option value="In Progress">In Progress</option>
							</select>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Start Date *</b><br/>
							<span style="font-size: 90%"><i>dd/mm/yyyy</i></span>
						</td>
						<td class="right">
							<input name="dateStart" id="dateStart" maxlength=10 <? if ($rowActivity["programStart"]!="") { print "value='" . dateConvertBack($rowActivity["programStart"]) . "'" ; } ?> type="text" style="width: 300px">
							<script type="text/javascript">
								var dateStart = new LiveValidation('dateStart');
								dateStart.add( Validate.Format, {pattern: /^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i, failureMessage: "Use dd/mm/yyyy." } ); 
							 	dateStart.add(Validate.Presence);
							 </script>
							 <script type="text/javascript">
								$(function() {
									$( "#dateStart" ).datepicker();
								});
							</script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>End Date</b><br/>
							<span style="font-size: 90%"><i>dd/mm/yyyy</i></span>
						</td>
						<td class="right">
							<input name="dateEnd" id="dateEnd" maxlength=10 value="<? print dateConvertBack($rowActivity["programEnd"]) ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								var dateEnd = new LiveValidation('dateEnd');
								dateEnd.add( Validate.Format, {pattern: /^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i, failureMessage: "Use dd/mm/yyyy." } ); 
							 </script>
							 <script type="text/javascript">
								$(function() {
									$( "#dateEnd" ).datepicker();
								});
							</script>
						</td>
					</tr>
					<tr>
						<td colspan=2> 
							<b>Description</b><br/>
							Use this space to describe the activity you are undertaking. You may wish to include:<i><ul><li>What is the nature of the activity?</li><li>How long will it last?</li><li>How frequently will your take part?</li><li>How is it new and challenging?</li><li>What do you hope to accomplish?</li></ul></i><br/>
							<? print "<textarea name='description' id='description' rows=15 style='width:738px; margin-left: 0px'>" . $row["description"] . "</textarea>" ?>
						</td>
					</tr>
					
					<?
					if ($type=="From School Activity" AND $gibbonActivityID!="") {
						try {
							$dataCoord=array("gibbonActivityID"=>$gibbonActivityID);  
							$sqlCoord="SELECT surname, preferredName, email, mobile1 FROM gibbonActivityStaff JOIN gibbonPerson ON (gibbonActivityStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonActivityID=:gibbonActivityID AND role='Organiser'" ;
							$resultCoord=$connection2->prepare($sqlCoord);
							$resultCoord->execute($dataCoord);  
						}
						catch(PDOException $e) { 
							print "<div class='error'>" . $e->getMessage() . "</div>" ; 
						}
						
						if ($resultCoord->rowCount()>0) {
							$rowCoord=$resultCoord->fetch() ;
						}
					}
					?>
					
					<tr class='break'>
						<td colspan=2> 
							<h3>Supervisor</h3>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Supervisor Name *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<input type='text' style='width: 302px' name='supervisorName' id='supervisorName' value='<? if ($rowCoord["surname"]!="") { print formatName("", $rowCoord["preferredName"], $rowCoord["surname"], "Staff", true, true) ; } ?>' maxlength=100>
							<script type="text/javascript">
								var supervisorName = new LiveValidation('supervisorName');
								supervisorName.add(Validate.Presence);
							 </script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Supervisor Email *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<input type='text' style='width: 302px' name='supervisorEmail' id='supervisorEmail' value='<? print $rowCoord["email"] ?>' maxlength=255>
							<script type="text/javascript">
								var supervisorEmail = new LiveValidation('supervisorEmail');
								supervisorEmail.add(Validate.Presence);
							 </script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Supervisor Phone *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<input type='text' style='width: 302px' name='supervisorPhone' id='supervisorPhone' value='<? print $rowCoord["mobile1"] ?>' maxlength=20>
							<script type="text/javascript">
								var supervisorPhone = new LiveValidation('supervisorPhone');
								supervisorPhone.add(Validate.Presence);
							 </script>
						</td>
					</tr>
					
					
					<tr>
						<td>
							<span style="font-size: 90%"><i>* denotes a required field</i></span>
						</td>
						<td class="right">
							<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="Submit">
						</td>
					</tr>
				</table>
			</form>
			<?
		}
	}
}
?>
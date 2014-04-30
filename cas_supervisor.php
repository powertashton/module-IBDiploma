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

$key=NULL ;
if (isset($_GET["key"])) {
	$key=$_GET["key"] ;
}
try {
	$dataKey=array("key"=>$key);  
	$sqlKey="SELECT ibDiplomaCASSupervisorFeedback.*, ibDiplomaCASCommitment.*, surname, preferredName FROM ibDiplomaCASSupervisorFeedback JOIN ibDiplomaCASCommitment ON (ibDiplomaCASSupervisorFeedback.ibDiplomaCASCommitmentID=ibDiplomaCASCommitment.ibDiplomaCASCommitmentID) JOIN gibbonPerson ON (ibDiplomaCASCommitment.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonPerson.status='Full' AND ibDiplomaCASSupervisorFeedback.key=:key" ;
	$resultKey=$connection2->prepare($sqlKey);
	$resultKey->execute($dataKey);
}
catch(PDOException $e) { 
	print "<div class='error'>" . $e->getMessage() . "</div>" ; 
}

if ($resultKey->rowCount()<1) {
	print "<div class='error'>" ;
	print "The supervisor feedback form cannot be displayed." ;
	print "</div>" ;
}
else {
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > IB Diploma > </div><div class='trailEnd'>CAS Supervisor Feedback Form</div>" ;
	print "</div>" ;
	
	if (isset($_GET["updateReturn"])) { $updateReturn=$_GET["updateReturn"] ; } else { $updateReturn="" ; }
	$updateReturnMessage ="" ;
	$class="error" ;
	if (!($updateReturn=="")) {
		if ($updateReturn=="fail1") {
			$updateReturnMessage ="Supervisor feedback form cannot be displayed.." ;	
		}
		else if ($updateReturn=="fail2") {
			$updateReturnMessage ="Update failed due to a database error." ;	
		}
		else if ($updateReturn=="success0") {
			$updateReturnMessage ="Update was successful." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $updateReturnMessage;
		print "</div>" ;
	} 
	
	if ($updateReturn!="success0") {
	
		$rowKey=$resultKey->fetch() ;
		
		//Check for pre-existing complete form for this commitment
		try {
			$dataComplete=array("ibDiplomaCASCommitmentID"=>$rowKey["ibDiplomaCASCommitmentID"]);  
			$sqlComplete="SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='Y'" ;
			$resultComplete=$connection2->prepare($sqlComplete);
			$resultComplete->execute($dataComplete);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		
		if ($resultComplete->rowCount()>0) {
			print "<div class='error'>" ;
			print "Supervisor feedback has already been completed for this commitment." ;
			print "</div>" ;
		}
		else {
			//Print out student and commitment details
			print "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>" ;
				print "<tr>" ;
					print "<td style='width: 34%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>Student</span><br/>" ;
						print formatName("", $rowKey["preferredName"], $rowKey["surname"], "Student", false, true) ;
					print "</td>" ;
					print "<td style='width: 34%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>Commitment</span><br/>" ;
						print $rowKey["name"] ;
					print "</td>" ;
					print "<td style='width: 34%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>Timing</span><br/>" ;
						if (substr($rowKey["dateStart"],0,4)==substr($rowKey["dateEnd"],0,4)) {
							if (substr($rowKey["dateStart"],5,2)==substr($rowKey["dateEnd"],5,2)) {
								print date("F", mktime(0, 0, 0, substr($rowKey["dateStart"],5,2))) . " "  . substr($rowKey["dateStart"],0,4) ;
							}
							else {
								print date("F", mktime(0, 0, 0, substr($rowKey["dateStart"],5,2))) . " - " . date("F", mktime(0, 0, 0, substr($rowKey["dateEnd"],5,2))) . " "  . substr($rowKey["dateStart"],0,4) ;
							}
						}
						else {
							print date("F", mktime(0, 0, 0, substr($rowKey["dateStart"],5,2))) . " "  . substr($rowKey["dateStart"],0,4) . " - " . date("F", mktime(0, 0, 0, substr($rowKey["dateEnd"],5,2))) . " "  . substr($rowKey["dateEnd"],0,4) ;
						}
					print "</td>" ;
				print "</tr>" ;
			print "</table>" ;
				
			//Print form
			print "<form method='post' action='" . $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/cas_supervisorProcess.php'>" ;
				print "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>" ;	
					?>
					<tr>
						<td> 
							<b>Attendance *</b><br/>
						</td>
						<td class="right">
							<select name="attendance" id="attendance" style="width: 302px">
								<option value="Please select...">Please select...</option>
								<option value="<60%"><60%</option>
								<option value="60-84%">60-84%</option>
								<option value="85-99%">85-99%</option>
								<option value="100%">100%</option>
							</select>
						</td>
					</tr>
					<script type="text/javascript">
						var attendance=new LiveValidation('attendance');
						attendance.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
					 </script>	
					<?php
					print "<tr>" ;
						print "<td colspan=2>" ;
							print "<b>Evaluation *</b><br/>Please use the space below to briefly evaluate " . $rowKey["preferredName"] . "'s participation in this commitment. You may wish to consider:" ;
							print "<i><ul style='margin-bottom: 0px'><li>Attitude</li><li>Enthusiasm</li><li>Dedication</li></ul></i><br/>" ;
							print "<textarea name='evaluation' id='evaluation' rows=10 style='width:738px; margin-left: 0px'></textarea>" ;
							?>
							<script type="text/javascript">
								var evaluation=new LiveValidation('evaluation');
								evaluation.add(Validate.Presence);
							 </script>
							 <?php
						print "</td>" ;
					print "</tr>" ;
					?>
					<tr>
						<td>
							<span style="font-size: 90%"><i>* denotes a required field</i></span>
						</td>
						<td class="right">
							<input type="hidden" name="key" value="<?php print $key ?>">
							<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="Submit">
						</td>
					</tr>
					<?php
				print "</table>" ;
			print "</form>" ;
		}
	}
}
?>
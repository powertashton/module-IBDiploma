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


if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_student_myCommitments_edit.php")==FALSE) {

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
		//Proceed!
		print "<div class='trail'>" ;
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/cas_student_myCommitments.php'>My Commitments</a> > </div><div class='trailEnd'>Edit Commitment</div>" ;
		print "</div>" ;
		
		$updateReturn = $_GET["updateReturn"] ;
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
		
		$deleteReturn = $_GET["deleteReturn"] ;
		$deleteReturnMessage ="" ;
		$class="error" ;
		if (!($deleteReturn=="")) {
			if ($deleteReturn=="fail0") {
				$deleteReturnMessage ="Delete failed because you do not have access to this action." ;	
			}
			else if ($deleteReturn=="fail1") {
				$deleteReturnMessage ="Delete failed because a required parameter was not set." ;	
			}
			else if ($deleteReturn=="fail2") {
				$deleteReturnMessage ="Delete failed due to a database error." ;	
			}
			else if ($deleteReturn=="fail3") {
				$deleteReturnMessage ="Delete failed because your inputs were invalid." ;	
			}
			else if ($deleteReturn=="success0") {
				$deleteReturnMessage ="Delete was successful." ;	
				$class="success" ;
			}
			print "<div class='$class'>" ;
				print $deleteReturnMessage;
			print "</div>" ;
		} 
		
		//Check if school year specified
		$ibDiplomaCASCommitmentID=$_GET["ibDiplomaCASCommitmentID"];
		if ($ibDiplomaCASCommitmentID=="Y") {
			print "<div class='error'>" ;
				print "You have not specified an activity." ;
			print "</div>" ;
		}
		else {
			try {
				$data=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"], "ibDiplomaCASCommitmentID"=>$ibDiplomaCASCommitmentID);  
				$sql="SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID" ; 
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}

			if ($result->rowCount()!=1) {
				print "<div class='error'>" ;
					print "The specified commitment could not be loaded." ;
				print "</div>" ;
			}
			else {
				//Let's go!
				$row=$result->fetch() ;
				?>
				<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/cas_student_myCommitments_editProcess.php" ?>">
					<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
						<tr class='break'>
							<td colspan=2> 
								<h3 class='top'>Basic Information</h3>
							</td>
						</tr>
						<tr>
							<td> 
								<b>Name *</b><br/>
								<span style="font-size: 90%"><i>This value cannot be changed</i></span>
							</td>
							<td class="right">
								<input readonly type='text' style='width: 302px' name='name' id='name' value='<? print $row["name"] ?>' maxlength=50>
							</td>
						</tr>
						<tr>
							<td> 
								<b>Status *</b><br/>
								<span style="font-size: 90%"><i></i></span>
							</td>
							<td class="right">
								<select name="status" id="status" style="width: 302px">
									<option <? if ($row["status"]=="Planning") { print "selected " ; } ?>value="Planning">Planning</option>
									<option <? if ($row["status"]=="In Progress") { print "selected " ; } ?>value="In Progress">In Progress</option>
									<option <? if ($row["status"]=="Complete") { print "selected " ; } ?>value="Complete">Complete</option>
								</select>
							</td>
						</tr>
						<tr>
							<td> 
								<b>Start Date *</b><br/>
								<span style="font-size: 90%"><i>dd/mm/yyyy</i></span>
							</td>
							<td class="right">
								<input name="dateStart" id="dateStart" maxlength=10 value='<? print dateConvertBack($row["dateStart"]) ?>' type="text" style="width: 300px">
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
								<input name="dateEnd" id="dateEnd" maxlength=10 value="<? print dateConvertBack($row["dateEnd"]) ?>" type="text" style="width: 300px">
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
								<input type='text' style='width: 302px' name='supervisorName' id='supervisorName' value='<? print $row["supervisorName"] ?>' maxlength=100>
								<script type="text/javascript">
									var name = new LiveValidation('name');
									name.add(Validate.Presence);
								 </script>
							</td>
						</tr>
						<tr>
							<td> 
								<b>Supervisor Email *</b><br/>
								<span style="font-size: 90%"><i></i></span>
							</td>
							<td class="right">
								<input type='text' style='width: 302px' name='supervisorEmail' id='supervisorEmail' value='<? print $row["supervisorEmail"] ?>' maxlength=255>
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
								<input type='text' style='width: 302px' name='supervisorPhone' id='supervisorPhone' value='<? print $row["supervisorPhone"] ?>' maxlength=20>
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
								<input type="hidden" name="ibDiplomaCASCommitmentID" value="<? print $ibDiplomaCASCommitmentID ?>">
								<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
								<input type="reset" value="Reset"> <input type="submit" value="Submit">
							</td>
						</tr>
					</table>
				</form>
				<?
			}
		}
	}
}
?>
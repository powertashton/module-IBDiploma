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

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_student_reflections_add.php")==FALSE) {
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
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/cas_student_reflections.php'>Reflections</a> > </div><div class='trailEnd'>Add Reflection</div>" ;
		print "</div>" ;
		
		if (isset($_GET["addReturn"])) { $addReturn=$_GET["addReturn"] ; } else { $addReturn="" ; }
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
				$addReturnMessage ="Add failed because the selected person is already registered." ;	
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
	
		$step=NULL ;
		if (isset($_GET["step"])) {
			$step=$_GET["step"] ;
		}
		if ($step!=1 AND $step!=2) {
			$step=1 ;
		}
		
		//Step 1
		if ($step==1) {
			?>
			<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/cas_student_reflections_add.php&step=2" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr class='break'>
						<td colspan=2> 
							<h3 class='top'>Reflection Source</h3>
						</td>
					</tr>
					
					<script type="text/javascript">
						$(document).ready(function(){
							$("#activityRow").css("display","none");
							
							$(".type1").click(function(){
								if ($('input[name=type1]:checked').val() == "General CAS Reflection" ) {
									$("#activityRow").css("display","none");
								} else {
									$("#activityRow").slideDown("fast", $("#activityRow").css("display","table-row")); //Slide Down Effect
								}
							 });
						});
					</script>
					<tr>
						<td> 
							<b>Reflection Type *</b><br/>
						</td>
						<td class="right">
							<input type="radio" name="type1" value="General CAS Reflection" class="type1" /> General CAS Reflection <b><u>or</u></b>
							<input type="radio" name="type1" value="Commitment Reflection" class="type1" style="margin-left: 3px" /> Commitment Reflection
						</td>
					</tr>
					<tr id="activityRow">
						<td> 
							<b>Choose Activity</b><br/>
						</td>
						<td class="right">
							<select style="width: 302px" name="ibDiplomaCASCommitmentID" id="ibDiplomaCASCommitmentID">
								<?php
								try {
									$dataSelect=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]);  
									$sqlSelect="SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID ORDER BY name" ;
									$resultSelect=$connection2->prepare($sqlSelect);
									$resultSelect->execute($dataSelect);
								}
								catch(PDOException $e) { 
									print "<div class='error'>" . $e->getMessage() . "</div>" ; 
								}
								
								print "<option value=''>Please select...</option>" ;
								while ($rowSelect=$resultSelect->fetch()) {
									print "<option $selected value='" . $rowSelect["ibDiplomaCASCommitmentID"] . "'>" . htmlPrep($rowSelect["name"]) . "</option>" ;
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
							<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="Go">
						</td>
					</tr>
				</table>
			<?php
		}
		else {
			$type=$_POST["type1"] ;
			if ($type!="General CAS Reflection" AND $type!="Commitment Reflection") {
				$type="General CAS Reflection" ;
			}
			if ($type=="Commitment Reflection") {
				$ibDiplomaCASCommitmentID=$_POST["ibDiplomaCASCommitmentID"] ;
				if ($ibDiplomaCASCommitmentID=="") {
					print "<div class='warning'>" ;
						print "You have not specified a commitment." ;
					print "</div>" ;
				}
				else {
					try {
						$dataActivity=array("ibDiplomaCASCommitmentID"=>$ibDiplomaCASCommitmentID);  
						$sqlActivity="SELECT * FROM ibDiplomaCASCommitment WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID" ;
						$resultActivity=$connection2->prepare($sqlActivity);
						$resultActivity->execute($dataActivity);
					}
					catch(PDOException $e) { 
						print "<div class='error'>" . $e->getMessage() . "</div>" ; 
					}
					
					if ($resultActivity->rowCount()!=1) {
						print "<div class='warning'>" ;
							print "The specified commitment does not exist." ;
						print "</div>" ;
					}
					else {
						$rowActivity=$resultActivity->fetch() ;
					}
				}
			}
		
			?>
			<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/cas_student_reflections_addProcess.php" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr>
						<td> 
							<b>Type *</b><br/>
							<span style="font-size: 90%"><i>This value cannot be changed</i></span>
						</td>
						<td class="right">
							<input readonly type='text' style='width: 302px' name='type' id='type' value='<?php print $type ?>' maxlength=50>
						</td>
					</tr>
					<?php
					if ($type=="Commitment Reflection") {
						?>
						<tr>
							<td> 
								<b>Commitment *</b><br/>
								<span style="font-size: 90%"><i>This value cannot be changed</i></span>
							</td>
							<td class="right">
								<input readonly type='text' style='width: 302px' name='name' id='name' value='<?php print $rowActivity["name"] ?>' maxlength=50>
								<input type='hidden' style='width: 302px' name='ibDiplomaCASCommitmentID' id='ibDiplomaCASCommitmentID' value='<?php print $rowActivity["ibDiplomaCASCommitmentID"] ?>' maxlength=50>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td> 
							<b>Title *</b><br/>
						</td>
						<td class="right">
							<input type='text' style='width: 302px' name='title' id='title' value='' maxlength=100>
							<script type="text/javascript">
								var title=new LiveValidation('title');
								title.add(Validate.Presence);
							 </script>
						</td>
					</tr>
					<tr>
						<td colspan=2> 
							<b>Reflection *</b><br/>
							<?php
							if ($type=="Commitment Reflection") { print "When describing your experience in this commitment you may wish to include:" ; }
							else { print "When describing your experience of CAS in general you may wish to include:" ; } 
							?>
							<i><ul><li>What was the nature of your experience?</li><li>What have you learned or accomplished?</li><li>What aspects were new or challenging?</li><li>How could it have been more challenging?</li><li>Did it match your expectations, if not, how?</li><li>How might you do things differently in the future?</li></ul></i><br/>
							
							<?php print getEditor($guid,  $connection2, "reflection", "", 20,false, true ) ?>
						</td>
					</tr>
					
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
?>
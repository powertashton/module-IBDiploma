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

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_supervisor_invite.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	$role=staffCASRole($guid, $_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role==FALSE) {
		print "<div class='error'>" ;
			print "You are not enroled in the IB Diploma programme." ;
		print "</div>" ;
	}
	else {
		print "<div class='trail'>" ;
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Invite CAS Supervisor Feedback</div>" ;
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
	
		$step=$_GET["step"] ;
		if ($step!=1 AND $step!=2 AND $step!=3) {
			$step=1 ;
		}
		
		//Step 1
		if ($step==1) {
			print "<h3>" ;
			print "Step 1" ;
			print "</h3>" ;
		
			
			?>
			<form method="get" action="<? print $_SESSION[$guid]["absoluteURL"] . "/index.php" ?>">
				<table style="width: 100%">	
					<tr><td style="width: 30%"></td><td></td></tr>
					<tr>
						<td>
							<b>Invitation Type *</b><br/>
						</td> 
						<td class='right'> 
							<input checked type="radio" name="type" value="Single" class="type" /> Single Commitment
							<input type="radio" name="type" value="Multiple" class="type" /> Multiple Commitments
						</td>
					</tr>
					<tr>
						<td class="right" colspan=2>
							<input type="hidden" name="q" value="<? print "/modules/" . $_SESSION[$guid]["module"] . "/cas_supervisor_invite.php" ?>">
							<input type="hidden" name="step" value="2">
							<input type="submit" value="Proceed">
						</td>
					</tr>
				</table>
			<?
		}
		else if ($step==2) {
			$type=$_GET["type"] ;
			if ($type!="Single" AND $type!="Multiple") {
				$type="Single" ;
			}
		
			print "<h3>" ;
			print "Step 2 - $type" ;
			print "</h3>" ;
			
			print "<div class='linkTop'>" ;
				if ($_SESSION[$guid]["returnTo"]!="") {
					print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/cas_supervisor_invite.php&step=1'>Back</a>" ;
				}
			print "</div>" ;
			
			print "<form method='post' action='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/cas_supervisor_invite.php&step=3'>" ;
				print "<table style='width: 100%'>"	;
					print "<tr><td style='width: 30%'></td><td></td></tr>" ;
					if ($type=="Single") {
						print "<tr>" ;
							print "<td>" ;
								print "<b>Student *</b><br/>" ;
							print "</td> " ;
							print "<td class='right'> " ;
								print "<select name='gibbonPersonID' id='gibbonPersonID' style='width: 302px'>" ;
									try {
										if ($role=="Coordinator") {
											$dataSelect=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"]);  
											$sqlSelect="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd ORDER BY rollGroup, surname, preferredName" ; 
										}
										else {
											$dataSelect=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "advisor"=>$_SESSION[$guid]["gibbonPersonID"]);  
											$sqlSelect="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor ORDER BY rollGroup, surname, preferredName" ; 
										}
										$resultSelect=$connection2->prepare($sqlSelect);
										$resultSelect->execute($dataSelect);
									}
									catch(PDOException $e) { }
									print "<option value='Please select...'>Please select...</option>" ;
									while ($rowSelect=$resultSelect->fetch()) {
										print "<option value='" . $rowSelect["gibbonPersonID"] . "'>" . formatName("", $rowSelect["preferredName"], $rowSelect["surname"], "Student", true, true) . " (" . htmlPrep($rowSelect["rollGroup"]) . ")</option>" ;
									}		
									?>				
								</select>
								<script type="text/javascript">
									var gibbonPersonID = new LiveValidation('gibbonPersonID');
									gibbonPersonID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
								 </script>
								 <?
							print "</td>" ;
						print "</tr>" ;
						print "<tr>" ;
							print "<td>" ;
								print "<b>Commitment *</b><br/>" ;
							print "</td> " ;
							print "<td class='right'> " ;
								?>
								<select name="ibDiplomaCASCommitmentID" id="ibDiplomaCASCommitmentID" style="width: 302px">
									<?
									try {
										$dataSelect2=array();  
										$sqlSelect2="SELECT * FROM ibDiplomaCASCommitment WHERE approval='Approved' ORDER BY name" ; 
										$resultSelect2=$connection2->prepare($sqlSelect2);
										$resultSelect2->execute($dataSelect2); 
									}
									catch(PDOException $e) { }

									while ($rowSelect2=$resultSelect2->fetch()) {
										print "<option class='" . $rowSelect2["gibbonPersonID"] . "' value='" . $rowSelect2["ibDiplomaCASCommitmentID"] . "'>" . htmlPrep($rowSelect2["name"]) . " (" . htmlPrep($rowSelect2["supervisorName"]) . ")</option>" ;
									}		
									?>				
								</select>
								<script type="text/javascript">
									$("#ibDiplomaCASCommitmentID").chainedTo("#gibbonPersonID");
								</script>
								<?
							print "</td>" ;
						print "</tr>" ;
					}
					else {
						print "<tr>" ;
							print "<td style='text-align: justify' colspan=2>" ;
								print "By clicking proceed you will generate invitations for every student you take care of for CAS who is in the final year of their IB Diploma. As a coordinator this will be all students in the cohort: as an advisor, just the students you advise.<br/><br/>" ;
								print "<b>Invitations will be generated for every approved, completed commitment which does not yet have supervisor feedback.<b/>" ;
							print "</td> " ;
						print "</tr>" ;
					}
					print "<tr>" ;
						print "<td class='right' colspan=2>" ;
							print "<input type='hidden' name='type' value='$type'>" ;
							print "<input type='reset' value='Reset'> <input type='submit' value='Proceed'>" ;
						print "</td>" ;
					print "</tr>" ;
					print "<tr>" ;
						print "<td class='right' colspan=2>" ;
							print "<span style='font-size: 90%'><i>* denotes a required field</i></span>" ;
						print "</td>" ;
					print "</tr>" ;
				print "</table>" ;	
			print "</form>" ;
		}
		else if ($step==3) {
			$type=$_POST["type"] ;
			if ($type!="Single" AND $type!="Multiple") {
				$type="Single" ;
			}
		
			print "<h3>" ;
			print "Step 3 - $type" ;
			print "</h3>" ;
			
			print "<div class='linkTop'>" ;
				if ($_SESSION[$guid]["returnTo"]!="") {
					print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/cas_supervisor_invite.php&step=2&type=$type'>Back</a>" ;
				}
			print "</div>" ;
			
			if ($type=="Single") {
				//Get and check variables
				$gibbonPersonID=$_POST["gibbonPersonID"] ;
				$ibDiplomaCASCommitmentID=$_POST["ibDiplomaCASCommitmentID"] ;
				if ($gibbonPersonID=="" OR $ibDiplomaCASCommitmentID=="") {
					print "<div class='error'>" ;
						print "You have not specified a student or commitment." ;
					print "</div>" ;
				}
				else {
					try {
						if ($role=="Coordinator") {
							$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "gibbonPersonID"=>$gibbonPersonID);  
							$sql="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName" ; 
						}
						else {
							$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "advisor"=>$_SESSION[$guid]["gibbonPersonID"], "gibbonPersonID"=>$gibbonPersonID);  
							$sql="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName" ; 
						}
						$result=$connection2->prepare($sql);
						$result->execute($data); 
					}
					catch(PDOException $e) { 
						print "<div class='error'>" . $e->getMessage() . "</div>" ; 
					}
				
					if ($result->rowCount()!=1) {
						print "<div class='error'>" ;
							print "Invite cannot be issued." ;
						print "</div>" ;
					}
					else {
						$row=$result->fetch() ;
						$student=$row["preferredName"] . " " . $row["surname"] ;
						$studentFirst=$row["preferredName"] ;
						
						//Check existence of and access to this commitment.
						try {
							$data=array("gibbonPersonID"=>$gibbonPersonID, "ibDiplomaCASCommitmentID"=>$ibDiplomaCASCommitmentID);  
							$sql="SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved' AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID" ; 
							$result=$connection2->prepare($sql);
							$result->execute($data); 
						}
						catch(PDOException $e) { 
							print "<div class='error'>" . $e->getMessage() . "</div>" ; 
						}
						
						if ($result->rowCount()!=1) {
							print "<div class='error'>" ;
								print "Invite cannot be issued." ;
							print "</div>" ;
						}
						else {
							$row=$result->fetch() ;
							
							//Check for completion
							try {
								$dataComplete=array("username"=>$username);  
								$sqlComplete="SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE complete='Y' AND ibDiplomaCASCommitmentID='$ibDiplomaCASCommitmentID'" ; 
								$resultComplete=$connection2->prepare($sqlComplete);
								$resultComplete->execute($dataComplete);
							}
							catch(PDOException $e) { 
								print "<div class='error'>" . $e->getMessage() . "</div>" ; 
							}
							
							if ($resultComplete->rowCount()>0) {
								print "<div class='success'>" ;
									print "This commitment has already had feedback completed, so no invite is required." ;
								print "</div>" ;
							}
							else {
								//Lock table
								$lock=true ;
								try {
									$sqlLock="LOCK TABLE ibDiplomaCASSupervisorFeedback WRITE" ;
									$resultLock=$connection2->query($sqlLock);   
								}
								catch(PDOException $e) { 
									$lock=false ;
									print "<div class='error'>" . $e->getMessage() . "</div>" ; 
								}			
								
								if ($lock) {
									//Let's go! Create key, send the invite
									$continue=FALSE ;
									$count=0 ;
									while ($continue==FALSE AND count<100) {
										$key=randomPassword(40) ;
										try {
											$dataUnique=array("key"=>$key);  
											$sqlUnique="SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASSupervisorFeedback.key=:key" ; 
											$resultUnique=$connection2->prepare($sqlUnique);
											$resultUnique->execute($dataUnique); 
										}
										catch(PDOException $e) { }
										
										if ($resultUnique->rowCount()==0) {
											$continue=TRUE ;
										}
										$count++ ;
									}
									
									if ($continue==FALSE) {
										print "<div class='error'>" ;
											print "A unique key cannot be generated, so it is not possible to continue." ;
										print "</div>" ;
									}
									else {
										//Write to database
										$proceed=true ;
										try {
											$data=array("ibDiplomaCASCommitmentID"=>$ibDiplomaCASCommitmentID, "key"=>$key);  
											$sql="INSERT INTO ibDiplomaCASSupervisorFeedback SET ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID, ibDiplomaCASSupervisorFeedback.key=:key" ;
											$result=$connection2->prepare($sql);
											$result->execute($data);
										}
										catch(PDOException $e) {
											$proceed=false ; 
											print "<div class='error'>" . $e->getMessage() . "</div>" ; 
										}
										
										if ($proceed) {
											//Unlock table
											try {
												$sql="UNLOCK TABLES" ;
												$result=$connection2->query($sql);     
											}
											catch(PDOException $e) { }			
										
											$to = $row["supervisorEmail"];
											$subject = $_SESSION[$guid]["organisationNameShort"] . " CAS Supervisor Feedback Request";
											$body = "Dear " . $row["supervisorName"] . ",<br/><br/>" ;
											$body = $body . "We greatly appreciate your support as a CAS activity supervisor to $student (" . $_SESSION[$guid]["organisationName"] . "). In order for this activity (" . $row["name"] . ") to count towards " . $studentFirst . "'s IB Diploma, we require a small amount of feedback from you.<br/><br/>" ;
											$body = $body . "If you are willing and able to provide us with this feedback, you may do so by <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/IB Diploma/cas_supervisor.php&key=$key'>clicking here</a>.<br/><br/>" ;
											$body = $body . "Your assistance is most appreciated. Regards,<br/><br/>" ;
											$body = $body . $_SESSION[$guid]["preferredName"] . " " . $_SESSION[$guid]["surname"] ;
											$headers = "From: " . $_SESSION[$guid]["email"] . "\r\n";
											$headers .= "MIME-Version: 1.0\r\n";
											$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n" ;
								
											if (mail($to, $subject, $body, $headers)) {
												print "<div class='success'>" ;
													print "The invite has been created and emailed to $to." ;
												print "</div>" ;
											}
											else {
												print "<div class='warning'>" ;
													print "The invite has been created, but could not be email. You may email the following link supervisor (" . $row["supervisorName"] . ") at " . $row["supervisorEmail"] . ": " . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/IB Diploma/cas_supervisor.php&key=$key" ;
												print "</div>" ;
											}													
										}
									}
								}
							}
						}
					}
				}
			}
			else {
				//Get list of students
				try {
					if ($role=="Coordinator") {
						$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"]);  
						$sql="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd ORDER BY rollGroup, surname, preferredName" ; 
					}
					else {
						$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "advisor"=>$_SESSION[$guid]["gibbonPersonID"]);  
						$sql="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor ORDER BY rollGroup, surname, preferredName" ; 
					}
					$result=$connection2->prepare($sql);
					$result->execute($data);
				}
				catch(PDOException $e) {
					print "<div class='error'>" . $e->getMessage() . "</div>" ; 
				}
				
				if ($result->rowCount()<1) {
					print "<div class='error'>" ;
						print "Invites cannot be issued." ;
					print "</div>" ;
				}
				else {
					while ($row=$result->fetch()) {
						$student=$row["preferredName"] . " " . $row["surname"] ;
						$studentFirst=$row["preferredName"] ;
						
						print "<h4>" ;
							print $student . " (" . $row["rollGroup"] . ")" ;
						print "</h4>" ;
						
						//Scan through commitments for each student look for ones that are approved, complete and have not feedback.
						try {
							$dataCommitment=array("gibbonPersonID"=>$row["gibbonPersonID"]);  
							$sqlCommitment="SELECT * FROM ibDiplomaCASCommitment WHERE status='Complete' AND approval='Approved' AND gibbonPersonID=:gibbonPersonID" ; 
							$resultCommitment=$connection2->prepare($sqlCommitment);
							$resultCommitment->execute($dataCommitment);
						}
						catch(PDOException $e) { 
							print "<div class='error'>" . $e->getMessage() . "</div>" ; 
						}
						
						if ($resultCommitment->rowCount()>0) {
							while ($rowCommitment=$resultCommitment->fetch()) {
								//Check for completion
								try {
									$dataComplete=array("ibDiplomaCASCommitmentID"=>$rowCommitment["ibDiplomaCASCommitmentID"]);  
									$sqlComplete="SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE complete='Y' AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID" ; 
									$resultComplete=$connection2->prepare($sqlComplete);
									$resultComplete->execute($dataComplete);
								}
								catch(PDOException $e) { 
									print "<div class='error'>" . $e->getMessage() . "</div>" ; 
								}
								
								if ($resultComplete->rowCount()<=0) {
									//Lock table
									$lock=true ;
									try {
										$sqlLock="LOCK TABLE ibDiplomaCASSupervisorFeedback WRITE" ;
										$resultLock=$connection2->query($sqlLock);   
									}
									catch(PDOException $e) { 
										$lock=false ;
										print "<div class='error'>" ;
											print $rowCommitment["name"] . ": Invite cannot be issued due to a database error." ;
										print "</div>" ; 
									}			
									
									
									if ($lock) {
										//Generate form and key for each commitment, and send email
										$continue=FALSE ;
										$count=0 ;
										while ($continue==FALSE AND count<100) {
											$key=randomPassword(40) ;
											try {
												$dataUnique=array("key"=>$key);  
												$sqlUnique="SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASSupervisorFeedback.key=:key" ; 
												$resultUnique=$connection2->prepare($sqlUnique);
												$resultUnique->execute($dataUnique);
											}
											catch(PDOException $e) { }
											
											if ($resultUnique->rowCount()==0) {
												$continue=TRUE ;
											}
											$count++ ;
										}
										
										//Unlock table
										try {
											$sqlUnlock="UNLOCK TABLES" ;
											$resultUnlock=$connection2->query($sqlUnlock);  
										}
										catch(PDOException $e) { }			
										
										if ($continue==FALSE) {
											print "<div class='error'>" ;
												print $rowCommitment["name"] . ": A unique key could not be generated, so the invite could not be sent." ;
											print "</div>" ;
										}
										else {
											//Write to database
											$proceed=true ;
											try {
												$dataWrite=array("ibDiplomaCASCommitmentID"=>$rowCommitment["ibDiplomaCASCommitmentID"], "key"=>$key);  
												$sqlWrite="INSERT INTO ibDiplomaCASSupervisorFeedback SET ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID, ibDiplomaCASSupervisorFeedback.key=:key" ;
												$resultWrite=$connection2->prepare($sqlWrite);
												$resultWrite->execute($dataWrite);
											}
											catch(PDOException $e) { 
												$proceed=false ;
												print "<div class='error'>" ;
													print $rowCommitment["name"] . ": Invite cannot be issued due to a database error." ;
												print "</div>" ;
											}
											
											if ($proceed) {
												$to = $rowCommitment["supervisorEmail"];
												$subject = $_SESSION[$guid]["organisationNameShort"] . " CAS Supervisor Feedback Request";
												$body = "Dear " . $rowCommitment["supervisorName"] . ",<br/><br/>" ;
												$body = $body . "We great appreciate your support as a CAS activity supervisor to $student (" . $_SESSION[$guid]["organisationName"] . "). In order for this activity (" . $rowCommitment["name"] . ") to count towards " . $studentFirst . "'s IB Diploma, we require a small amount of feedback from you.<br/><br/>" ;
												$body = $body . "If you are willing and able to provide us with this feedback, you may do so by <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/IB Diploma/cas_supervisor.php&key=$key'>clicking here</a>.<br/><br/>" ;
												$body = $body . "Your assistance is most appreciated. Regards,<br/><br/>" ;
												$body = $body . $_SESSION[$guid]["preferredName"] . " " . $_SESSION[$guid]["surname"] ;
												$headers = "From: " . $_SESSION[$guid]["email"] . "\r\n";
												$headers .= "MIME-Version: 1.0\r\n";
												$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n" ;
									
												if (mail($to, $subject, $body, $headers)) {
													print "<div class='success'>" ;
														print $rowCommitment["name"] . ": An invite has been created and emailed to $to." ;
													print "</div>" ;
												}
												else {
													print "<div class='warning'>" ;
														print $rowCommitment["name"] . ": An invite has been created, but could not be email. You may email the following link supervisor (" . $row["supervisorName"] . ") at " . $row["supervisorEmail"] . ": " . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/IB Diploma/cas_supervisor.php&key=$key" ;
													print "</div>" ;
												}				
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
?>
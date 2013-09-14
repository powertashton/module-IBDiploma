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

include "../../functions.php" ;
include "../../config.php" ;

//Module includes
include "./moduleFunctions.php" ;

//New PDO DB connection
try {
    $connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
    echo $e->getMessage();
}


session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$gibbonPersonID=$_POST["gibbonPersonID"] ;
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/cas_adviseStudents_details.php&gibbonPersonID=$gibbonPersonID&subpage=Interview 1" ;

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_adviseStudents_details.php")==FALSE) {
	//Fail 0
	$URL = $URL . "&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	$role=staffCASRole($guid, $_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role==FALSE) {
		//Fail 0
		$URL = $URL . "&updateReturn=fail0" ;
		header("Location: {$URL}");
	}
	else {
		//Check if school year specified
		if ($gibbonPersonID=="") {
			//Fail1
			$URL = $URL . "&updateReturn=fail1" ;
			header("Location: {$URL}");
		}
		else {
			try {
				if ($role=="Coordinator") {
					$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "gibbonPersonID"=>$gibbonPersonID);  
					$sql="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName" ; 
				}
				else {
					$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "advisor"=> $_SESSION[$guid]["gibbonPersonID"], "gibbonPersonID"=>$gibbonPersonID);  
					$sql="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName" ; 
				}
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				//Fail 2
				$URL = $URL . "&updateReturn=fail2" ;
				header("Location: {$URL}");
				break ;
			}
							
			if ($result->rowCount()==1) {	
				$count=$_POST["count"] ;
				$partialFail=FALSE ;
				
				
				//Update commitments
				for ($i=1;$i<=$count;$i++) {
					$ibDiplomaCASCommitmentID=$_POST["$i-ibDiplomaCASCommitmentID"] ;
					$goals=$_POST["$i-goals"] ;
					
					//UPDATE
					try {
						$data=array("goals"=>$goals, "ibDiplomaCASCommitmentID"=>$ibDiplomaCASCommitmentID);  
						$sql="UPDATE ibDiplomaCASCommitment SET goals=:goals WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID" ;
						$result=$connection2->prepare($sql);
						$result->execute($data);  
					}
					catch(PDOException $e) { 
						$partialFail=TRUE ;
					}
				}
				
				//Update status
				$casStatusSchool=$_POST["casStatusSchool"] ;
				if ($casStatusSchool=="") {
					$partialFail=TRUE ;
				}
				else {
					try {
						$data=array("casStatusSchool"=>$casStatusSchool, "gibbonPersonID"=>$gibbonPersonID);  
						$sql="UPDATE ibDiplomaStudent SET casStatusSchool=:casStatusSchool WHERE gibbonPersonID=:gibbonPersonID" ;
						$result=$connection2->prepare($sql);
						$result->execute($data);  
					}
					catch(PDOException $e) { 
						$partialFail=TRUE ;
					}
				}
				
				//Get interview variables
				$date=dateConvert($_POST["date"]) ;
				$notes=$_POST["notes"] ;
				if ($date=="") {
					//Fail1
					$URL = $URL . "&updateReturn=fail1" ;
					header("Location: {$URL}");
				}
				else {
					
					//See if interview exists
					try {
						$dataInterview=array("gibbonPersonID"=>$gibbonPersonID);  
						$sqlInterview="SELECT ibDiplomaCASInterview.*, surname, preferredName FROM ibDiplomaCASInterview JOIN gibbonPerson ON (ibDiplomaCASInterview.1_gibbonPersonIDInterviewer=gibbonPerson.gibbonPersonID) WHERE gibbonPersonIDInterviewee=:gibbonPersonID" ;
						$resultInterview=$connection2->prepare($sqlInterview);
						$resultInterview->execute($dataInterview);
					}
					catch(PDOException $e) { 
						//Fail 2
						$URL = $URL . "&updateReturn=fail2" ;
						header("Location: {$URL}");
						break ;
					}
					
					if ($resultInterview->rowCount()>1) {
						//Fail 2
						$URL = $URL . "&updateReturn=fail2" ;
						header("Location: {$URL}");
					}
					else {
						try {
							//If exists, update
							if ($resultInterview->rowCount()==1) {
								$data=array("notes"=>$notes, "gibbonPersonID"=>$gibbonPersonID);  
								$sql="UPDATE ibDiplomaCASInterview SET 1_notes=:notes WHERE gibbonPersonIDInterviewee=:gibbonPersonID" ;
							}
							//If not, insert
							else if ($resultInterview->rowCount()==0) {
								$data=array("notes"=>$notes, "gibbonPersonID"=>$gibbonPersonID, "date"=>$date, "gibbonPersonIDInterviewer"=>$_SESSION[$guid]["gibbonPersonID"]);  
								$sql="INSERT INTO ibDiplomaCASInterview SET gibbonPersonIDInterviewee=:gibbonPersonID, 1_date=:date, 1_notes=:notes, 1_gibbonPersonIDInterviewer=:gibbonPersonIDInterviewer" ;
							}
							$result=$connection2->prepare($sql);
							$result->execute($data);
						}
						catch(PDOException $e) { 
							//Fail 2
							$URL = $URL . "&updateReturn=fail2" ;
							header("Location: {$URL}");
							break ;
						}

						//Return!
						if ($partialFail==TRUE) {
							//Fail 4
							$URL = $URL . "&updateReturn=fail4" ;
							header("Location: {$URL}");
						}
						else {
							//Success 0
							$URL = $URL . "&updateReturn=success0" ;
							header("Location: {$URL}");
						}	
					}
				}
			}
		}
	}
}
?>
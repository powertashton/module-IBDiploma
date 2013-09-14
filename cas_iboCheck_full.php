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

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_iboCheck_full.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this page." ;
	print "</div>" ;
}
else {
	$gibbonPersonID=$_GET["gibbonPersonID"] ;
	if ($gibbonPersonID=="") {
		print "<div class='error'>" ;
			print "You have not specified a student." ;
		print "</div>" ;
	}
	else {
		try {
			$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "gibbonPersonID"=>$gibbonPersonID);  
			$sql="SELECT gibbonPerson.gibbonPersonID, gibbonStudentEnrolment.gibbonYearGroupID, gibbonStudentEnrolment.gibbonRollGroupID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber=:sequenceEnd AND gibbonPerson.gibbonPersonID=:gibbonPersonID" ; 
			$result=$connection2->prepare($sql);
			$result->execute($data); 
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		
		if ($result->rowCount()!=1) {
			print "<div class='error'>" ;
				print "The specified student does not exist, or you do not have access to them." ;
			print "</div>" ;
		}
		else {
			//Get class variable
			$ibDiplomaCASCommitmentID=$_GET["ibDiplomaCASCommitmentID"] ;
			if ($ibDiplomaCASCommitmentID=="") {
				print "<div class='warning'>" ;
					print "Commitment has not been specified ." ;
				print "</div>" ;
			}
			//Check existence of and access to this class.
			else {
				try {
					$data=array("gibbonPersonID"=>$gibbonPersonID, "ibDiplomaCASCommitmentID"=>$ibDiplomaCASCommitmentID);  
					$sql="SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID" ; 
					$result=$connection2->prepare($sql);
					$result->execute($data); 
				}
				catch(PDOException $e) { 
					print "<div class='error'>" . $e->getMessage() . "</div>" ; 
				}
		
				if ($result->rowCount()!=1) {
					print "<div class='warning'>" ;
						print "Commitment does not exist or you do not have access to it." ;
					print "</div>" ;
				}
				else {
					$row=$result->fetch() ;
					if (($row["role"]=="Student" AND $row["viewableStudents"]=="N") AND ($highestAction=="Planner_viewMyChildrensClasses" AND $row["viewableParents"]=="N")) {
						print "<div class='warning'>" ;
							print "Lesson does not exist or you do not have access to it." ;
						print "</div>" ;
					}
					else {
						print "<h1>" ;
							print $row["name"] . "<br>" ;
						print "</h1>" ;
						
						print "<div style='width:510px; float: left; font-size: 115%; margin-top: -5px'>" ;
							try {
								$dataReflections=array("gibbonPersonID"=>$gibbonPersonID, "ibDiplomaCASCommitmentID"=>$ibDiplomaCASCommitmentID);  
								$sqlReflections="SELECT * FROM ibDiplomaCASReflection WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID ORDER BY timestamp" ; 
								$resultReflections=$connection2->prepare($sqlReflections);
								$resultReflections->execute($dataReflections);
							}
							catch(PDOException $e) { 
								print "<div class='error'>" . $e->getMessage() . "</div>" ; 
							}
							
							if ($resultReflections->rowCount()<1) {
								print "<div class='warning'>" ;
								print "There are no reflections to display in this commitment" ;
								print "</div>" ;
							}
							else {
								while ($rowReflections=$resultReflections->fetch()) {
									print "<h3>" ;
										print $rowReflections["title"] . "<br/>" ;
										print "<span style='font-size: 55%; font-weight: normal; font-style: italic; margin-top: 5px'>" . dateConvertBack(substr($rowReflections["timestamp"],0,10)) . " at " . substr($rowReflections["timestamp"],11,5) . "</span>" ;
									print "</h3>" ;
									print "<p>" ;
										print $rowReflections["reflection"] ;
									print "</p>" ;
								}
							}
						print "</div>" ;
							
						//Details
						print "<div style='width:430px; float: right; font-size: 115%; padding-top: 14px'>" ;
							print "<table style='width: 420px; float: left;'>" ;
								print "<tr>" ;
									print "<td colspan=3'>" ;
										print "<h2 style='margin-top: 0px'>" ;
											print "General Information" ;
										print "</h2>" ;
									print "</td>" ;
								print "</tr>" ;
								print "<tr>" ;
									print "<td style='width: 33%; vertical-align: top'>" ;
										print "<span style='font-size: 115%; font-weight: bold'>Status</span><br/>" ;
										if ($row["approval"]=="Pending" OR $row["approval"]=="Not Approved") {
											print $row["approval"] ;
										}
										else {
											print $row["status"] ;
										}
									print "</td>" ;
									print "<td style='width: 33%; vertical-align: top'>" ;
										print "<span style='font-size: 115%; font-weight: bold'>Start Date</span><br/>" ;
										print dateConvertBack($row["dateStart"]) ;
									print "</td>" ;
									print "<td style='width: 33%; vertical-align: top'>" ;
										print "<span style='font-size: 115%; font-weight: bold'>End Date</span><br/>" ;
										print dateConvertBack($row["dateEnd"]) ;
									print "</td>" ;
								print "</tr>" ;
								if ($row["description"]!="") {
									print "<tr>" ;
										print "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>" ;
											print "<span style='font-size: 115%; font-weight: bold'>Description</span><br/>" ;
											print $row["description"] ;
										print "</td>" ;
									print "</tr>" ;
								}
								if ($row["goals"]!="") {
									print "<tr>" ;
										print "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>" ;
											print "<span style='font-size: 115%; font-weight: bold'>Goals</span><br/>" ;
											print $row["goals"] ;
										print "</td>" ;
									print "</tr>" ;
								}
								print "<tr>" ;
									print "<td colspan=3>" ;
										print "<h2>" ;
											print "Supervisor" ;
										print "</h2>" ;
									print "</td>" ;
								print "</tr>" ;
								print "<tr>" ;
									print "<td style='width: 33%; vertical-align: top'>" ;
										print "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>" ;
										print $row["supervisorName"] ;
									print "</td>" ;
									print "<td style='width: 33%; vertical-align: top'>" ;
										print "<span style='font-size: 115%; font-weight: bold'>Phone</span><br/>" ;
										print $row["supervisorPhone"] ;
									print "</td>" ;
									print "<td style='15px; width: 33%; vertical-align: top'>" ;
										print "<span style='font-size: 115%; font-weight: bold'>Email</span><br/>" ;
										print $row["supervisorEmail"] ;
									print "</td>" ;
								print "</tr>" ;
								
								//Print feedback if there is any
								try {
									$dataFeedback=array("ibDiplomaCASCommitmentID"=>$ibDiplomaCASCommitmentID);  
									$sqlFeedback="SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='Y'" ; 
									$resultFeedback=$connection2->prepare($sqlFeedback);
									$resultFeedback->execute($dataFeedback);
								}
								catch(PDOException $e) { 
									print "<div class='error'>" . $e->getMessage() . "</div>" ; 
								}
								
								if ($resultFeedback->rowCount()==1) {
									$rowFeedback=$resultFeedback->fetch() ;
									print "<tr>" ;
										print "<td colspan=3>" ;
											print "<h2>" ;
												print "Feedback" ;
											print "</h2>" ;
										print "</td>" ;
									print "</tr>" ;
									print "<tr>" ;
										print "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>" ;
											print "<span style='font-size: 115%; font-weight: bold'>Evaluation</span><br/>" ;
											print $rowFeedback["evaluation"] ;
										print "</td>" ;
									print "</tr>" ;
									print "<tr>" ;
										print "<td style='padding-top: 15px; width: 33%; vertical-align: top; text-align: justify' colspan=3>" ;
											print "<span style='font-size: 115%; font-weight: bold'>Attendance</span><br/>" ;
											print $rowFeedback["attendance"] ;
										print "</td>" ;
									print "</tr>" ;
								}
							print "</table>" ;
						print "</div>" ;
					}
				}
			}
		}
	}	
}
?>
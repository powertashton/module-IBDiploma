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

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_iboCheck.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	print "<div class='trail'>" ;
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>IBO CAS Check</div>" ;
	print "</div>" ;
	print "<p>" ;
		print "Your CAS staff role is $role. The students listed below are determined by your role, and student-staff relationship assignment." ;	
	print "</p>" ;
	
	try {
		$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"]);  
		$sql="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber=:sequenceEnd ORDER BY start.sequenceNumber DESC, surname, preferredName" ; 
		$result=$connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) { 
		print "<div class='error'>" . $e->getMessage() . "</div>" ; 
	}
	
	if ($result->rowCount()<1) {
		print "<div class='error'>" ;
		print "There are no students to display." ;
		print "</div>" ;
	}
	else {
		print "<table cellspacing='0' style='width: 100%'>" ;
			print "<tr class='head'>" ;
				print "<th>" ;
					print "Name" ;
				print "</th>" ;
				print "<th>" ;
					print "Roll<br/>Group" ;
				print "</th>" ;
				print "<th>" ;
					print "End" ;
				print "</th>" ;
				print "<th>" ;
					print "CAS Advisor" ;
				print "</th>" ;
				print "<th>" ;
					print "Status<br/>" ;
				print "</th>" ;
				print "<th>" ;
					print "Actions" ;
				print "</th>" ;
			print "</tr>" ;
			
			$count=0;
			$rowNum="odd" ;
			while ($row=$result->fetch()) {
				if ($count%2==0) {
					$rowNum="even" ;
				}
				else {
					$rowNum="odd" ;
				}
				$count++ ;
				
				if ($row["active"]=="N") {
					$rowNum="error" ;
				}

				//COLOR ROW BY STATUS!
				print "<tr class=$rowNum>" ;
					print "<td>" ;
						print formatName("", $row["preferredName"], $row["surname"], "Student", true, true) ;
					print "</td>" ;
					print "<td>" ;
						print $row["rollGroup"] ;
					print "</td>" ;
					print "<td>" ;
						print "<b>" . $row["end"] . "</b>" ;
					print "</td>" ;
					print "<td>" ;
						if ($row["gibbonPersonIDCASAdvisor"]!="") {
							try {
								$dataAdvisor=array("gibbonPersonID"=>$row["gibbonPersonIDCASAdvisor"]);  
								$sqlAdvisor="SELECT surname, preferredName FROM gibbonPerson WHERE gibbonPersonID=:gibbonPersonID AND status='Full'" ;
								$resultAdvisor=$connection2->prepare($sqlAdvisor);
								$resultAdvisor->execute($dataAdvisor);
							}
							catch(PDOException $e) { 
								print "<div class='error'>" . $e->getMessage() . "</div>" ; 
							}
							if ($resultAdvisor->rowCount()==1) {
								$rowAdvisor=$resultAdvisor->fetch() ;
								print formatName("", $rowAdvisor["preferredName"], $rowAdvisor["surname"], "Staff", true, true) ;
							}
						}
					print "</td>" ;
					print "<td>" ;
						if ($row["casStatusSchool"]=="At Risk") {
							print "<img title='At Risk' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconCross.png'/>" ;
						}
						else if ($row["casStatusSchool"]=="On Task") {
							print "<img title='On Task' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconTick.png'/>" ;
						}
						else if ($row["casStatusSchool"]=="Excellence") {
							print "<img title='Excellence' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/like_on_small.png'/>" ;
						}
						else if ($row["casStatusSchool"]=="Incomplete") {
							print "<img title='Incomplete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconCross.png'/> Incomplete" ;
						}
						else if ($row["casStatusSchool"]=="Complete") {
							print "<img title='Complete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconTick.png'/> Complete" ;
						}
					print "</td>" ;
					print "<td>" ;
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/cas_iboCheck_details.php&gibbonPersonID=" . $row["gibbonPersonID"] . "'><img title='Details' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_right.png'/></a> " ;
					print "</td>" ;
				print "</tr>" ;
			}
		print "</table>" ;
	}
}
?>
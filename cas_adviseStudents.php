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

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_adviseStudents.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	$role=staffCASRole($guid, $_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role==FALSE) {
		//Acess denied
		print "<div class='error'>" ;
			print "You are not enroled in the IB Diploma programme." ;
		print "</div>" ;
	}
	else {
		print "<div class='trail'>" ;
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Advise CAS Students</div>" ;
		print "</div>" ;
		print "<p>" ;
			print "Your CAS staff role is $role. The students listed below are determined by your role, and student-staff relationship assignment." ;	
		print "</p>" ;
		
		try {
			if ($role=="Coordinator") {
				$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"]);  
				$sql="SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd ORDER BY rollGroup, surname, preferredName" ; 
			}
			else {
				$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "advisor"=> $_SESSION[$guid]["gibbonPersonID"]);  
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
			print "There are no students to display." ;
			print "</div>" ;
		}
		else {
			print "<div class='linkTop'>" ;
				print "Filter Roll Group: " ;
				
				?>
				<script type="text/javascript">
				$(document).ready(function() {
					$('.searchInput').val(1);
					$('.body').find("tr:odd").addClass('odd');
					$('.body').find("tr:even").addClass('even');
						
					$(".searchInput").change(function(){
						$('.body').find("tr").hide() ;
						if ($('.searchInput :selected').val() == "" ) {
							$('.body').find("tr").show() ;
						}
						else {
							$('.body').find('.' + $('.searchInput :selected').val()).show();
						}
									
						$('.body').find("tr").removeClass('odd even');
						$('.body').find('tr:visible:odd').addClass('odd');
						$('.body').find('tr:visible:even').addClass('even');
					});
				});
				</script>

				<select name="searchInput" class="searchInput" style='float: none; width: 100px'>
					<option selected value=''>All</option>
					<?
					try {
						if ($role=="Coordinator") {
							$dataSelect=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"]);  
							$sqlSelect="SELECT DISTINCT gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd ORDER BY gibbonRollGroup.nameShort" ; 
						}
						else {
							$dataSelect=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "sequenceStart"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "sequenceEnd"=>$_SESSION[$guid]["gibbonSchoolYearSequenceNumber"], "advisor"=>$_SESSION[$guid]["gibbonPersonID"]);  
							$sqlSelect="SELECT DISTINCT gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor ORDER BY gibbonRollGroup.nameShort" ; 
						}
						$resultSelect=$connection2->prepare($sqlSelect);
						$resultSelect->execute($dataSelect);
					}
					catch(PDOException $e) { 
						print "<div class='error'>" . $e->getMessage() . "</div>" ; 
					}

					while ($rowSelect=$resultSelect->fetch()) {
						print "<option value='" . $rowSelect["gibbonRollGroupID"] . "'>" . htmlPrep($rowSelect["rollGroup"]) . "</option>" ;
					}
					?>
				</select>
			<?	
			print "</div>" ;
			
			print "<table cellspacing='0' style='width: 100%'>" ;
				print "<tr class='head'>" ;
					print "<th>" ;
						print "Name" ;
					print "</th>" ;
					print "<th>" ;
						print "Roll<br/>Group" ;
					print "</th>" ;
					print "<th>" ;
						print "Start" ;
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
				print "<tbody class='body'>" ;
				
					$count=0;
					$rowNum="odd" ;
					while ($row=$result->fetch()) {
						$count++ ;
						
						//COLOR ROW BY STATUS!
						print "<tr class='" . $row["gibbonRollGroupID"] . "' id='" . $row["rollGroup"] ."' name='" . $row["rollGroup"] ."'>" ;
							print "<td>" ;
								print formatName("", $row["preferredName"], $row["surname"], "Student", true, true) ;
							print "</td>" ;
							print "<td>" ;
								print $row["rollGroup"] ;
							print "</td>" ;
							print "<td>" ;
								print "<b>" . $row["start"] . "</b>" ;
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
										print formatName("", $rowAdvisor["preferredName"], $rowAdvisor["surname"], "Staff", false, true) ;
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
								print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/cas_adviseStudents_details.php&gibbonPersonID=" . $row["gibbonPersonID"] . "'><img title='Details' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_right.png'/></a> " ;
							print "</td>" ;
						print "</tr>" ;
					}
				print "</tbody>" ;
			print "</table>" ;
		}
	}
}
?>
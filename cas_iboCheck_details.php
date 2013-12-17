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

@session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_iboCheck_details.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
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
			$row=$result->fetch() ;
			
			print "<div class='trail'>" ;
			print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/cas_iboCheck.php'>IBO CAS Check</a> > </div><div class='trailEnd'>Student Details</div>" ;
			print "</div>" ;
			
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
				else if ($updateReturn=="success0") {
					$updateReturnMessage ="Update was successful." ;	
					$class="success" ;
				}
				print "<div class='$class'>" ;
					print $updateReturnMessage;
				print "</div>" ;
			} 
			
			if (isset($_GET["deleteReturn"])) { $deleteReturn=$_GET["deleteReturn"] ; } else { $deleteReturn="" ; }
			$deleteReturnMessage ="" ;
			$class="error" ;
			if (!($deleteReturn=="")) {
				if ($deleteReturn=="success0") {
					$deleteReturnMessage ="Delete was successful." ;	
					$class="success" ;
				}
				print "<div class='$class'>" ;
					print $deleteReturnMessage;
				print "</div>" ;
			} 
			
			print "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>" ;
				print "<tr>" ;
					print "<td style='width: 34%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>" ;
						print formatName("", $row["preferredName"], $row["surname"], "Student", true, true) ;
					print "</td>" ;
					print "<td style='width: 33%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>Year Group</span><br/>" ;
						try {
							$dataDetail=array("gibbonYearGroupID"=>$row["gibbonYearGroupID"]);  
							$sqlDetail="SELECT * FROM gibbonYearGroup WHERE gibbonYearGroupID=:gibbonYearGroupID" ;
							$resultDetail=$connection2->prepare($sqlDetail);
							$resultDetail->execute($dataDetail);
						}
						catch(PDOException $e) { 
							print "<div class='error'>" . $e->getMessage() . "</div>" ; 
						}
						if ($resultDetail->rowCount()==1) {
							$rowDetail=$resultDetail->fetch() ;
							print "<i>" . $rowDetail["name"] . "</i>" ;
						}
					print "</td>" ;
					print "<td style='width: 34%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>Roll Group</span><br/>" ;
						try {
							$dataDetail=array("gibbonRollGroupID"=>$row["gibbonRollGroupID"]);  
							$sqlDetail="SELECT * FROM gibbonRollGroup WHERE gibbonRollGroupID=:gibbonRollGroupID" ;
							$resultDetail=$connection2->prepare($sqlDetail);
							$resultDetail->execute($dataDetail); 
						}
						catch(PDOException $e) { 
							print "<div class='error'>" . $e->getMessage() . "</div>" ; 
						}
						if ($resultDetail->rowCount()==1) {
							$rowDetail=$resultDetail->fetch() ;
							print "<i>" . $rowDetail["name"] . "</i>" ;
							}
					print "</td>" ;
				print "</tr>" ;
				print "<tr>" ;
					print "<td style='padding-top: 15px; width: 34%; vertical-align: top' colspan=3>" ;
						$casStatusSchool=$row["casStatusSchool"] ;
						print "<span style='font-size: 115%; font-weight: bold'>CAS Status</span><br/>" ;
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
				print "</tr>" ;
			print "</table>" ;
			
			
			print "<h2>" ;
				print "Commitments" ;
			print "</h2>" ;
			
			try {
				$data=array("gibbonPersonID"=>$gibbonPersonID);  
				$sql="SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID ORDER BY approval, name" ; 
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
			
			if ($result->rowCount()<1) {
				print "<div class='error'>" ;
				print "There are no commitments to display." ;
				print "</div>" ;
			}
			else {
				print "<table cellspacing='0' style='width: 100%'>" ;
					print "<tr class='head'>" ;
						print "<th style='vertical-align: bottom'>" ;
							print "Commitment" ;
						print "</th>" ;
						print "<th style='vertical-align: bottom'>" ;
							print "Status" ;
						print "</th>" ;
						print "<th style='vertical-align: bottom'>" ;
							print "Timing" ;
						print "</th>" ;
						print "<th style='vertical-align: bottom'>" ;
							print "Supervisor" ;
						print "</th>" ;
						print "<th style='vertical-align: bottom'>" ;
							print "Actions" ;
					print "</tr>" ;
					
					$count=0;
					$rowNum="odd" ;
					$intended=array() ;
					$complete=array() ;
					while ($row=$result->fetch()) {
						if ($count%2==0) {
							$rowNum="even" ;
						}
						else {
							$rowNum="odd" ;
						}
						$count++ ;
						
						//COLOR ROW BY STATUS!
						print "<tr class=$rowNum>" ;
							print "<td>" ;
								print $row["name"] ;
							print "</td>" ;
							print "<td>" ;
								if ($row["approval"]=="Pending" OR $row["approval"]=="Not Approved") {
									print $row["approval"] ;
								}
								else {
									print $row["status"] ;
								}
							print "</td>" ;
							print "<td>" ;
								if (substr($row["dateStart"],0,4)==substr($row["dateEnd"],0,4)) {
										if (substr($row["dateStart"],5,2)==substr($row["dateEnd"],5,2)) {
											print date("F", mktime(0, 0, 0, substr($row["dateStart"],5,2))) . " "  . substr($row["dateStart"],0,4) ;
										}
										else {
											print date("F", mktime(0, 0, 0, substr($row["dateStart"],5,2))) . " - " . date("F", mktime(0, 0, 0, substr($row["dateEnd"],5,2))) . " "  . substr($row["dateStart"],0,4) ;
										}
									}
									else {
										print date("F", mktime(0, 0, 0, substr($row["dateStart"],5,2))) . " "  . substr($row["dateStart"],0,4) . " - " . date("F", mktime(0, 0, 0, substr($row["dateEnd"],5,2))) . " "  . substr($row["dateEnd"],0,4) ;
									}
							print "</td>" ;
							print "<td>" ;
								if ($row["supervisorEmail"]!="") {
									print "<a href='mailto:" . $row["supervisorEmail"] . "'>" . $row["supervisorName"] . "</a>" ;
								}
								else {
									print $row["supervisorName"] ;
								}
							print "</td>" ;
							print "<td>" ;
								print "<a class='thickbox' href='" . $_SESSION[$guid]["absoluteURL"] . "/fullscreen.php?q=/modules/" . $_SESSION[$guid]["module"] . "/cas_iboCheck_full.php&gibbonPersonID=$gibbonPersonID&ibDiplomaCASCommitmentID=" . $row["ibDiplomaCASCommitmentID"] . "&width=1000&height=550'><img title='View' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_right.png'/></a> " ;
							print "</td>" ;
						print "</tr>" ;
					}
				print "</table>" ;
				
				print "<h2>" ;
					print "Reflections" ;
				print "</h2>" ;
				try {
					$data=array("gibbonPersonID"=>$gibbonPersonID);  
					$sql="SELECT * FROM ibDiplomaCASReflection WHERE gibbonPersonID=:gibbonPersonID ORDER BY timestamp" ; 
					$result=$connection2->prepare($sql);
					$result->execute($data); 
				}
				catch(PDOException $e) { 
					print "<div class='error'>" . $e->getMessage() . "</div>" ; 
				}
			
				print "<div class='linkTop'>" ;
					print "Filter Commitment: " ;
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
						<option selected value='General'>General CAS</option>
						<?
						try {
							$dataSelect=array("gibbonPersonID"=>$gibbonPersonID);  
							$sqlSelect="SELECT DISTINCT ibDiplomaCASCommitment.ibDiplomaCASCommitmentID, name FROM ibDiplomaCASReflection JOIN ibDiplomaCASCommitment ON (ibDiplomaCASCommitment.ibDiplomaCASCommitmentID=ibDiplomaCASReflection.ibDiplomaCASCommitmentID) WHERE ibDiplomaCASReflection.gibbonPersonID=:gibbonPersonID ORDER BY timestamp" ; 
							$resultSelect=$connection2->prepare($sqlSelect);
							$resultSelect->execute($dataSelect);
						}
						catch(PDOException $e) { }
					
						while ($rowSelect=$resultSelect->fetch()) {
							print "<option value='" . $rowSelect["ibDiplomaCASCommitmentID"] . "'>" . htmlPrep($rowSelect["name"]) . "</option>" ;
						}
						?>
					</select>
				<?	
				print "</div>" ;
				
				if ($result->rowCount()<1) {
					print "<div class='error'>" ;
					print "There are no reflections to display." ;
					print "</div>" ;
				}
				else {
					print "<table cellspacing='0' style='width: 100%'>" ;
						print "<tr class='head'>" ;
							print "<th style='vertical-align: bottom'>" ;
								print "Commitment" ;
							print "</th>" ;
							print "<th style='vertical-align: bottom'>" ;
								print "Date" ;
							print "</th>" ;
							print "<th style='vertical-align: bottom'>" ;
								print "Title" ;
							print "</th>" ;
							print "<th style='vertical-align: bottom'>" ;
								print "Action" ;
							print "</th>" ;
						print "</tr>" ;
						print "<tbody class='body'>" ;
							$count=0;
							$rowNum="odd" ;
							while ($row=$result->fetch()) {
								$count++ ;
								
								$class=$row["ibDiplomaCASCommitmentID"] ;
								if ($class=="") {
									$class="General" ;
								}
								print "<tr class='$class'>" ;
									print "<td>" ;
										if (is_null($row["ibDiplomaCASCommitmentID"])) {
											print "<b><i>General CAS</i></b>" ;
										}
										else {
											try {
												$dataCommitment=array("ibDiplomaCASCommitmentID"=>$row["ibDiplomaCASCommitmentID"]);  
												$sqlCommitment="SELECT * FROM ibDiplomaCASCommitment WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID" ;
												$resultCommitment=$connection2->prepare($sqlCommitment);
												$resultCommitment->execute($dataCommitment);
											}
											catch(PDOException $e) { 
												print "<div class='error'>" . $e->getMessage() . "</div>" ; 
											}
											
											if ($resultCommitment->rowCount()==1) {
												$rowCommitment=$resultCommitment->fetch() ;
												print $rowCommitment["name"] ;
											}
										}
									print "</td>" ;
									print "<td>" ;
										print dateConvertBack(substr($row["timestamp"],0,10)) ;
									print "</td>" ;
									print "<td>" ;
										print $row["title"] ;
									print "</td>" ;
									print "<td>" ;
										print "<script type='text/javascript'>" ;	
											print "$(document).ready(function(){" ;
												print "\$(\".comment-$count\").hide();" ;
												print "\$(\".show_hide-$count\").fadeIn(1000);" ;
												print "\$(\".show_hide-$count\").click(function(){" ;
												print "\$(\".comment-$count\").fadeToggle(1000);" ;
												print "});" ;
											print "});" ;
										print "</script>" ;
										print "<a class='show_hide-$count' onclick='false'  href='#'><img style='padding-right: 5px' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/page_down.png' alt='Show Comment' onclick='return false;' /></a>" ;
									print "</td>" ;
								print "</tr>" ;
								print "<tr class='comment-$count' id='comment-$count'>" ;
									print "<td style='background-color: #D4F6DC' colspan=4>" ;
										print $row["reflection"] ;
									print "</td>" ;
								print "</tr>" ;
							}			
						print "</tbody'>" ;
					print "</table>" ;
					?>
					<script type="text/javascript">
						$(document).ready(function() {
							$('.searchInput').val(1);
							$('.body').find("tr:visible:odd").addClass('odd');
							$('.body').find("tr:visible:even").addClass('even');
								
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
					<?
				}											
			}
		}	
	}
}
?>
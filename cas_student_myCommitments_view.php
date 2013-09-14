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

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_student_myCommitments_view.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this page." ;
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
				$row=$result->fetch() ;
				
				print "<h1>" ;
					print $row["name"] . "<br>" ;
				print "</h1>" ;
				
				print "<div style='width:510px; float: left; font-size: 115%; margin-top: -5px'>" ;
					try {
						$dataReflections=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"], "ibDiplomaCASCommitmentID"=>$ibDiplomaCASCommitmentID);  
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
						print "<tr>" ;
							print "<td style='padding-top: 15px; width: 33%; vertical-align: top'>" ;
								print "<span style='font-size: 115%; font-weight: bold'>Supervisor</span><br/>" ;
								print $row["supervisorName"] ;
							print "</td>" ;
							print "<td style='padding-top: 15px; width: 33%; vertical-align: top'>" ;
								print "<span style='font-size: 115%; font-weight: bold'>Phone</span><br/>" ;
								print $row["supervisorPhone"] ;
							print "</td>" ;
							print "<td style='padding-top: 15px; width: 33%; vertical-align: top'>" ;
								print "<span style='font-size: 115%; font-weight: bold'>Email</span><br/>" ;
								print $row["supervisorEmail"] ;
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
					print "</table>" ;
				print "</div>" ;
			}
		}
	}
}		
?>
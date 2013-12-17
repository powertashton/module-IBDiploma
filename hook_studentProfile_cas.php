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
include "./modules/IB Diploma/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/hook_studentProfile_cas.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	if (enroled($guid, $gibbonPersonID, $connection2)==FALSE) {
		//Acess denied
		print "<div class='error'>" ;
			print "The selected student is not enroled in the IB Diploma programme." ;
		print "</div>" ;
	}
	else {
		try {
			$data=array("gibbonPersonID"=>$gibbonPersonID);  
			$sql="SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID ORDER BY approval, name" ; 
			$result=$connection2->prepare($sql);
			$result->execute($data); 
		}
		catch(PDOException $e) { 
			print "<div class='error'>" ;
			print "Commitments cannot be displayed." ;
			print "</div>" ;
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
						print "Name" ;
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
							print $row["supervisorName"] ;
						print "</td>" ;
					print "</tr>" ;
				}					
			print "</table>" ;
		}
	}	
}
?>
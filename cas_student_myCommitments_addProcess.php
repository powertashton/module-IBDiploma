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

$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/cas_student_myCommitments_add.php" ;

if (isActionAccessible($guid, $connection2, "/modules/IB Diploma/cas_student_myCommitments_add.php")==FALSE) {

	//Fail 0
	$URL = $URL . "&addReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	if (enroled($guid, $_SESSION[$guid]["gibbonPersonID"], $connection2)==FALSE) {
		//Fail 0
		$URL = $URL . "&addReturn=fail0" ;
		header("Location: {$URL}");
	}
	else {
		//Proceed!
		$name=$_POST["name"] ;
		$status=$_POST["status"] ;
		$dateStart=dateConvert($_POST["dateStart"]) ;
		if ($_POST["dateEnd"]=="") {
			$dateEnd=NULL ;
		}
		else {
			$dateEnd=dateConvert($_POST["dateEnd"]) ;
		}
		
		$supervisorName=$_POST["supervisorName"] ;
		$supervisorEmail=$_POST["supervisorEmail"] ;
		$supervisorPhone=$_POST["supervisorPhone"] ;
		
		$description=$_POST["description"] ;
		
		if ($name=="" OR $status=="" OR $dateStart=="" OR $supervisorName=="" OR $supervisorEmail=="" OR $supervisorPhone=="") {
			//Fail 3
			$URL = $URL . "&addReturn=fail3" ;
			header("Location: {$URL}");
		}
		else {
			//Check unique inputs for uniqueness
			try {
				$data=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"], "name"=>$name);  
				$sql="SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND name=:name" ;
				$result=$connection2->prepare($sql);
				$result->execute($data); 
			}
			catch(PDOException $e) { 
				//Fail 2
				$URL = $URL . "&addReturn=fail2" ;
				header("Location: {$URL}");
				break ;
			}
			
			if ($result->rowCount()>0) {
				//Fail 4
				$URL = $URL . "&addReturn=fail4" ;
				header("Location: {$URL}");
			}
			else {
				try {
					$data=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"], "name"=>$name, "status"=>$status, "dateStart"=>$dateStart, "dateEnd"=>$dateEnd, "supervisorName"=>$supervisorName, "supervisorEmail"=>$supervisorEmail, "supervisorPhone"=>$supervisorPhone, "description"=>$description);  
					$sql="INSERT INTO ibDiplomaCASCommitment SET gibbonPersonID=:gibbonPersonID, name=:name, status=:status, dateStart=:dateStart, dateEnd=:dateEnd, supervisorName=:supervisorName, supervisorEmail=:supervisorEmail, supervisorPhone=:supervisorPhone, description=:description" ;
					$result=$connection2->prepare($sql);
					$result->execute($data);  
				}
				catch(PDOException $e) { 
					//Fail 2
					$URL = $URL . "&addReturn=fail2" ;
					header("Location: {$URL}");
					break ;
				}
				
				//Success 0
				$URL = $URL . "&addReturn=success0" ;
				header("Location: {$URL}");
			}
		}
	}
}
?>
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


$key=$_POST["key"] ;
$URL="../../index.php?q=/modules/" . getModuleName($_POST["address"]) . "/cas_supervisor.php&key=$key" ;

try {
	$dataKey=array("key"=>$key);  
	$sqlKey="SELECT ibDiplomaCASSupervisorFeedback.*, ibDiplomaCASCommitment.*, surname, preferredName FROM ibDiplomaCASSupervisorFeedback JOIN ibDiplomaCASCommitment ON (ibDiplomaCASSupervisorFeedback.ibDiplomaCASCommitmentID=ibDiplomaCASCommitment.ibDiplomaCASCommitmentID) JOIN gibbonPerson ON (ibDiplomaCASCommitment.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonPerson.status='Full' AND ibDiplomaCASSupervisorFeedback.key=:key" ;
	$resultKey=$connection2->prepare($sqlKey);
	$resultKey->execute($dataKey);
}
catch(PDOException $e) { 
	//Fail2
	$URL=$URL . "&updateReturn=fail2" ;
	header("Location: {$URL}");
	break ;
}

if ($resultKey->rowCount()<1) {
	//Fail2
	$URL=$URL . "&updateReturn=fail2" ;
	header("Location: {$URL}");
}
else {
	$rowKey=$resultKey->fetch() ;
	
	//Check for pre-existing complete form for this commitment
	try {
		$dataComplete=array("ibDiplomaCASCommitmentID"=>$rowKey["ibDiplomaCASCommitmentID"]);  
		$sqlComplete="SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='Y'" ;
		$resultComplete=$connection2->prepare($sqlComplete);
		$resultComplete->execute($dataComplete);
	}
	catch(PDOException $e) { 
		//Fail2
		$URL=$URL . "&updateReturn=fail2" ;
		header("Location: {$URL}");		
		break ;
	}
	
	if ($resultComplete->rowCount()>0) {
		//Fail2
		$URL=$URL . "&updateReturn=fail2" ;
		header("Location: {$URL}");
	}
	else {
		//Get variables
		$attendance=$_POST["attendance"] ;
		$evaluation=$_POST["evaluation"] ;
		if ($attendance=="" OR $evaluation=="") {
			//Fail1
			$URL=$URL . "&updateReturn=fail1" ;
			header("Location: {$URL}");
		}
		else {
			try {
				$data=array("attendance"=>$attendance, "evaluation"=>$evaluation, "key"=>$key);  
				$sql="UPDATE ibDiplomaCASSupervisorFeedback SET complete='Y', attendance=:attendance, evaluation=:evaluation WHERE ibDiplomaCASSupervisorFeedback.key=:key" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);  
			}
			catch(PDOException $e) { 
				//Fail 2
				$URL=$URL . "&updateReturn=fail2" ;
				header("Location: {$URL}");
				break ;
			}

			//Success 0
			$URL=$URL . "&updateReturn=success0" ;
			header("Location: {$URL}");
		}
	}
}
?>
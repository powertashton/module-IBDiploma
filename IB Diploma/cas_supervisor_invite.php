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

@session_start();

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

//LOAD FORM OBJECTS
use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_supervisor_invite.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffCASRole($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role == false) { $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {

        $page->breadcrumbs
            ->add(__('Invite CAS Supervisor Feedback'));
            
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        $step = null;
        if (isset($_GET['step'])) {
            $step = $_GET['step'];
        }
        if ($step != 1 and $step != 2 and $step != 3) {
            $step = 1;
        }

        //Step 1
        if ($step == 1) {
            echo '<h3>';
            echo 'Step 1';
            echo '</h3>';

            $form = Form::create('invitationType',$_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
            
            $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/cas_supervisor_invite.php');
            $form->addHiddenValue('step', 2);
            
            $row = $form->addRow();
            $row->addLabel('Invitation Type', __('Invitation Type'));
            $row->addRadio("type")->fromArray(array("Single" =>__("Single Commitment"), "Multiple" =>__("Multiple Commitments")))->isRequired()->inline();
            
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit("Proceed");
            echo $form->getOutput();


        } elseif ($step == 2) {
            $type = $_GET['type'];
            if ($type != 'Single' and $type != 'Multiple') {
                $type = 'Single';
            }

            echo '<h3>';
            echo "Step 2 - $type";
            echo '</h3>';
            
            
            $form = Form::create('supervisorInvite',$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/cas_supervisor_invite.php&step=3'", 'post');
            
            $form->setFactory(DatabaseFormFactory::create($pdo));
            
            if ($type == 'Single') {
                
                 try {
                    if ($role == 'Coordinator') {
                        $data = array('coordinator' => $_SESSION[$guid]['gibbonPersonID']);
                        $sql = "SELECT gibbonPerson.gibbonPersonID as value, concat(gibbonPerson.surname,', ',gibbonPerson.firstName, ' (', gibbonRollGroup.nameShort,')') as name FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonPerson.status='Full' ORDER BY nameShort, surname, preferredName";
                    } else {
                        $data = array('advisor' => $_SESSION[$guid]['gibbonPersonID']);
                        $sql = "SELECT gibbonPerson.gibbonPersonID as value, concat(gibbonPerson.surname,', ',gibbonPerson.firstName, ' (', gibbonRollGroup.nameShort,')') as name FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID)  LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonPerson.status='Full' AND gibbonPersonIDCASAdvisor=:advisor ORDER BY nameShort, surname, preferredName";
                    }
                } catch (PDOException $e) {
                }
                

                $row = $form->addRow();
                    $row->addLabel('gibbonPersonID', __('Student'));
                    $row->addSelect('gibbonPersonID')->fromQuery($pdo, $sql, $data)->placeholder()->isRequired();                
                
                try {
                        $data2 = array();
                        $sql2 = "SELECT ibDiplomaCASCommitmentID as value, concat(ibDiplomaCASCommitment.name, ' (', ibDiplomaCASCommitment.supervisorName, ')') as name, gibbonPersonID as chainedTo FROM ibDiplomaCASCommitment WHERE approval='Approved'  ";
                    } catch (PDOException $e) {
                    }
                    
                 $row = $form->addRow();
                        $row->addLabel('ibDiplomaCASCommitmentID', __('Commitment'));
                        $row->addSelect('ibDiplomaCASCommitmentID')->fromQueryChained($pdo, $sql2, $data2, 'gibbonPersonID')->placeholder();
                                    
                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit('Proceed');
                echo $form->getOutput();

            
            
            } else {
                echo '<tr>';
                echo "<td style='text-align: justify' colspan=2>";
                echo 'By clicking proceed you will generate invitations for every student you take care of for CAS who is in the final year of their IB Diploma. As a coordinator this will be all students in the cohort: as an advisor, just the students you advise.<br/><br/>';
                echo '<b>Invitations will be generated for every approved, completed commitment which does not yet have supervisor feedback.<b/>';
                echo '</td> ';
                echo '</tr>';
            }
                
                
            
        
        } elseif ($step == 3) {
            $type = $_POST['type'];
            if ($type != 'Single' and $type != 'Multiple') {
                $type = 'Single';
            }

            echo '<h3>';
            echo "Step 3 - $type";
            echo '</h3>';

            if ($type == 'Single') {
                //Get and check variables
                $gibbonPersonID = $_POST['gibbonPersonID'];
                $ibDiplomaCASCommitmentID = $_POST['ibDiplomaCASCommitmentID'];
                if ($gibbonPersonID == '' or $ibDiplomaCASCommitmentID == '') {
                    $page->addError(__('You have not specified a student or commitment.'));
                } else {
                    try {
                        if ($role == 'Coordinator') {
                            $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'gibbonPersonID' => $gibbonPersonID);
                            $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName";
                        } else {
                            $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'advisor' => $_SESSION[$guid]['gibbonPersonID'], 'gibbonPersonID' => $gibbonPersonID);
                            $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName";
                        }
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $page->addError($e->getMessage());
                    }

                    if ($result->rowCount() != 1) {
                        $page->addError(__('Invite cannot be issued.'));
                    } else {
                        $values = $result->fetch();
                        $student = $values['preferredName'].' '.$values['surname'];
                        $studentFirst = $values['preferredName'];

                        //Check existence of and access to this commitment.
                        try {
                            $data = array('gibbonPersonID' => $gibbonPersonID, 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                            $sql = "SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved' AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $page->addError($e->getMessage());
                        }

                        if ($result->rowCount() != 1) {
                            $page->addError(__('Invite cannot be issued.'));
                        } else {
                            $values = $result->fetch();

                            //Check for completion
                            try {
                                $dataComplete = array('ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                                $sqlComplete = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE complete='Y' AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID";
                                $resultComplete = $connection2->prepare($sqlComplete);
                                $resultComplete->execute($dataComplete);
                            } catch (PDOException $e) {
                                $page->addError($e->getMessage());
                            }

                            if ($resultComplete->rowCount() > 0) {
                                echo "<div class='success'>";
                                echo 'This commitment has already had feedback completed, so no invite is required.';
                                echo '</div>';
                            } else {
                                //Lock table
                                $lock = true;
                                try {
                                    $sqlLock = 'LOCK TABLE ibDiplomaCASSupervisorFeedback WRITE';
                                    $resultLock = $connection2->query($sqlLock);
                                } catch (PDOException $e) {
                                    $lock = false;
                                    $page->addError($e->getMessage());
                                }

                                if ($lock) {
                                    //Let's go! Create key, send the invite
                                    $continue = false;
                                    $count = 0;
                                    while ($continue == false and $count < 100) {
                                        $key = randomPassword(40);
                                        try {
                                            $dataUnique = array('key' => $key);
                                            $sqlUnique = 'SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASSupervisorFeedback.key=:key';
                                            $resultUnique = $connection2->prepare($sqlUnique);
                                            $resultUnique->execute($dataUnique);
                                        } catch (PDOException $e) {
                                        }

                                        if ($resultUnique->rowCount() == 0) {
                                            $continue = true;
                                        }
                                        ++$count;
                                    }

                                    if ($continue == false) {
                                        $page->addError(__('A unique key cannot be generated, so it is not possible to continue.'));
                                    } else {
                                        //Write to database
                                        $proceed = true;
                                        try {
                                            $data = array('ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID, 'key' => $key);
                                            $sql = 'INSERT INTO ibDiplomaCASSupervisorFeedback SET ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID, ibDiplomaCASSupervisorFeedback.key=:key';
                                            $result = $connection2->prepare($sql);
                                            $result->execute($data);
                                        } catch (PDOException $e) {
                                            $proceed = false;
                                            $page->addError($e->getMessage());
                                        }

                                        if ($proceed) {
                                            //Unlock table
                                            try {
                                                $sql = 'UNLOCK TABLES';
                                                $result = $connection2->query($sql);
                                            } catch (PDOException $e) {
                                            }

                                            $to = $values['supervisorEmail'];
                                            $subject = $_SESSION[$guid]['organisationNameShort'].' CAS Supervisor Feedback Request';
                                            $body = 'Dear '.$values['supervisorName'].',<br/><br/>';
                                            $body = $body."We greatly appreciate your support as a CAS activity supervisor to $student (".$_SESSION[$guid]['organisationName'].'). In order for this activity ('.$values['name'].') to count towards '.$studentFirst."'s IB Diploma, we require a small amount of feedback from you.<br/><br/>";
                                            $body = $body."If you are willing and able to provide us with this feedback, you may do so by <a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/IB Diploma/cas_supervisor.php&key=$key'>clicking here</a>.<br/><br/>";
                                            $body = $body.'Your assistance is most appreciated. Regards,<br/><br/>';
                                            $body = $body.$_SESSION[$guid]['preferredName'].' '.$_SESSION[$guid]['surname'];
                                            $headers = 'From: '.$_SESSION[$guid]['email']."\r\n";
                                            $headers .= "MIME-Version: 1.0\r\n";
                                            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

                                            if (mail($to, $subject, $body, $headers)) {
                                                echo "<div class='success'>";
                                                echo "The invite has been created and emailed to $to.";
                                                echo '</div>';
                                            } else {
                                                echo "<div class='warning'>";
                                                echo 'The invite has been created, but could not be emailed. You may email the following link supervisor ('.$values['supervisorName'].') at '.$values['supervisorEmail'].': '.$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/IB Diploma/cas_supervisor.php&key=$key";
                                                echo '</div>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                //Get list of students
                try {
                    if ($role == 'Coordinator') {
                        $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber']);
                        $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd ORDER BY rollGroup, surname, preferredName";
                    } else {
                        $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'advisor' => $_SESSION[$guid]['gibbonPersonID']);
                        $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor ORDER BY rollGroup, surname, preferredName";
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }

                if ($result->rowCount() < 1) {
                    $page->addError(__('Invites cannot be issued.'));
                } else {
                    while ($values = $result->fetch()) {
                        $student = $values['preferredName'].' '.$values['surname'];
                        $studentFirst = $values['preferredName'];

                        echo '<h4>';
                        echo $student.' ('.$values['rollGroup'].')';
                        echo '</h4>';

                        //Scan through commitments for each student look for ones that are approved, complete and have not feedback.
                        try {
                            $dataCommitment = array('gibbonPersonID' => $values['gibbonPersonID']);
                            $sqlCommitment = "SELECT * FROM ibDiplomaCASCommitment WHERE status='Complete' AND approval='Approved' AND gibbonPersonID=:gibbonPersonID";
                            $resultCommitment = $connection2->prepare($sqlCommitment);
                            $resultCommitment->execute($dataCommitment);
                        } catch (PDOException $e) {
                            $page->addError($e->getMessage());
                        }

                        if ($resultCommitment->rowCount() > 0) {
                            while ($valuesCommitment = $resultCommitment->fetch()) {
                                //Check for completion
                                try {
                                    $dataComplete = array('ibDiplomaCASCommitmentID' => $valuesCommitment['ibDiplomaCASCommitmentID']);
                                    $sqlComplete = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE complete='Y' AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID";
                                    $resultComplete = $connection2->prepare($sqlComplete);
                                    $resultComplete->execute($dataComplete);
                                } catch (PDOException $e) {
                                    $page->addError($e->getMessage());
                                }

                                if ($resultComplete->rowCount() <= 0) {
                                    //Lock table
                                    $lock = true;
                                    try {
                                        $sqlLock = 'LOCK TABLE ibDiplomaCASSupervisorFeedback WRITE';
                                        $resultLock = $connection2->query($sqlLock);
                                    } catch (PDOException $e) {
                                        $lock = false;
                                        $page->addError(__($valuesCommitment['name'].': Invite cannot be issued due to a database error.'));
                                    }

                                    if ($lock) {
                                        //Generate form and key for each commitment, and send email
                                        $continue = false;
                                        $count = 0;
                                        while ($continue == false and count < 100) {
                                            $key = randomPassword(40);
                                            try {
                                                $dataUnique = array('key' => $key);
                                                $sqlUnique = 'SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASSupervisorFeedback.key=:key';
                                                $resultUnique = $connection2->prepare($sqlUnique);
                                                $resultUnique->execute($dataUnique);
                                            } catch (PDOException $e) {
                                            }

                                            if ($resultUnique->rowCount() == 0) {
                                                $continue = true;
                                            }
                                            ++$count;
                                        }

                                        //Unlock table
                                        try {
                                            $sqlUnlock = 'UNLOCK TABLES';
                                            $resultUnlock = $connection2->query($sqlUnlock);
                                        } catch (PDOException $e) {
                                        }

                                        if ($continue == false) {
                                            $page->addError(__($valuesCommitment['name'].': A unique key could not be generated, so the invite could not be sent.'));
                                        } else {
                                            //Write to database
                                            $proceed = true;
                                            try {
                                                $dataWrite = array('ibDiplomaCASCommitmentID' => $valuesCommitment['ibDiplomaCASCommitmentID'], 'key' => $key);
                                                $sqlWrite = 'INSERT INTO ibDiplomaCASSupervisorFeedback SET ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID, ibDiplomaCASSupervisorFeedback.key=:key';
                                                $resultWrite = $connection2->prepare($sqlWrite);
                                                $resultWrite->execute($dataWrite);
                                            } catch (PDOException $e) {
                                                $proceed = false;
                                                $page->addError(__($valuesCommitment['name'].': Invite cannot be issued due to a database error.'));
                                            }

                                            if ($proceed) {
                                                $to = $valuesCommitment['supervisorEmail'];
                                                $subject = $_SESSION[$guid]['organisationNameShort'].' CAS Supervisor Feedback Request';
                                                $body = 'Dear '.$valuesCommitment['supervisorName'].',<br/><br/>';
                                                $body = $body."We great appreciate your support as a CAS activity supervisor to $student (".$_SESSION[$guid]['organisationName'].'). In order for this activity ('.$valuesCommitment['name'].') to count towards '.$studentFirst."'s IB Diploma, we require a small amount of feedback from you.<br/><br/>";
                                                $body = $body."If you are willing and able to provide us with this feedback, you may do so by <a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/IB Diploma/cas_supervisor.php&key=$key'>clicking here</a>.<br/><br/>";
                                                $body = $body.'Your assistance is most appreciated. Regards,<br/><br/>';
                                                $body = $body.$_SESSION[$guid]['preferredName'].' '.$_SESSION[$guid]['surname'];
                                                $headers = 'From: '.$_SESSION[$guid]['email']."\r\n";
                                                $headers .= "MIME-Version: 1.0\r\n";
                                                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

                                                if (mail($to, $subject, $body, $headers)) {
                                                    echo "<div class='success'>";
                                                    echo $valuesCommitment['name'].": An invite has been created and emailed to $to.";
                                                    echo '</div>';
                                                } else {
                                                    echo "<div class='warning'>";
                                                    echo $valuesCommitment['name'].': An invite has been created, but could not be email. You may email the following link supervisor ('.$values['supervisorName'].') at '.$values['supervisorEmail'].': '.$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/IB Diploma/cas_supervisor.php&key=$key";
                                                    echo '</div>';
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

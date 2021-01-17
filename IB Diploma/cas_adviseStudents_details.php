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

use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\IBDiploma\Domain\CommitmentGateway;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Domain\DataSet;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_adviseStudents_details.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffCASRole($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role == false) { $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {
        $gibbonPersonID = $_GET['gibbonPersonID'];
        if ($gibbonPersonID == '') {
            $page->addError(__('You have not specified a student.'));
        } else {
            try {
                if ($role == 'Coordinator') {
                    $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, image_240, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName";
                } else {
                    $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'sequenceStart' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'sequenceEnd' => $_SESSION[$guid]['gibbonSchoolYearSequenceNumber'], 'advisor' => $_SESSION[$guid]['gibbonPersonID'], 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, image_240, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY rollGroup, surname, preferredName";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError($e->getMessage());
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The specified student does not exist, or you do not have access to them.'));
            } else {
                $values = $result->fetch();
                $image_240 = $values['image_240'];
                $casStatusSchool = $values['casStatusSchool'];
                
                $page->breadcrumbs
                    ->add(__('Advise CAS Students'), 'cas_adviseStudents.php')
                    ->add(__('Advise Student'));
                    
                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }
                $table = DataTable::createDetails('student');
                $table->addColumn('name', __('Name'))->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', 'true']));
                $table->addColumn('rollGroup', __('Roll Group'));
                $table->addColumn('casStatusSchool', __('CAS Status'));
                echo $table->render([$values]);
                
                $subpage = null;
                if (isset($_GET['subpage'])) {
                    $subpage = $_GET['subpage'];
                }
                if ($subpage == '') {
                    $subpage = 'Overview';
                }

                echo '<h2>';
                echo $subpage;
                echo '</h2>';

                if ($subpage == 'Overview') {
                    try {
                        $data = array('gibbonPersonID' => $gibbonPersonID);
                        $sql = 'SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID ORDER BY approval, name';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $page->addError($e->getMessage());
                    }

                    if ($result->rowCount() < 1) {
                        $page->addError(__('There are no commitments to display.'));
                    } else {
                        $CommitmentGateway = $container->get(CommitmentGateway::class);
                        $criteria = $CommitmentGateway
                            ->newQueryCriteria()
                            ->filterBy('gibbonPersonID', $gibbonPersonID)
                            ->sortBy('approval')
                            ->fromPOST();
                        //TODO: FILTER BY ROLE/GIBBONPERSONID
                        $commitment = $CommitmentGateway->queryCommitments($criteria);
    
                        $userGateway = $container->get(UserGateway::class);
   
    
                        $table = DataTable::createPaginated('Commitments', $criteria);
                        $table->addHeaderAction('add', __('New'))
                            ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_student_myCommitments_add.php')
                            ->displayLabel();
        
                        $table->addColumn('name', __('Commitment Name'));
                        $table->addColumn('status', __('Status'))
                            ->format(function ($row) {
                                if ($row['approval'] == 'Pending' or $row['approval'] == 'Not Approved') {
                                    return $row['approval'];
                                } else {
                                    return $row['status'];
                                }
                            });
                        $table->addColumn('timing', __('Timing'))
                            ->notSortable()
                            ->format(function ($row) {
                                if (substr($row['dateStart'], 0, 4) == substr($row['dateEnd'], 0, 4)) {
                                    if (substr($row['dateStart'], 5, 2) == substr($row['dateEnd'], 5, 2)) {
                                        return date('F', mktime(0, 0, 0, substr($row['dateStart'], 5, 2))).' '.substr($row['dateStart'], 0, 4);
                                    } else {
                                        return date('F', mktime(0, 0, 0, substr($row['dateStart'], 5, 2))).' - '.date('F', mktime(0, 0, 0, substr($row['dateEnd'], 5, 2))).' '.substr($row['dateStart'], 0, 4);
                                    }
                                } else {
                                    return date('F', mktime(0, 0, 0, substr($row['dateStart'], 5, 2))).' '.substr($row['dateStart'], 0, 4).' - '.date('F', mktime(0, 0, 0, substr($row['dateEnd'], 5, 2))).' '.substr($row['dateEnd'], 0, 4);
                                }
                            });
            
                        $table->addColumn('supervisor', __('Supervisor'))
                            ->notSortable()
                            ->format(function ($row) {
                                if ($row['supervisorEmail'] != '') {
                                    return "<a href='mailto:".$row['supervisorEmail']."'>".$row['supervisorName'].'</a>';
                                } else {
                                    return $row['supervisorName'];
                                }
                            });
                            
                        $table->addColumn('supervisor', __('Supervisor'))
                            ->notSortable()
                            ->format(function ($row) {
                                if ($row['supervisorEmail'] != '') {
                                    return "<a href='mailto:".$row['supervisorEmail']."'>".$row['supervisorName'].'</a>';
                                } else {
                                    return $row['supervisorName'];
                                }
                            });
                        
                        $table->addColumn('feedback', __('Feedback'))
                            ->notSortable()
                            ->format(function ($row) use ($connection2) {
                                $dataFeedback = array('ibDiplomaCASCommitmentID' => $row['ibDiplomaCASCommitmentID']);
                                $sqlFeedback = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='Y'";
                                $resultFeedback = $connection2->prepare($sqlFeedback);
                                 if ($resultFeedback->rowCount() == 1) {
                                    return "<img title='Supervisor Feedback Complete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick.png'/>";
                                 } else {
                                    $dataFeedback = array('ibDiplomaCASCommitmentID' => $row['ibDiplomaCASCommitmentID']);
                                    $sqlFeedback = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='N'";
                                    $resultFeedback = $connection2->prepare($sqlFeedback);
                                    $resultFeedback->execute($dataFeedback);
                                    if ($resultFeedback->rowCount() > 0) {
                                        return "<img title='Supervisor Feedback Requested' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick_light.png'/>";
                                    } 
                                }   
                            });
                    
                        $table->addActionColumn()
                                ->addParam('ibDiplomaCASCommitmentID')
                                ->addParam('gibbonPersonID')
                                ->format(function ($row, $actions) use ($gibbon) {
                                    $actions->addAction('view', __('View'))
                                        ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_adviseStudents_full.php')
                                        ->modalWindow();
                                });

                        echo $table->render($commitment);
                    }
                } elseif ($subpage == 'Reflection') {
                    //TODO: OO INTO GATEWAY AND THEN ADD A FILTER
                    $data = array('gibbonPersonID' => $gibbonPersonID);
                    $sql = 'SELECT * FROM ibDiplomaCASReflection WHERE gibbonPersonID=:gibbonPersonID ORDER BY timestamp';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                
                    $table = DataTable::create('reflections');
                    $table->addExpandableColumn('reflection');
                    $table->addColumn('commitment', __('Commitment'))
                            ->format(function ($row) use ($connection2) {
                                if (is_null($row['ibDiplomaCASCommitmentID'])) {
                                    return '<b><i>General CAS</i></b>';
                                } else {
                                        $dataCommitment = array('ibDiplomaCASCommitmentID' => $row['ibDiplomaCASCommitmentID']);
                                        $sqlCommitment = 'SELECT * FROM ibDiplomaCASCommitment WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID';
                                        $resultCommitment = $connection2->prepare($sqlCommitment);
                                        $resultCommitment->execute($dataCommitment);
                                
                                    if ($resultCommitment->rowCount() == 1) {
                                        $valuesCommitment = $resultCommitment->fetch();
                                        return $valuesCommitment['name'];
                                    }
                                }
                            });
                    $table->addColumn('date', __('Date'))
                            ->format(function ($row) use ($guid){
                             return dateConvertBack($guid, substr($row['timestamp'], 0, 10));
                            });    
                    $table->addColumn('title', __('Title'));  
                    
                    echo $table->render($result->toDataSet());
                
                
                } elseif ($subpage == 'CAS Status') {
                    echo '<p>';
                    echo "This field is used to indicate whether or not the student has, in the school's opinion, completed the CAS component of the IB Diploma.";
                    echo '</p>';


                    $form = Form::create('casStatus', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cas_adviseStudents_detailsStatusProcess.php', "post");
                    
                    $form->setFactory(DatabaseFormFactory::create($pdo));
                    
                     
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                    $form->addHiddenValue('gibbonPersonID', $gibbonPersonID);
                     
                     $row = $form->addRow();
                         $row->addHeading(__('Status *'));
                         $row->addSelect('casStatusSchool')->fromArray(array('' => __(''), 'At Risk' => __('At Risk'), 'On Task' => __('On Task'), 'Excellence' => __('Execellence'), 'Complete' => ('Complete'), 'Incomplete' => ('Incomplete')))->selected($casStatusSchool)->isRequired();
                     
                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit();
                        
                    echo $form->getOutput();

                } elseif ($subpage == 'Interview 1') {
                    try {
                        $dataInterview = array('gibbonPersonID' => $gibbonPersonID);
                        $sqlInterview = 'SELECT ibDiplomaCASInterview.*, surname, preferredName FROM ibDiplomaCASInterview JOIN gibbonPerson ON (ibDiplomaCASInterview.1_gibbonPersonIDInterviewer=gibbonPerson.gibbonPersonID) WHERE gibbonPersonIDInterviewee=:gibbonPersonID';
                        $resultInterview = $connection2->prepare($sqlInterview);
                        $resultInterview->execute($dataInterview);
                    } catch (PDOException $e) {
                        $page->addError($e->getMessage());
                    }

                    if ($resultInterview->rowCount() > 1) {
                        $page->addError(__('Interview cannot be displayed.'));
                    } else {
                        if ($resultInterview->rowCount() == 1) {
                            $valuesInterview = $resultInterview->fetch();
                        }
                    try {
                            $dataCommitments = array('gibbonPersonID' => $gibbonPersonID);
                            $sqlCommitments = "SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved' ORDER BY name";
                            $resultCommitments = $connection2->prepare($sqlCommitments);
                            $resultCommitments->execute($dataCommitments);
                        } catch (PDOException $e) {
                            $page->addError($e->getMessage());
                        }
                    if ($resultCommitments->rowCount() < 1) {
                            echo "<div class='error'>";
                            echo 'There are no commitments to display.';
                            echo '</div>';
                        } else {
                        
                        $form = Form::create('interview1', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/cas_adviseStudents_detailsInterview1Process.php");
                            
                            $form->addHiddenValue('gibbonPersonID', $gibbonPersonID);
                            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                            
                            $form->addRow()->addHeading(__('Commitment Goals'));
                                $formRow = $form->addRow();
                                $formRow->addLabel('label', __('Work with the interviewee to determine a suitable, brief goal for each approved commitment.'));
                            
                            $table = $form->addRow()->addTable()->setClass('mini fullWidth');
                                $row = $table->addHeaderRow();
                                    $row->addContent(__('Commitment'))->wrap('<div style="width: 120px;">', '</div>');
                                    $row->addContent(__('Timing'))->wrap('<div style="width: 300px;">', '</div>');
                                    $row->addContent(__('Goals'))->wrap('<div style="width: 120px;">', '</div>');
                            
                            $count = 0;
                            while ($valuesCommitments = $resultCommitments->fetch()) {
                                ++$count;
                                $row = $table->addRow();
                                    $row->addContent(__($valuesCommitments['name']));
                                    if (substr($valuesCommitments['dateStart'], 0, 4) == substr($valuesCommitments['dateEnd'], 0, 4)) {
                                            if (substr($valuesCommitments['dateStart'], 5, 2) == substr($valuesCommitments['dateEnd'], 5, 2)) {
                                                $row->addContent(__(date('F', mktime(0, 0, 0, substr($valuesCommitments['dateStart'], 5, 2))).' '.substr($valuesCommitments['dateStart'], 0, 4)));
                                            } else {
                                                $row->addContent(__(date('F', mktime(0, 0, 0, substr($valuesCommitments['dateStart'], 5, 2))).' - '.date('F', mktime(0, 0, 0, substr($valuesCommitments['dateEnd'], 5, 2))).' '.substr($valuesCommitments['dateStart'], 0, 4)));
                                            }
                                        } else {
                                            $row->addContent(__(date('F', mktime(0, 0, 0, substr($valuesCommitments['dateStart'], 5, 2))).' '.substr($valuesCommitments['dateStart'], 0, 4).' - '.date('F', mktime(0, 0, 0, substr($valuesCommitments['dateEnd'], 5, 2))).' '.substr($valuesCommitments['dateEnd'], 0, 4)));
                                        }
                                    $form->addHiddenValue($count.'-ibDiplomaCASCommitmentID', $valuesCommitments['ibDiplomaCASCommitmentID']);
                                    $row->addTextField($count.'-goals')->setValue($valuesCommitments['goals'])->maxLength(255);
                            }
                        
                            $form->addRow()->addHeading(__('Notes'));
                                $row = $form->addRow();
                                    $column = $row->addColumn();
                                        $column->addContent( __('Use this space to take notes on your conversation with the student. You may wish to consider:<i><ul><li>Is there a balance across commitments?</li><li>Are commitments genuine and meaningful?</li><li>Do commitments require student to show persistence and commitment?</li></ul></i>'));
                                        $column->addTextArea('notes')->setRows(15)->setValue($valuesInterview['1_notes'])->setClass('fullWidth');
                        
                            $form->addRow()->addHeading(__('General Information'));
                                $row = $form->addRow();
                                    $row->addLabel('interviewer', __('Interviewer'));
                                    $row->addTextField('interviewer')->setValue(formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', true, true))->readOnly()->isRequired();
                                $row = $form->addRow();
                                    $row->addLabel('date', __('Date'));
                                    $row->addDate('date')->setValue(dateConvertBack($guid, $valuesInterview['1_date']))->isRequired();
                                $row = $form->addRow();
                                    $row->addLabel('casStatusSchool', __('CAS Status'));
                                    $row->addSelect('casStatusSchool')->fromArray(array('At Risk' =>__('At Risk'), 'On Task' => __('On Task'), 'Excellence' =>__('Excellence')))->selected($casStatusSchool)->isRequired();
                        
                            $form->addHiddenValue("count", $count);
                            $row = $form->addRow();
                                $row->addFooter();
                                $row->addSubmit();
                            echo $form->getOutput();
                    }
                }
                } elseif ($subpage == 'Interview 2') {
                    try {
                        $dataInterview = array('gibbonPersonID' => $gibbonPersonID);
                        $sqlInterview = 'SELECT ibDiplomaCASInterview.*, surname, preferredName FROM ibDiplomaCASInterview JOIN gibbonPerson ON (ibDiplomaCASInterview.1_gibbonPersonIDInterviewer=gibbonPerson.gibbonPersonID) WHERE gibbonPersonIDInterviewee=:gibbonPersonID';
                        $resultInterview = $connection2->prepare($sqlInterview);
                        $resultInterview->execute($dataInterview);
                    } catch (PDOException $e) {
                        $page->addError($e->getMessage());
                    }

                    if ($resultInterview->rowCount() > 1) {
                        $page->addError(__('Interview cannot be displayed.'));
                    } else {
                        if ($resultInterview->rowCount() == 0) {
                            $page->addError(__('You have not yet completed Interview 1, and so cannot access Interview 2.'));
                        } else {
                            $valuesInterview = $resultInterview->fetch();
                            
                            
                            $form = Form::create('interview2', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/cas_adviseStudents_detailsInterview2Process.php");
                            
                            $form->addHiddenValue('gibbonPersonID', $gibbonPersonID);
                            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                            
                            $form->addRow()->addHeading(__('Commitment Goals'));
                                $formRow = $form->addRow();
                                $formRow->addContent( __('Work with the interviewee to determine which commitments you think <b>might</b> satisfy each of the outcomes listed below. The student should have pre-filled this information before Interview 2.'));
                            
                            try {
                                $dataList = array('gibbonPersonID' => $gibbonPersonID);
                                $sqlList = "SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved' ORDER BY name";
                                $resultList = $connection2->prepare($sqlList);
                                $resultList->execute($dataList);
                            } catch (PDOException $e) {
                                $page->addError($e->getMessage());
                            }

                            $list = '';
                            while ($valuesList = $resultList->fetch()) {
                                $list .= '{id: "'.$valuesList['ibDiplomaCASCommitmentID'].'", name: "'.$valuesList['name'].'"},';
                            }
                            $list = substr($list, 0, -1);
                            for ($i = 1; $i < 9; ++$i) {
                                switch ($i) {
                                    case 1:
                                        $title = "<span style='font-weight: bold' title='They are able to see themselves as individuals with various skills and abilities, some more developed than others, and understand that they can make choices about how they wish to move forward.'>Increased their awareness of their own strengths and areas for growth</span>";
                                        break;
                                    case 2:
                                        $title = "<span style='font-weight: bold' title='A new challenge may be an unfamiliar activity, or an extension to an existing one.'>Undertaken new challenges</span>";
                                        break;
                                    case 3:
                                        $title = "<span style='font-weight: bold' title='Planning and initiation will often be in collaboration with others. It can be shown in activities that are part of larger projects, for example, ongoing school activities in the local community, as well as in small student-led activities.'>Planned and initiated activities</span>";
                                        break;
                                    case 4:
                                        $title = "<span style='font-weight: bold' title='Collaboration can be shown in many different activities, such as team sports, playing music in a band, or helping in a kindergarten. At least one project, involving collaboration and the integration of at least two of creativity, action and service, is required.'>Worked collaboratively with others</span>";
                                        break;
                                    case 5:
                                        $title = "<span style='font-weight: bold' title='At a minimum, this implies attending regularly and accepting a share of the responsibility for dealing with problems that arise in the course of activities.'>Shown perseverance and commitment in their activities</span>";
                                        break;
                                    case 6:
                                        $title = "<span style='font-weight: bold' title='Students may be involved in international projects but there are many global issues that can be acted upon locally or nationally (for example, environmental concerns, caring for the elderly).'>Engaged with issues of global importance</span>";
                                        break;
                                    case 7:
                                        $title = "<span style='font-weight: bold' title='Ethical decisions arise in almost any CAS activity (for example, on the sports field, in musical composition, in relationships with others involved in service activities). Evidence of thinking about ethical issues can be shown in various ways, including journal entries and conversations with CAS advisers.'>Considered the ethical implications of their actions</span>";
                                        break;
                                    case 8:
                                        $title = "<span style='font-weight: bold' title='As with new challenges, new skills may be shown in activities that the student has not previously undertaken, or in increased expertise in an established area.'>Developed new skills</span>";
                                        break;
                                }
                                
                                $prepopulate = '';
                                if ($valuesInterview["2_outcome".$i] != '') {
                                    $outcomeList = array();
                                    try {
                                        array_push($outcomeList, $valuesInterview['2_outcome'.$i]);
                                        $dataPrepopulate = ['outcomeList' => $valuesInterview['2_outcome'.$i]];
                                        $sqlPrepopulate = "SELECT ibDiplomaCASCommitmentID as value, name as name FROM ibDiplomaCASCommitment WHERE FIND_IN_SET(ibDiplomaCASCommitmentID, '".$dataPrepopulate['outcomeList']."')";
                                        $resultPrepopulate = $connection2->query($sqlPrepopulate);
                                    } catch (PDOException $e) {
                                        $page->addError($e->getMessage());
                                    }
                                    while ($valuesPrepopulate = $resultPrepopulate->fetch()) {
                                        $prepopulate = $pdo->select($sqlPrepopulate, $dataPrepopulate)->fetchKeyPair();
                                    }
                                }
                                
                                    $data = array('gibbonPersonID' => $gibbonPersonID);
                                    $sql = "SELECT name as name, ibDiplomaCASCommitmentID as value FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved'";
                                    $row = $form->addRow()->addClass('tags');
                                           $column = $row->addColumn();
                                        $column->addLabel('outcome'.$i, __('Outcome '.$i))
                                            ->description(__($title));
                                        $column->addFinder('outcome'.$i)
                                            ->fromQuery($pdo, $sql, $data)
                                            ->setParameter('hintText', __('Type the name of an approved commitment...'))
                                            ->setParameter('allowCreation', false)
                                            ->selected($prepopulate);
                            }
                            $form->addRow()->addHeading(__('Notes'));
                                $row = $form->addRow();
                                    $column = $row->addColumn();
                                        $column->addContent( __('Use this space to take notes on your conversation with the student. You may wish to consider:<i><ul><li>How is student progressing?</li><li>Are all outcomes begun?</li><li>Which outcomes require more thought and action?</li></ul></i>'));
                                        $column->addTextArea('notes')->setRows(15)->setValue($valuesInterview['2_notes'])->setClass('fullWidth');
                        
                            $form->addRow()->addHeading(__('General Information'));
                                $row = $form->addRow();
                                    $row->addLabel('interviewer', __('Interviewer'));
                                    $row->addTextField('interviewer')->setValue(formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', true, true))->readOnly()->isRequired();
                                $row = $form->addRow();
                                    $row->addLabel('date', __('Date'));
                                    $row->addDate('date')->setValue(dateConvertBack($guid, $valuesInterview['2_date']))->isRequired();
                                $row = $form->addRow();
                                    $row->addLabel('casStatusSchool', __('CAS Status'));
                                    $row->addSelect('casStatusSchool')->fromArray(array('At Risk' =>__('At Risk'), 'On Task' => __('On Task'), 'Excellence' =>__('Excellence')))->selected($casStatusSchool)->isRequired();
                        
                            
                            $row = $form->addRow();
                                $row->addFooter();
                                $row->addSubmit();
                            echo $form->getOutput();                        
                        }
                    }
                } elseif ($subpage == 'Interview 3') {
                    try {
                        $dataInterview = array('gibbonPersonID' => $gibbonPersonID);
                        $sqlInterview = 'SELECT ibDiplomaCASInterview.*, surname, preferredName FROM ibDiplomaCASInterview JOIN gibbonPerson ON (ibDiplomaCASInterview.1_gibbonPersonIDInterviewer=gibbonPerson.gibbonPersonID) WHERE gibbonPersonIDInterviewee=:gibbonPersonID';
                        $resultInterview = $connection2->prepare($sqlInterview);
                        $resultInterview->execute($dataInterview);
                    } catch (PDOException $e) {
                        $page->addError($e->getMessage());
                    }

                    if ($resultInterview->rowCount() > 1) {
                        $page->addError(__('Interview cannot be displayed.'));
                    } else {
                        if ($resultInterview->rowCount() == 0) {
                            $page->addError(__('You have not yet completed Interview 1, and so cannot access Interview 2.'));
                        } else {
                            $valuesInterview = $resultInterview->fetch();

                            if (is_null($valuesInterview['2_date'])) {
                                $page->addError(__('You have not yet completed Interview 2, and so cannot access Interview 3.'));
                            } else {
                                $form = Form::create('interview3', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/cas_adviseStudents_detailsInterview3Process.php");
                            
                                $form->addHiddenValue('gibbonPersonID', $gibbonPersonID);
                                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                            
                                $form->addRow()->addHeading(__('Outcomes'));
                                    $formRow = $form->addRow();
                                    $formRow->addContent( __('Work with the interviewee to determine which commitments you think <b>have</b> satisfied each of the outcomes listed below. The student should have pre-filled this information before Interview 3. Use the second box for each outcome to record notes from your discussion'));
                            
                                try {
                                    $dataList = array('gibbonPersonID' => $gibbonPersonID);
                                    $sqlList = "SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved' ORDER BY name";
                                    $resultList = $connection2->prepare($sqlList);
                                    $resultList->execute($dataList);
                                } catch (PDOException $e) {
                                    $page->addError($e->getMessage());
                                }

                                $list = '';
                                while ($valuesList = $resultList->fetch()) {
                                    $list .= '{id: "'.$valuesList['ibDiplomaCASCommitmentID'].'", name: "'.$valuesList['name'].'"},';
                                }
                                $list = substr($list, 0, -1);
                                for ($i = 1; $i < 9; ++$i) {
                                    switch ($i) {
                                        case 1:
                                            $title = "<span style='font-weight: bold' title='They are able to see themselves as individuals with various skills and abilities, some more developed than others, and understand that they can make choices about how they wish to move forward.'>Increased their awareness of their own strengths and areas for growth</span>";
                                            break;
                                        case 2:
                                            $title = "<span style='font-weight: bold' title='A new challenge may be an unfamiliar activity, or an extension to an existing one.'>Undertaken new challenges</span>";
                                            break;
                                        case 3:
                                            $title = "<span style='font-weight: bold' title='Planning and initiation will often be in collaboration with others. It can be shown in activities that are part of larger projects, for example, ongoing school activities in the local community, as well as in small student-led activities.'>Planned and initiated activities</span>";
                                            break;
                                        case 4:
                                            $title = "<span style='font-weight: bold' title='Collaboration can be shown in many different activities, such as team sports, playing music in a band, or helping in a kindergarten. At least one project, involving collaboration and the integration of at least two of creativity, action and service, is required.'>Worked collaboratively with others</span>";
                                            break;
                                        case 5:
                                            $title = "<span style='font-weight: bold' title='At a minimum, this implies attending regularly and accepting a share of the responsibility for dealing with problems that arise in the course of activities.'>Shown perseverance and commitment in their activities</span>";
                                            break;
                                        case 6:
                                            $title = "<span style='font-weight: bold' title='Students may be involved in international projects but there are many global issues that can be acted upon locally or nationally (for example, environmental concerns, caring for the elderly).'>Engaged with issues of global importance</span>";
                                            break;
                                        case 7:
                                            $title = "<span style='font-weight: bold' title='Ethical decisions arise in almost any CAS activity (for example, on the sports field, in musical composition, in relationships with others involved in service activities). Evidence of thinking about ethical issues can be shown in various ways, including journal entries and conversations with CAS advisers.'>Considered the ethical implications of their actions</span>";
                                            break;
                                        case 8:
                                            $title = "<span style='font-weight: bold' title='As with new challenges, new skills may be shown in activities that the student has not previously undertaken, or in increased expertise in an established area.'>Developed new skills</span>";
                                            break;
                                    }
                                
                                    $prepopulate = '';
                                    if ($valuesInterview["3_outcome".$i] != '') {
                                        echo "3_outcome".$i;
                                        $outcomeList = array();
                                        try {
                                            array_push($outcomeList, $valuesInterview['3_outcome'.$i]);
                                            $dataPrepopulate = ['outcomeList' => $valuesInterview['3_outcome'.$i]];
                                            $sqlPrepopulate = "SELECT ibDiplomaCASCommitmentID as value, name as name FROM ibDiplomaCASCommitment WHERE FIND_IN_SET(ibDiplomaCASCommitmentID, '".$dataPrepopulate['outcomeList']."')";
                                            $resultPrepopulate = $connection2->query($sqlPrepopulate);
                                        } catch (PDOException $e) {
                                            $page->addError($e->getMessage());
                                        }
                                        while ($valuesPrepopulate = $resultPrepopulate->fetch()) {
                                            $prepopulate = $pdo->select($sqlPrepopulate, $dataPrepopulate)->fetchKeyPair();
                                        }
                                    }
                                
                                        $data = array('gibbonPersonID' => $gibbonPersonID);
                                        $sql = "SELECT name as name, ibDiplomaCASCommitmentID as value FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND approval='Approved'";
                                        $row = $form->addRow()->addClass('tags');
                                            $column = $row->addColumn();
                                            $column->addLabel('outcome'.$i, __('Outcome '.$i))
                                                ->description(__($title));
                                            $column->addFinder('outcome'.$i)
                                                ->fromQuery($pdo, $sql, $data)
                                                ->setParameter('hintText', __('Type the name of an approved commitment...'))
                                                ->setParameter('allowCreation', false)
                                                ->selected($prepopulate);
                                            $column->addTextArea('outcome'.$i.'Notes')
                                                ->setRows(3)
                                                ->setValue($valuesInterview['3_outcome'.$i.'Notes'])
                                                ->setClass('fullWidth');
                                }
                                $form->addRow()->addHeading(__('Notes'));
                                    $row = $form->addRow();
                                        $column = $row->addColumn();
                                            $column->addContent( __('Use this space to take notes on your conversation with the student. You may wish to consider:<i><ul style="margin-bottom: 0px"><li>Are all outcomes satisfactorily completed?</li></ul></i><br/>'));
                                            $column->addTextArea('notes')->setRows(15)->setValue($valuesInterview['3_notes'])->setClass('fullWidth');
                        
                                $form->addRow()->addHeading(__('General Information'));
                                    $row = $form->addRow();
                                        $row->addLabel('interviewer', __('Interviewer'));
                                        $row->addTextField('interviewer')->setValue(formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', true, true))->readOnly()->isRequired();
                                    $row = $form->addRow();
                                        $row->addLabel('date', __('Date'));
                                        $row->addDate('date')->setValue(dateConvertBack($guid, $valuesInterview['3_date']))->isRequired();
                                    $row = $form->addRow();
                                        $row->addLabel('casStatusSchool', __('CAS Status'));
                                        $row->addSelect('casStatusSchool')->fromArray(array('Complete' =>__('Complete'), 'Incomplete' => __('Incomplete')))->selected($casStatusSchool)->isRequired();
                        
                            
                                $row = $form->addRow();
                                    $row->addFooter();
                                    $row->addSubmit();
                                echo $form->getOutput();
                                
                            

                            }
                        }
                    }
                }

                //Set sidebar
                $_SESSION[$guid]['sidebarExtra'] = getUserPhoto($guid, $image_240, 240);
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].'<br>';
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].'<ul>';
                $style = '';
                if ($subpage == 'Overview') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Overview'>Overview</a></li>";

                $style = '';
                if ($subpage == 'Reflection') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Reflection'>Reflection</a></li>";

                $style = '';
                if ($subpage == 'CAS Status') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=CAS Status'>CAS Status</a></li>";

                $style = '';
                if ($subpage == 'Interview 1') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Interview 1'>Interview 1</a></li>";

                $style = '';
                if ($subpage == 'Interview 2') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Interview 2'>Interview 2</a></li>";

                $style = '';
                if ($subpage == 'Interview 3') {
                    $style = "style='font-weight: bold'";
                }
                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra']."<li><a $style href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q']."&gibbonPersonID=$gibbonPersonID&subpage=Interview 3'>Interview 3</a></li>";

                $_SESSION[$guid]['sidebarExtra'] = $_SESSION[$guid]['sidebarExtra'].'</ul>';
            }
        }
    }
}
?>

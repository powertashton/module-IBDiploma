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

use Gibbon\Module\IBDiploma\Domain\CommitmentGateway;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\DataSet;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_approveCommitments.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffCASRole($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role == false) { $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {
    
        $page->breadcrumbs->add(__('Approve CAS Commitments'));
        
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }
    }
    $gibbonSchoolYearID = $gibbon->session->get('gibbonSchoolYearID');
    $gibbonSchoolYearSequenceNumber = $gibbon->session->get('gibbonSchoolYearSequenceNumber');
    $gibbonPersonID = $gibbon->session->get('gibbonPersonID');
    
    $CommitmentGateway = $container->get(CommitmentGateway::class);
    $criteria = $CommitmentGateway->newQueryCriteria()->fromPOST();
    $commitment = $CommitmentGateway->queryCommitments($criteria, $gibbonSchoolYearID, $gibbonSchoolYearSequenceNumber, $gibbonPersonID);
    
    $userGateway = $container->get(UserGateway::class);
    
    $table = DataTable::createPaginated('CASStaff', $criteria);
    $table->addHeaderAction('add', __('Add Staff'))
            ->setURL('/modules/' . $gibbon->session->get('module') . '/staff_manage_add.php')
            ->displayLabel();
    $table->addColumn('name', __('Commitment')) 
                ->description(__('Student'))
                ->format(function ($row) use ($userGateway) {
                    $student = $userGateway->getByID($row['gibbonPersonID']);
                    
                    return $row['name'] . '<br/>'. Format::small(__(Format::name($student['title'], $student['preferredName'], $student['surname'], 'Student')));
                });
    $table->addColumn('approval', __('Approval'));
    $table->addActionColumn()
            ->addParam('gibbonPersonID')
            ->addParam('ibDiplomaCASCommitmentID')
            ->format(function ($row, $actions) use ($gibbon) {
                $actions->addAction('view', __('View'))
                        ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_adviseStudents_full.php');
            });

    echo $table->render($commitment);
    //todo: this
          // echo "<tr class=$rowNum>";
//                 echo '<td>';
//                 echo formatName('', $row['preferredName'], $row['surname'], 'Student', true, true);
//                 echo '</td>';
//                 echo '<td>';
//                 echo $row['name'];
//                 echo '</td>';
//                 echo '<td>';
//                 if ($row['approval'] == 'Pending' or $row['approval'] == 'Not Approved') {
//                     echo $row['approval'];
//                 } else {
//                     echo $row['status'];
//                 }
//                 echo '</td>';
//                 echo '<td>';
//                 echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/cas_adviseStudents_full.php&gibbonPersonID='.$row['gibbonPersonID'].'&ibDiplomaCASCommitmentID='.$row['ibDiplomaCASCommitmentID']."&width=1000&height=550'><img title='View' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_right.png'/></a> ";
//                 echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cas_approveCommitmentsProcess.php?address='.$_GET['q'].'&job=approve&ibDiplomaCASCommitmentID='.$row['ibDiplomaCASCommitmentID']."'><img title='Approve' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick.png'/></a> ";
//                 echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cas_approveCommitmentsProcess.php?address='.$_GET['q'].'&job=reject&ibDiplomaCASCommitmentID='.$row['ibDiplomaCASCommitmentID']."'><img title='Reject' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconCross.png'/></a> ";
//                 echo '</td>';
//                 echo '</tr>';
//             }
//             echo '</table>';
}

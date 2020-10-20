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
    $gibbonPersonIDCASAdvisor = NULL;
     if ($role != 'Coordinator') {
        $gibbonPersonIDCASAdvisor = $gibbon->session->get('gibbonPersonID');
     }
    
    $approval = 'Pending';
    
    $CommitmentGateway = $container->get(CommitmentGateway::class);
    
    $criteria = $CommitmentGateway
        ->newQueryCriteria()
        ->filterBy('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->filterBy('gibbonSchoolYearSequenceNumber', $gibbonSchoolYearSequenceNumber)
        ->filterBy('approval', $approval)
        ->filterBy('gibbonPersonIDCASAdvisor', $gibbonPersonIDCASAdvisor)
        ->fromPOST();
    
    $commitment = $CommitmentGateway->queryCommitments($criteria);
    
    $userGateway = $container->get(UserGateway::class);
   
    
    $table = DataTable::createPaginated('Commitments', $criteria);
    $table->addColumn('name', __('Commitment')) 
                ->description(__('Student'))
                ->format(function ($row) use ($userGateway) {
                    $student = $userGateway->getByID($row['gibbonPersonID']);
                    
                    return $row['name'] . '<br/>'. Format::small(__(Format::name($student['title'], $student['preferredName'], $student['surname'], 'Student')));
                });
    $table->addColumn('approval', __('Approval'));
    $table->addActionColumn()
            ->addParam('ibDiplomaCASCommitmentID')
            ->format(function ($row, $actions) use ($gibbon) {
                $actions->addAction('view', __('View'))
                        ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_adviseStudents_full.php')
                        ->modalWindow()
                        ->addParam('gibbonPersonID',$row['gibbonPersonID']);
                        
                $actions->addAction('approve', __('Approve'))
                        ->directLink()
                        ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_approveCommitmentsProcess.php')
                        ->addParam('job', 'approve')
                        ->setIcon('iconTick');
                 $actions->addAction('reject', __('Reject'))
                        ->directLink()
                        ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_approveCommitmentsProcess.php')
                        ->addParam('job', 'reject')
                        ->setIcon('iconCross');
            });

    echo $table->render($commitment);
}

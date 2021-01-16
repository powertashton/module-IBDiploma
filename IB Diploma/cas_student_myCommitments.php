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
use Gibbon\Services\Format;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\DataSet;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_myCommitments.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {
        $page->breadcrumbs
            ->add(__('My Commitments'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }
        
        $CommitmentGateway = $container->get(CommitmentGateway::class);
        $gibbonPersonID = $gibbon->session->get('gibbonPersonID');
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
            
            
            
        $table->addActionColumn()
                ->addParam('ibDiplomaCASCommitmentID')
                ->format(function ($row, $actions) use ($gibbon) {
                    $actions->addAction('view', __('View'))
                        ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_student_myCommitments_view.php')
                        ->modalWindow();
                    if ($row['approval'] == 'Approved') {
                    $actions->addAction('edit', __('Edit'))
                            ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_student_myCommitments_edit.php');
                    }
                    $actions->addAction('delete', __('Delete'))
                            ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_student_myCommitments_delete.php');
                        
                });

        echo $table->render($commitment);
        
    }
}

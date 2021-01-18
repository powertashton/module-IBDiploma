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

use Gibbon\Tables\DataTable;
use Gibbon\Module\IBDiploma\Domain\ReflectionGateway;
use Gibbon\Services\Format;


//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_myCommitments_view.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this page.'));
} else {
    if (enroled($guid, $gibbon->session->get('gibbonPersonID', $connection2) == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {
        //Proceed!
                       //Get class variable
        $ibDiplomaCASCommitmentID = $_GET['ibDiplomaCASCommitmentID'];
        if ($ibDiplomaCASCommitmentID == '') {
            $page->addError(__('Commitment has not been specified.'));
        }
        //Check existence of and access to this commitment.
        else {
            try {
                $data = array('gibbonPersonID' => $gibbon->session->get('gibbonPersonID'), 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                $sql = 'SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError($e->getMessage());
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('Commitment does not exist or you do not have access to it.'));
            } else {
                $values = $result->fetch();

                $table = DataTable::createDetails('details');
                $table->setTitle($values['name']);
                
                $table->addColumn('approval', __('Approval'));
                $table->addColumn('status', __('Status'));
                $table->addColumn('dateStart', __('Start Date'))
                    ->format(function ($row) use ($guid) {
                    return dateConvertBack($guid, $row['dateStart']);
                    });
                $table->addColumn('dateEnd', __('End Date'))
                    ->format(function ($row) use ($guid) {
                    return dateConvertBack($guid, $row['dateEnd']);
                    });
                $table->addColumn('supervisorName', __('Supervisor'));
                $table->addColumn('supervisorPhone', __('Phone'));    
                $table->addColumn('supervisorEmail', __('Email'))->addClass('col-span-2'); 
                if ($values['description'] != '') {
                    $table->addColumn('description', __('Description'))->addClass('col-span-4');
                }
                if ($values['goals'] != '') {
                    $table->addColumn('goals', __('Goals'))->addClass('col-span-4');
                }
                $table->addMetaData('gridClass', 'grid-cols-4'); 
                echo $table->render([$values]);
                
                //TODO: TURN THIS INTO NICE GATEWAY + DISCUSSION TWIG
                $dataFeedback = array('ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                $sqlFeedback = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='Y'";
                $resultFeedback = $connection2->prepare($sqlFeedback);
                $resultFeedback->execute($dataFeedback);

                if ($resultFeedback->rowCount() == 1) {
                    $valuesFeedback = $resultFeedback->fetch();
                    
                    $table = DataTable::createDetails('feedback');
                    $table->setTitle(__('Feedback'));
                    $table->addColumn('evaluation', __('Evaluation'))->addClass('col-span-3');
                    $table->addColumn('attendance', __('Attendance'))->addClass('col-span-3');

                }

                //TODO: USE DISCUSSION TWIG FOR THIS
                $ReflectionGateway = $container->get(ReflectionGateway::class);
                $criteria = $ReflectionGateway
                    ->newQueryCriteria()
                    ->filterBy('ibDiplomaCASCommitmentID', $ibDiplomaCASCommitmentID)
                    ->fromPOST();
                $reflection = $ReflectionGateway->queryCASReflection($criteria, $gibbon->session->get('gibbonPersonID'));

                $table = DataTable::create('reflections');
                $table->setTitle(__('Reflections'));
                $table->addColumn('title', __('Title'))
                  ->description(__('Date'))
                  ->format(function ($row) use ($guid) {
                    return Format::bold($row['title']) . '<br/>' . Format::small(dateConvertBack($guid, substr($row['timestamp'], 0, 10)));
                  });

                $table->addColumn('reflection', __('Reflection'));  

                echo $table->render($reflection);
                
            }
        }
    }
}

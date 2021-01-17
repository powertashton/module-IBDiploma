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

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_reflections.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {
        $page->breadcrumbs
            ->add(__('Reflections'));
            
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        try {
            $data = array('gibbonPersonID' => $gibbon->session->get('gibbonPersonID'));
            $sql = 'SELECT * FROM ibDiplomaCASReflection WHERE gibbonPersonID=:gibbonPersonID ORDER BY timestamp';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }
        
        //TODO: FILTER
        $ReflectionGateway = $container->get(ReflectionGateway::class);
        $criteria = $ReflectionGateway
            ->newQueryCriteria()
            ->fromPOST();
        $reflection = $ReflectionGateway->queryCASReflection($criteria, $gibbon->session->get('gibbonPersonID'));

        $table = DataTable::create('reflections');
        $table->addHeaderAction('add', __('New'))
                ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_student_reflections_add.php');
    
        $table->addExpandableColumn('reflection');
        $table->addColumn('commitment', __('Commitment'))
                ->format(function ($row) use ($connection2) {
                    if (is_null($row['ibDiplomaCASCommitmentID'])) {
                        return '<b><i>General CAS</i></b>';
                    } else { //TODO: MOVE INTO GATEWAY
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
    
        $table->addActionColumn()
                    ->addParam('ibDiplomaCASReflectionID')
                    ->format(function ($row, $actions) use ($gibbon) {
                        $actions->addAction('delete', __('Delete'))
                            ->setURL('/modules/' . $gibbon->session->get('module') . '/cas_student_reflections_delete.php')
                            ->modalWindow();
                    }); 
    
        echo $table->render($reflection);
        
    }
}
?>

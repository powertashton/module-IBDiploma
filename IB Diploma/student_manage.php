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

use Gibbon\Module\IBDiploma\Domain\CASStudentGateway;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\DataSet;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/student_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
        $page->breadcrumbs
        ->add(__('Manage Student Enrolment'));
    
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    
            
        $CASStudentGateway = $container->get(CASStudentGateway::class);
        $userGateway = $container->get(UserGateway::class);
        $gibbonSchoolYearID = $gibbon->session->get('gibbonSchoolYearID');
        $gibbonSchoolYearSequenceNumber = $gibbon->session->get('gibbonSchoolYearSequenceNumber');
        $gibbonPersonID = $gibbon->session->get('gibbonPersonID');
        
        $gibbonRollGroupID = $_GET['gibbonRollGroupID'] ?? NULL;
        $criteria = $CASStudentGateway
            ->newQueryCriteria()
            ->searchBy($CASStudentGateway->getSearchableColumns(), $_GET['search'] ?? '')
            ->filterBy('gibbonRollGroupID', $gibbonRollGroupID)
            ->fromPOST();

    
        $students = $CASStudentGateway->queryCASStudents($criteria, $gibbonSchoolYearID, $gibbonSchoolYearSequenceNumber, $gibbonPersonID);
        
        $form = Form::create('searchForm', $gibbon->session->get('absoluteURL') . '/index.php', 'get');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('q', '/modules/' . $gibbon->session->get('module') . '/cas_adviseStudents.php');
        $form->addHiddenValue('address', $gibbon->session->get('address'));

        $form->setClass('noIntBorder fullWidth standardForm');
        $form->setTitle(__('Search & Filter'));

        $row = $form->addRow();
            $row->addLabel('search', __('Search'))
                ->description(__('Student Name'));
            $row->addTextField('search')
                ->setValue($criteria->getSearchText());
    
        $row = $form->addRow();
            $row->addLabel('gibbonRollGroupID', __('Roll Group'));
            $row->addSelectRollGroup('gibbonRollGroupID', $gibbon->session->get('gibbonSchoolYearID'))->selected($gibbonRollGroupID)->placeholder();
    
        $row = $form->addRow();
            $row->addSearchSubmit($gibbon->session, __('Clear Filters'));

        echo $form->getOutput();    
        
        
        $table = DataTable::createPaginated('CASStudents', $criteria);
        $table->setTitle('Students');
        $table->setDescription(__m('This page only displays students enroled in the current school year.'));
        $table->addHeaderAction('add', __('Add Students'))
            ->setURL('/modules/' . $gibbon->session->get('module') . '/student_manage_add.php')
            ->displayLabel();
        
        $table->addColumn('gibbonPersonID', __('Student')) 
                ->description(__('CAS Advisor'))
                ->format(function ($row) use ($userGateway) {
                    $student = $userGateway->getByID($row['gibbonPersonID']);
                    $advisor = $userGateway->getByID($row['gibbonPersonIDCASAdvisor']);
                    
                    return Format::name($student['title'], $student['preferredName'], $student['surname'], 'Student') . '<br/>'. Format::small(__(Format::name($advisor['title'], $advisor['preferredName'], $advisor['surname'], 'Staff')));
                });
        $table->addColumn('rollGroup', __('Roll Group'));
        $table->addColumn('start', __('Start'));
        $table->addColumn('end', __('End'));
        $table->addActionColumn()
            ->addParam('ibDiplomaStudentID')
            ->format(function ($row, $actions) use ($gibbon) {
                $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/' . $gibbon->session->get('module') . '/student_manage_edit.php');
                 $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/' . $gibbon->session->get('module') . '/student_manage_delete.php');
            });

    
        echo $table->render($students);  
}

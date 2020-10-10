<?php
namespace Gibbon\Module\IBDiploma\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * Technician Gateway
 *
 * @version v20
 * @since   v20
 */
class CASStudentGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'ibDiplomaStudent';
    private static $primaryKey = 'ibDiplomaStudentID';
    private static $searchableColumns = ['surname', 'preferredName'];

    
    public function queryCASStudents($criteria, $gibbonSchoolYearID, $gibbonSchoolYearSequenceNumber, $gibbonPersonID) {      
        $query = $this
            ->newQuery()
            ->from('ibDiplomaStudent')
            ->cols(['gibbonPerson.gibbonPersonID', 'ibDiplomaStudentID', 'surname', 'preferredName', 'start.name AS start', 'end.name AS end', 'gibbonYearGroup.nameShort AS yearGroup', 'gibbonRollGroup.nameShort AS rollGroup', 'gibbonRollGroup.gibbonRollGroupID', 'gibbonPersonIDCASAdvisor', 'casStatusSchool'])
            ->leftjoin('gibbonPerson', 'ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID' )
            ->leftjoin('gibbonStudentEnrolment', 'ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID')
            ->leftJoin('gibbonSchoolYear AS start', 'start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart')
            ->leftJoin('gibbonSchoolYear AS end','end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd')
            ->leftJoin('gibbonYearGroup','gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonRollGroup', 'gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID')
            ->where('gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID ')->bindvalue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->where('gibbonPerson.status="Full"')
            ->where('start.sequenceNumber<=:sequenceStart')->bindvalue('sequenceStart', $gibbonSchoolYearSequenceNumber)
            ->where('end.sequenceNumber>=:sequenceEnd')->bindvalue('sequenceEnd', $gibbonSchoolYearSequenceNumber);

        $criteria->addFilterRules([
            'gibbonRollGroupID' => function ($query, $gibbonRollGroupID) {
                return $query
                    ->where('gibbonRollGroup.gibbonRollGroupID=:gibbonRollGroupID')
                    ->bindValue('gibbonRollGroupID', $gibbonRollGroupID);
            }
        ]);

       return $this->runQuery($query, $criteria);
    }
}
        

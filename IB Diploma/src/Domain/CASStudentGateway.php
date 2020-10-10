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
    
    public function selectCASStudents($gibbonSchoolYearID, $gibbonSchoolYearSequenceNumber ) {
        $data = array('gibbonSchoolYearID' =>$gibbonSchoolYearID, 'sequenceStart' => $gibbonSchoolYearSequenceNumber, 'sequenceEnd' => $gibbonSchoolYearSequenceNumber);
        $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd ORDER BY rollGroup, surname, preferredName";
        return $this->db()->select($sql, $data);
    }
    
    public function selectCASStudentsByAdvisor($gibbonSchoolYearID, $gibbonSchoolYearSequenceNumber, $gibbonerPersonID) {
        $data = array('gibbonSchoolYearID' =>$gibbonSchoolYearID, 'sequenceStart' => $gibbonSchoolYearSequenceNumber, 'sequenceEnd' => $gibbonSchoolYearSequenceNumber, 'advisor' => $gibbonPersonID);
        $sql = "SELECT gibbonPerson.gibbonPersonID, ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDCASAdvisor, casStatusSchool FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND start.sequenceNumber<=:sequenceStart AND end.sequenceNumber>=:sequenceEnd AND gibbonPersonIDCASAdvisor=:advisor ORDER BY rollGroup, surname, preferredName";
        return $this->db()->select($sql, $data);
    }

    
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
        

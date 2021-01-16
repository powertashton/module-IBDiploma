<?php

namespace Gibbon\Module\IBDiploma\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * Commitment Gateway
 *
 * @version v21
 * @since   v21
 */
class CommitmentGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'ibDiplomaCASCommitment';
    private static $primaryKey = 'ibDiplomaCASCommitmentID';
    private static $searchableColumns = [];

    public function queryCommitments($criteria, $gibbonSchoolYearID, $gibbonSchoolYearSequenceNumber, $gibbonPersonID) {      
        $query = $this
            ->newQuery()
            ->from('ibDiplomaStudent')
            ->cols(['ibDiplomaCASCommitment.*', 'gibbonPerson.gibbonPersonID', 'gibbonStudentEnrolment.gibbonYearGroupID', 'gibbonStudentEnrolment.gibbonRollGroupID', 'ibDiplomaStudentID', 'surname', 'preferredName', 'start.name AS start', 'end.name AS end', 'gibbonYearGroup.nameShort AS yearGroup', 'gibbonRollGroup.nameShort AS rollGroup', 'gibbonPersonIDCASAdvisor', 'casStatusSchool'])
            ->leftjoin('gibbonPerson', 'ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID' )
            ->leftjoin('gibbonStudentEnrolment', 'ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID')
            ->leftJoin('gibbonSchoolYear AS start', 'start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart')
            ->leftJoin('gibbonSchoolYear AS end','end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd')
            ->leftJoin('gibbonYearGroup','gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonRollGroup', 'gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID')
            ->innerJoin('ibDiplomaCASCommitment', 'ibDiplomaCASCommitment.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID ')->bindvalue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->where('gibbonPerson.status="Full"')
            ->where('start.sequenceNumber<=:sequenceStart')->bindvalue('sequenceStart', $gibbonSchoolYearSequenceNumber)
            ->where('end.sequenceNumber>=:sequenceEnd')->bindvalue('sequenceEnd', $gibbonSchoolYearSequenceNumber)
            ->where('approval="Pending"');

       return $this->runQuery($query, $criteria);
    }

    
}

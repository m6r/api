<?php

namespace App\Services;

use Doctrine\DBAL\Driver\Connection;

class Statistics
{
    const SIGNATURES_TOTAL = 1;

    const SIGNATURES_WORDPRESS = 2;

    const SIGNATURES_PLIGG = 3;

    const SIGNATURES_DUPLICATES = 4;

    /**
     * The database connection.
     * @var DriverConnection
     */
    private $db;

    /**
     * Name of the users pligg table.
     * @var string
     */
    private $pliggTable;

    /**
     * The dk_speakup table name on wordpress.
     * @var string
     */
    private $wpTable;

    /**
     * The petition id of the dk_speakup
     * @var string
     */
    private $wpPetitionID;

    /**
     * Instantiate the service.
     * @param Connection $db           The DBAL database connection.
     * @param string     $pliggTable   The pligg table.
     * @param string     $wpTable      The wordpress dk_speakup_table.
     * @param string     $wpPetitionID The petition id we want to count.
     */
    public function __construct(Connection $db, $pliggTable, $wpTable, $wpPetitionID)
    {
        $this->db = $db;
        $this->pliggTable = $pliggTable;
        $this->wpTable = $wpTable;
        $this->wpPetitionID = $wpPetitionID;

        return;
    }

    /**
     * Le nombre de signatures total sans les doublons.
     * @param  integer $scope Nombre total, de dupliquées, de WP ou de pligg ?
     * @return integer Le nombre de signatures totales.
     */
    public function getSignatures($scope = SIGNATURES_TOTAL)
    {
        switch ($scope) {
            case self::SIGNATURES_TOTAL:
                return $this->getTotalSignatures();
            case self::SIGNATURES_PLIGG:
                return $this->getPliggSignatures();
            case self::SIGNATURES_WORDPRESS:
                return $this->getWordpressSignatures();
            case self::SIGNATURES_DUPLICATES:
                return $this->getDuplicatesSignatures();
            default:
                return;
        }
    }

    /**
     * L'historique des signatures.
     * @param  integer $scope Nombre total, de dupliquées, de WP ou de pligg ?
     * @return array   Un tableau associant des dates et des informations.
     */
    public function getSignaturesHistory($scope = SIGNATURES_TOTAL)
    {
        switch ($scope) {
            case self::SIGNATURES_TOTAL:
                return $this->getTotalSignaturesHistory();
            case self::SIGNATURES_PLIGG:
                return $this->getPliggSignaturesHistory();
            case self::SIGNATURES_WORDPRESS:
                return $this->getWordpressSignaturesHistory();
            case self::SIGNATURES_DUPLICATES:
                return $this->getDuplicatesSignaturesHistory();
            default:
                return;
        }
    }

    /**
     * Combien d'utilisateurs ont signé sur wordpress et pligg.
     * @return integer Le nombre de signatures.
     */
    private function getDuplicatesSignatures()
    {
        $pliggTable = $this->pliggTable;
        $wpTable = $this->wpTable;
        $wpPetitionID = $this->wpPetitionID;

        $sql = 'SELECT COUNT(user_id) FROM '.$pliggTable.' '.
            'INNER JOIN '.$wpTable.' '.
            'ON '.$pliggTable.'.user_email='.$wpTable.'.email '.
            'WHERE '.$wpTable.'.petitions_id = ?';
        $duplicates = $this->db->fetchColumn($sql, array($wpPetitionID));

        return $duplicates;
    }

    /**
     * Récupérer le nombre de nouvelles signatures dupliquées par jour.
     * @return array Un tableau associants date et informations.
     */
    private function getDuplicatesSignaturesHistory()
    {
        $pliggTable = $this->pliggTable;
        $wpTable = $this->wpTable;
        $wpPetitionID = $this->wpPetitionID;

        $sql =
            'SELECT '.
                'CASE '.
                    'WHEN DATE(date) >= DATE(user_date) THEN DATE(date) '.
                    'WHEN DATE(date) < DATE(user_date) THEN DATE(user_date) '.
                'END AS date, COUNT(user_id) as new_signatures '.
            'FROM '.$pliggTable.' '.
            'INNER JOIN '.$wpTable.' '.
            'ON '.$pliggTable.'.user_email='.$wpTable.'.email '.
            'WHERE '.$wpTable.'.petitions_id = ? '.
            'GROUP BY date';
        $duplicates = $this->db->fetchAll($sql, array($wpPetitionID));

        return $duplicates;
    }

    /**
     * Combien d'utilisateurs se sont inscrits et ont signé sur pligg.
     * @return integer Le nombre d'inscrits.
     */
    private function getPliggSignatures()
    {
        $pliggTable = $this->pliggTable;
        $sql = 'SELECT COUNT(user_id) FROM '.$pliggTable;
        $pliggUsers = $this->db->fetchColumn($sql);

        return $pliggUsers;
    }

    /**
     * L'historique des signatures sur pligg.
     * @return integer Le nombre d'adresse en par jour sur pligg.
     */
    private function getPliggSignaturesHistory()
    {
        $pliggTable = $this->pliggTable;
        $sql = 'SELECT DATE(user_date) as date, COUNT(user_id) as new_signatures '.
            'FROM '.$pliggTable.' '.
            'GROUP BY DATE(date)';
        $pliggSignatures = $this->db->fetchAll($sql);

        return $pliggSignatures;
    }

    /**
     * Récupérer le nombre total de signatures moins les doublons.
     * @return integer Le nombre.
     */
    private function getTotalSignatures()
    {
        $pliggTable = $this->pliggTable;
        $wpTable = $this->wpTable;
        $wpPetitionID = $this->wpPetitionID;

        $sql =
            'SELECT COUNT(DISTINCT email) FROM ('.
                'SELECT user_email AS email '.
                'FROM '.$pliggTable.' '.
                'UNION '.
                'SELECT email '.
                'FROM '.$wpTable.' '.
                'WHERE '.$wpTable.'.petitions_id = ? '.
            ') AS signatures';

        $total = $this->db->fetchColumn($sql, array($wpPetitionID));

        return $total;
    }

    /**
     * Récupérer le nombre de nouvelles signatures par jours sans les doublons.
     * @return array Un tableau associant dates et données.
     */
    private function getTotalSignaturesHistory()
    {
        $pliggTable = $this->pliggTable;
        $wpTable = $this->wpTable;
        $wpPetitionID = $this->wpPetitionID;

        $sql =
            'SELECT signature_date as date, COUNT(email) AS new_signatures '.
            'FROM ('.
                'SELECT MIN(signature_date) AS signature_date, email FROM ('.
                    'SELECT user_email AS email, DATE(user_date) AS signature_date '.
                    'FROM '.$pliggTable.' '.
                    'UNION '.
                    'SELECT email, DATE(date) '.
                    'FROM '.$wpTable.' '.
                    'WHERE '.$wpTable.'.petitions_id = ? '.
                ') AS signatures '.
                'GROUP BY email '.
            ') AS unique_signatures '.
            'GROUP BY unique_signatures.signature_date';

        $total = $this->db->fetchAll($sql, array($wpPetitionID));

        return $total;
    }

    /**
     * Le nombre d'adresse email inscrites sur WP.
     * @return integer Le nombre d'adresse sur WP.
     */
    private function getWordpressSignatures()
    {
        $wpTable = $this->wpTable;
        $wpPetitionID = $this->wpPetitionID;
        $sql = 'SELECT COUNT(id) FROM '.$wpTable.' '.
            'WHERE petitions_id = ?';
        $wpSignatures = $this->db->fetchColumn($sql, array($wpPetitionID));

        return $wpSignatures;
    }

    /**
     * L'historique des signatures sur wordpress.
     * @return integer Le nombre d'adresse par jour sur WP.
     */
    private function getWordpressSignaturesHistory()
    {
        $wpTable = $this->wpTable;
        $wpPetitionID = $this->wpPetitionID;
        $sql = 'SELECT DATE(date) as date, COUNT(id) as new_signatures '.
            'FROM '.$wpTable.' '.
            'WHERE petitions_id = ? '.
            'GROUP BY DATE(date)';
        $wpSignatures = $this->db->fetchAll($sql, array($wpPetitionID));

        return $wpSignatures;
    }
}

<?php

namespace App\Services;

use Doctrine\DBAL\Driver\Connection;

class Statistics
{
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
     * Combien d'utilisateurs ont signé sur wordpress et pligg.
     * @return integer Le nombre de signatures.
     */
    public function getDoubleSignatures()
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
     * Combien d'utilisateurs se sont inscrits et ont signé sur pligg.
     * @return integer Le nombre d'inscrits.
     */
    public function getPliggSignatures()
    {
        $pliggTable = $this->pliggTable;
        $sql = 'SELECT COUNT(user_id) FROM '.$pliggTable;
        $pliggUsers = $this->db->fetchColumn($sql);

        return $pliggUsers;
    }

    /**
     * Le nombre de signatures total sans les doublons.
     * @return integer Le nombre de signatures totales.
     */
    public function getSignatures()
    {
        return $this->getPliggSignatures() + $this->getWordpressSignatures() - $this->getDoubleSignatures();
    }

    /**
     * Le nombre d'adresse email inscrites sur les deux plateformes.
     * @return integer Le nombre d'adresse en double.
     */
    public function getWordpressSignatures()
    {
        $wpTable = $this->wpTable;
        $wpPetitionID = $this->wpPetitionID;
        $sql = 'SELECT COUNT(id) FROM '.$wpTable.' '.
            'WHERE petitions_id = ?';
        $wpSignatures = $this->db->fetchColumn($sql, array($wpPetitionID));

        return $wpSignatures;
    }
}

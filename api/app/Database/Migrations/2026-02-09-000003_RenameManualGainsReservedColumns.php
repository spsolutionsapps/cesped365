<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Renombra columnas 'year' y 'month' (palabras reservadas en MariaDB/MySQL)
 * a gain_year y gain_month para evitar errores de sintaxis en INSERT/SELECT.
 */
class RenameManualGainsReservedColumns extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('manual_gains')) {
            return;
        }
        $driver = $this->db->DBDriver;
        if (strpos(strtolower($driver), 'mysql') !== false) {
            $this->db->query('ALTER TABLE `manual_gains` CHANGE COLUMN `year` gain_year SMALLINT(4) UNSIGNED NOT NULL');
            $this->db->query('ALTER TABLE `manual_gains` CHANGE COLUMN `month` gain_month TINYINT(2) UNSIGNED NOT NULL');
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('manual_gains')) {
            return;
        }
        $driver = $this->db->DBDriver;
        if (strpos(strtolower($driver), 'mysql') !== false) {
            $this->db->query('ALTER TABLE `manual_gains` CHANGE COLUMN gain_year `year` SMALLINT(4) UNSIGNED NOT NULL');
            $this->db->query('ALTER TABLE `manual_gains` CHANGE COLUMN gain_month `month` TINYINT(2) UNSIGNED NOT NULL');
        }
    }
}

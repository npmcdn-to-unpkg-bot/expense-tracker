<?php
namespace Spendings\Transaction;

use Core\DI;

class Storage
{

    /** @var \PDO $dbh */
    private $dbh;

    public function __construct()
    {
        $this->dbh = DI::get('mysql');
    }

    public function getList(Array $options = [])
    {
        $sql_string = "
          SELECT
           t.dt,
           t.price,
           t.quantity,
           s.name,
           a.name
          FROM transactions AS t
          LEFT JOIN receipts AS r
          ON t.receipt_id = r.id
          LEFT JOIN shops AS s
          ON r.shop_id = s.id
          LEFT JOIN accounts AS a
          ON a.id = r.account_id
          LIMIT 300
        ";
        $query = $this->dbh->prepare($sql_string);
        $query->execute();
        while ($r = $query->fetch()) {

        }
    }

}
<?php

namespace Core;

class Controller
{

    public function __construct()
    {
        /** @var \Core\View $view */
        $view = DI::get('view');

        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');

        if (isset($_COOKIE['receipt_id']) && (int)$_COOKIE['receipt_id'] > 0) {
            $receiptId = (int)$_COOKIE['receipt_id'];
            $query = $dbh->prepare("
                SELECT 
                  s.name AS shopName,
                  a.id AS accountId,
                  a.name AS accountName,
                  b.dt AS `date`
                FROM baskets AS b
                INNER JOIN shops AS s
                ON b.shop_id = s.id
                INNER JOIN accounts AS a 
                ON a.id = b.account_id
                WHERE b.id = :id
            ");
            $query->bindValue('id', $receiptId, \PDO::PARAM_INT);
            $query->execute();
            $r = $query->fetch(\PDO::FETCH_ASSOC);
            $receiptData = [
                'id' => $receiptId,
                'shopName' => $r['shopName'],
                'accountId' => $r['accountId'],
                'accountName' => $r['accountName'],
                'date' => $r['date']
            ];
            $view->set_var('openReceipt', true);
            $view->set_var('receipt', $receiptData);
            $view->set_var('selectedAccountId', $receiptData['accountId']);

            $today = [
                'day' => substr($receiptData['date'], 8, 2),
                'month' => substr($receiptData['date'], 5, 2),
                'year' => substr($receiptData['date'], 0, 4)
            ];
        } else {
            $view->set_var('selectedAccountId', 2);
            $view->set_var('openReceipt', false);
            $today = [
                'day' => date('d'),
                'month' => date('m'),
                'year' => date('Y')
            ];
        }
        $view->set_var('today', $today);
    }

}
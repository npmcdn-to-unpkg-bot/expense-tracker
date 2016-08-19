<?php

namespace Controller;

use Core\Controller;
use Core\DI;

class Income extends Controller
{

    public function IndexAction()
    {

        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');

        /** @var \Core\View $view */
        $view = DI::get('view');

        $breadcrumbs = [
            [
                'href' => '/',
                'name' => 'Dashboard'
            ]
        ];

        $breadcrumbs[] = [
            'href' => '/income',
            'name' => 'Income'
        ];

        $q = $dbh->prepare("
            SELECT
              t.price / 100 as price,
              t.dt,
              a_from.name AS from_name,
              a_to.name AS to_name
            FROM transactions AS t
            INNER JOIN accounts AS a_from
              ON t.from_account = a_from.id
            INNER JOIN accounts AS a_to
              ON t.to_account = a_to.id
            WHERE a_from.is_external = 1
            AND a_from.id <> 0
            ORDER BY dt DESC");
        $q->execute();
        $transactions = [];

        while ($r = $q->fetch()) {
            $transactions[] = $r;
        }
        $accounts_int = [];
        $accounts_ext = [];
        $q = $dbh->prepare("
              SELECT *
              FROM accounts
              WHERE id <> 0
            ");
        $q->execute();
        while ($r = $q->fetch()) {
            if ($r['is_external'] == 0) {
                $accounts_int[] = [
                    'id' => $r['id'],
                    'name' => $r['name']
                ];
            } else {
                $accounts_ext[] = [
                    'id' => $r['id'],
                    'name' => $r['name']
                ];
            }
        }

        $view->set_var('accounts_int', $accounts_int);
        $view->set_var('accounts_ext', $accounts_ext);

        $view->set_var('breadcrumbs', $breadcrumbs);
        $view->set_var('transactions', $transactions);

    }


    public function AddAction()
    {

        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');

        /** @var \Core\View $view */
        $view = DI::get('view');

        $day   = (int)$_POST['day']   < 10 ? '0' . $_POST['day']   : $_POST['day'];
        $month = (int)$_POST['month'] < 10 ? '0' . $_POST['month'] : $_POST['month'];
        $year  = (int)$_POST['year']  < 10 ? '0' . $_POST['year']  : $_POST['year'];
        $date = $year . '-' . $month . '-' . $day . ' 00:00:00';
        $q = $dbh->prepare("INSERT INTO transactions
                        SET dt = :date,
                            price = :price,
                            catID = '0',
                            quantity = '0',
                            from_account = :from_account,
                            to_account = :to_account,
                            type = 1");
        $q->bindValue(':date', $date);
        $q->bindValue(':price', (int)$_POST['amount'] * 100);
        $q->bindValue(':from_account', $_POST['from_account']);
        $q->bindValue(':to_account', $_POST['to_account']);
        $q->execute();

        $view->set_template([
            'controller' => 'common',
            'action' => 'json'
        ]);
    }

}
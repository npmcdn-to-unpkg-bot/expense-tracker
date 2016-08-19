<?php

namespace Controller;

use Core\DI;

class Expense extends \Core\Controller
{

    public function IndexAction()
    {
        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');
        $conversion = new \Spendings\Conversion();

        /** @var \Core\View $view */
        $view = DI::get('view');

        /** @var \Core\Dispatcher $dispatcher */
        $dispatcher = DI::get('dispatcher');

        $breadcrumbs = [
            [
                'href' => '/',
                'name' => 'Dashboard'
            ]
        ];



//        $category_tree = new \Spendings\Category\Tree();
//
//        var_dump($category_tree);
//        die;


        $q = $dbh->prepare("SELECT *
                            FROM tree
                            WHERE path REGEXP '," . $dispatcher->get_param('category_id') . ",$'
                            ORDER BY NAME");
        $q->execute();
        $items = [];
        while ($r = $q->fetch()) {
            $items[] = [
                'id' => $r['id'],
                'name' => $r['name']
            ];
        }
        $view->set_var('items', $items);


        $breadcrumbs[] = [
            'href' => '/expense/1/',
            'name' => 'Expenses'
        ];

        $q = $dbh->prepare("SELECT * FROM tree
                                WHERE id = :id");
        $q->bindValue(':id', $dispatcher->get_param('category_id'));
        $q->execute();

        $path = '';
        while ($r = $q->fetch()) {
            $path = $r['path'];
        }
        $path = explode(',', substr($path, 1, strlen($path) - 2));
        $path[] = $dispatcher->get_param('category_id');

        foreach ($path as $item) {
            if ($item == 1) {
                continue;
            }
            $q = $dbh->prepare("SELECT * FROM tree
                                WHERE id = :id");
            $q->bindValue(':id', $item);
            $q->execute();
            while ($r = $q->fetch()) {
                $breadcrumbs[] = [
                    'href' => '/expense/' . $item . '/',
                    'name' => $r['name']
                ];
            }
        }

        $view->set_var('catID', $dispatcher->get_param('category_id'));

        $transactions = [];

        $q = $dbh->prepare("SELECT * FROM transactions
                            WHERE catID = :catID
                            AND type = '0'
                            ORDER BY dt DESC");
        $q->bindValue(':catID', $dispatcher->get_param('category_id'));
        $q->execute();
        while ($r = $q->fetch()) {
            $r['price'] /= 100;
            $transactions[] = $r;
        }
        $view->set_var('transactions', $transactions);


        $accounts = [];
        $q = $dbh->prepare("
            SELECT *
            FROM accounts
            WHERE is_external = 0
        ");
        $q->execute();
        while ($r = $q->fetch()) {
            $accounts[] = [
                'id' => $r['id'],
                'name' => $r['name']
            ];
        }

        $view->set_var('accounts', $accounts);
        $view->set_var('breadcrumbs', $breadcrumbs);
    }

    public function AddAction()
    {
        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');

        /** @var \Core\View $view */
        $view = DI::get('view');

        /** @var \Core\Dispatcher $dispatcher */
        $dispatcher = DI::get('dispatcher');

        $day   = (int)$_POST['day']   < 10 ? '0' . $_POST['day']   : $_POST['day'];
        $month = (int)$_POST['month'] < 10 ? '0' . $_POST['month'] : $_POST['month'];
        $year  = (int)$_POST['year']  < 10 ? '0' . $_POST['year']  : $_POST['year'];

        $date = $year . '-' . $month . '-' . $day . ' 00:00:00';

        $q = $dbh->prepare("INSERT INTO transactions
                            SET dt = :date,
                                price = :price,
                                catID = :catID,
                                quantity = :quantity,
                                from_account = :account_from,
                                to_account = 0,
                                type = 0,
                                receipt_id = :receiptId");
        $q->bindValue(':date', $date);
        $q->bindValue(':price', $_POST['price'] * 100);
        $q->bindValue(':catID', $_POST['catID']);
        $q->bindValue(':quantity', $_POST['quantity']);
        $q->bindValue(':account_from', $_POST['account_from']);
        $receiptId = isset($_COOKIE['receipt_id']) && (int)$_COOKIE['receipt_id'] > 0 ? (int)$_COOKIE['receipt_id'] : 0;
        $q->bindValue(':receiptId', $receiptId);
        $q->execute();

        $view->set_template([
            'controller' => 'common',
            'action' => 'json'
        ]);
    }

    public function AddCategoryAction()
    {
        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');

        /** @var \Core\View $view */
        $view = DI::get('view');

        /** @var \Core\Dispatcher $dispatcher */
        $dispatcher = DI::get('dispatcher');

        $q = $dbh->prepare("
            SELECT path FROM tree
            WHERE id = :id
        ");
        $q->bindValue(':id', $_POST['catID']);
        $q->execute();
        $path = '';
        while ($r = $q->fetch()) {
            if ($r['path'] == '') {
                $path = ',' . $_POST['catID'] . ',';
            } else {
                $path = $r['path'] . $_POST['catID'] . ',';
            }
        }



        $q = $dbh->prepare("
            INSERT INTO tree
            SET `name` = :name,
                `path` = :path");
        $q->bindValue(':name', $_POST['name']);
        $q->bindValue(':path', $path);
        $q->execute();

        $view->set_template([
            'controller' => 'common',
            'action' => 'json'
        ]);
    }

}
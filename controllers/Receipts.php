<?php

namespace Controller;

use Core\DI;

class Receipts extends \Core\Controller
{

    public function NewAction()
    {
        /** @var \Core\View $view */
        $view = DI::get('view');

        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');

        $breadcrumbs = [
            [
                'href' => '/',
                'name' => 'Dashboard'
            ],
            [
                'href' => '/receipts/',
                'name' => 'Receipts'
            ],
            [
                'href' => '/receipts/new/',
                'name' => 'New'
            ]
        ];
        $view->set_var('breadcrumbs', $breadcrumbs);

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


        $shops = [];
        $q = $dbh->prepare("
            SELECT *
            FROM shops
            ORDER BY `name`
        ");
        $q->execute();
        while ($r = $q->fetch()) {
            $shops[] = [
                'id' => $r['id'],
                'name' => $r['name']
            ];
        }
        $view->set_var('shops', $shops);

    }


    public function OpenAction()
    {
        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');

        $response = [
            'status' => 'ok'
        ];
        try {
            if ($_POST['day'] == '' || $_POST['month'] == '' || $_POST['year'] == '') {
                throw new \Exception('Wrong date');
            }
            if ($_POST['shop_new'] != '') {
                $query = $dbh->prepare('
                    INSERT INTO shops
                    SET `name` = :name
                ');
                $query->bindValue(':name', $_POST['shop_new']);
                $query->execute();
                $shopId = $dbh->lastInsertId('id');
            } else {
                $shopId = $_POST['shop'];
            }
            $query = $dbh->prepare('
                INSERT INTO baskets
                SET shop_id = :shop_id,
                    dt = :dt,
                    account_id = :account_id
            ');
            $query->bindValue(':shop_id', $shopId);
            $query->bindValue(':dt', $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day']);
            $query->bindValue(':account_id', $_POST['account']);
            $query->execute();
            $receiptId = $dbh->lastInsertId('id');
            setcookie('receipt_id', $receiptId, time() + 24 * 3600, '/');
        } catch (\Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        echo json_encode($response);
        die;
    }

    public function CloseAction()
    {
        setcookie('receipt_id', '', time() + 365 * 24 * 3600, '/');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        die;
    }

}
<?php

namespace ExpenseTracker\Controller;

use ExpenseTracker\Controller;

class Home extends Controller
{

    public function index()
    {
        /** @var \PDO $dbh */
        $dbh = $this->_di->get('mysql');
        $conversion = new \Spendings\Conversion();

        /** @var \Core\View $view */
        $view = $this->_di->get('view');

        $data = [];

        $q = $dbh->prepare("
            SELECT
                CONCAT(YEAR(t.dt), '-', MONTH(t.dt)) AS d,
                SUM(price) AS s
            FROM transactions AS t
                INNER JOIN accounts AS a_from
                ON t.from_account = a_from.id
                INNER JOIN accounts AS a_to
                ON t.to_account = a_to.id
            WHERE t.from_account <> 0
            AND a_from.is_external = 1
            GROUP BY d
            ORDER BY d DESC
        ");
        $q->execute();
        while ($r = $q->fetch()) {
            $data[$r['d']] = [
                'd' => $r['d'],
                'income' => floor($r['s'] / 100),
                'expense' => 0
            ];
        }

        $q = $dbh->prepare("
            SELECT
              CONCAT(YEAR(t.dt), '-', MONTH(t.dt)) AS d,
              SUM(price) AS s
            FROM transactions AS t
            INNER JOIN accounts AS a_from
              ON t.from_account = a_from.id
            INNER JOIN accounts AS a_to
              ON t.to_account = a_to.id
            WHERE t.to_account = 0
            GROUP BY d 
            ORDER BY d DESC
        ");
        $q->execute();
        while ($r = $q->fetch()) {
            if (isset($data[$r['d']])) {
                $data[$r['d']]['expense'] = floor($r['s'] / 100);
            } else {
                $data[$r['d']] = [
                    'expense' => floor($r['s'] / 100),
                    'income' => 0,
                    'd' => $r['d']
                ];
            }
        }

        $view->set_var('data', $data);

        $q = $dbh->prepare("
            SELECT SUM(price) AS total, dt
            FROM transactions
            WHERE to_account = 0
            AND catID <> 72
            GROUP BY dt
        ");
        $q->execute();
        $data = [];

        while ($r = $q->fetch()) {
            $data[substr($r['dt'], 0, 10)] = (int)floor($r['total'] / 100);
        }

        $output = [];
        $ma7 = [];
        $ma30 = [];

        for ($i = 30; $i >= 0; $i--) {
            $date = date('Y-m-d', time() - $i * 24 * 3600);
            if (isset($data[$date])) {
                $output[$date] = $data[$date];
            } else {
                $output[$date] = 0;
            }
//    $ma7_total = 0;
//    for ($j = 6; $j >= 0; $j--) {
//        $ma_date = date('Y-m-d', time() - ($i + $j) * 24 * 3600);
//        $ma7_total += isset($data[$ma_date]) ? $data[$ma_date] : 0;
//    }
        }
        for ($i = 30; $i >= 0; $i--) {
            $date = date('Y-m-d', time() - $i * 24 * 3600);
            if (isset($data[$date])) {
                $output[$date] = $data[$date];
            } else {
                $output[$date] = 0;
            }
            $ma7_total = 0;
            $ma30_total = 0;
            for ($j = 6; $j >= 0; $j--) {
                $ma_date = date('Y-m-d', time() - ($i + $j) * 24 * 3600);
                $ma7_total += isset($data[$ma_date]) ? $data[$ma_date] : 0;
            }
            for ($j = 29; $j >= 0; $j--) {
                $ma_date = date('Y-m-d', time() - ($i + $j) * 24 * 3600);
                $ma30_total += isset($data[$ma_date]) ? $data[$ma_date] : 0;
            }
            $ma7[$date] = (int)floor($ma7_total / 7);
            $ma30[$date] = (int)floor($ma30_total / 30);
        }


        $bars = [];
        $query = $dbh->prepare("
            SELECT id, name, monthly_allowance
            FROM tree
            WHERE monthly_allowance > 0
        ");
        $query->execute();
        while ($r = $query->fetch()) {
            $cat_ids = [
                $r['id']
            ];
            $q = $dbh->prepare("
                SELECT id
                FROM tree
                WHERE path REGEXP ',{$r['id']},'
            ");
            $q->execute();
            while ($r2 = $q->fetch()) {
                $cat_ids[] = $r2['id'];
            }
            $q = $dbh->prepare("
                SELECT SUM(price) AS total
                FROM transactions
                WHERE catID IN (" . implode(',', $cat_ids) . ")
                AND to_account = 0
                AND dt > '" . date('Y-m-d 00:00:00', time() - 30 * 24 * 3600) . "'
            ");
            $q->execute();
            while ($r2 = $q->fetch()) {
                $bars[] = [
                    'name' => $r['name'],
                    'id' => $r['id'],
                    'allowed' => number_format($r['monthly_allowance'] / 100, 0, '.', ' '),
                    'total' => (int)floor($r2['total'] / 100),
                    'diff' => number_format($r['monthly_allowance'] / 100 - $r2['total'] / 100, 0, '.', ' '),
                    'class' => $r['monthly_allowance'] / 100 - (int)floor($r2['total'] / 100) > 0 ? 'green' : 'red'
                ];
            }
        }


        $view->set_var('bars', $bars);
        $view->set_var('graph', $output);
        $view->set_var('graph_ma7', $ma7);
        $view->set_var('graph_ma30', $ma30);


        $accounts = [];
        $total = 0;
        $q = $dbh->prepare("
            SELECT
              SUM(t.price * IF(t.from_account = a.id,-1,1)) AS amount,
              a.*
            FROM transactions AS t
            RIGHT JOIN accounts AS a
              ON t.from_account = a.id
              OR t.to_account = a.id
            WHERE a.is_external = 0
            GROUP   BY a.id
            ORDER BY id
        ");
        $q->execute();

        $total_rub = 0;
        $total_rub_disposable = 0;
        $total_usd = 0;
        $total_eur = 0;

        while ($r = $q->fetch()) {
            $accounts[] = [
                'name' => $r['name'],
                'amount' => number_format(((is_null($r['amount']) ? 0 : $r['amount']) + $r['initial_amount']) / 100, 2, '.', ' '),
                'class' => ($r['amount'] + $r['initial_amount']) >= 0 ? 'green' : 'red',
                'currency' => $r['currency']
            ];
            switch ($r['currency']) {
                case 'RUB':
                    $total_rub += $r['amount'] + $r['initial_amount'];
                    if ($r['id'] != 14) { // DEBT ACCOUNT
                        $total_rub_disposable += $r['amount'] + $r['initial_amount'] - $r['blocked'];
                    }
                    $total_usd += $conversion->buy_usd($r['amount'] + $r['initial_amount']);
                    $total_eur += $conversion->buy_eur($r['amount'] + $r['initial_amount']);
                    break;
                case 'USD':
                    $total_rub += $conversion->sell_usd($r['amount'] + $r['initial_amount']);
                    $total_rub_disposable += $conversion->sell_usd($r['amount'] + $r['initial_amount'] - $r['blocked']);
                    $total_usd += $r['amount'] + $r['initial_amount'];
                    $total_eur += $conversion->buy_eur($conversion->sell_usd($r['amount'] + $r['initial_amount']));
                    break;
                case 'EUR':
                    $total_rub += $conversion->sell_eur($r['amount'] + $r['initial_amount']);
                    $total_rub_disposable += $conversion->sell_eur($r['amount'] + $r['initial_amount'] - $r['blocked']);
                    $total_usd += $conversion->buy_usd($conversion->sell_eur($r['amount'] + $r['initial_amount']));
                    $total_eur += $r['amount'] + $r['initial_amount'];
                    break;
            }
//    $total += ($r['amount'] + $r['initial_amount']) * call_user_func(function($currency) {
//            switch ($currency) {
//                case 'RUB': return 1;
//                case 'USD': return 64.5;
//                case 'EUR': return 73.5;
//                default: return 1;
//            }
//        }, $r['currency']);
        }

        $view->set_var('accounts', $accounts);
        $view->set_var('accounts_total_rub', number_format(floor($total_rub / 100), 0, '.', ' '));
        $view->set_var('accounts_total_usd', number_format(floor($total_usd / 100), 0, '.', ' '));
        $view->set_var('accounts_total_eur', number_format(floor($total_eur / 100), 0, '.', ' '));

        $view->set_var('accounts_total_rub_disposable', number_format($total_rub_disposable / 100, 0, '.', ' '));

        $q = $dbh->prepare("
            SELECT
                MIN(dt) as min,
                MAX(dt) as max
            FROM transactions
        ");
        $q->execute();
        $min_date = null;
        $max_date = null;
        $total_days = 0;
        $daily_burn_rate = 0;
        $monthly_burn_rate = 0;
        while ($r = $q->fetch()) {
            $min_date = $r['min'];
            $max_date = $r['max'];
        }

        $max_date = date('Y-m-d 00:00:00');

        if (!is_null($min_date) && !is_null($max_date)) {
            $total_days = floor((strtotime($max_date) - strtotime($min_date)) / 24 / 3600);
        }
        if ($total_days > 0) {
            $q = $dbh->prepare("
                SELECT
                  CONCAT(YEAR(t.dt), '-', MONTH(t.dt)) AS d,
                  SUM(price) AS s
                FROM transactions AS t
                INNER JOIN accounts AS a_from
                  ON t.from_account = a_from.id
                INNER JOIN accounts AS a_to
                  ON t.to_account = a_to.id
                WHERE t.to_account = 0
                GROUP BY d
            ");
            $q->execute();
            while ($r = $q->fetch()) {
                $daily_burn_rate = number_format($r['s'] / $total_days / 100, 0, ',', ' ');
                $monthly_burn_rate = number_format($r['s'] / $total_days / 100 * ((365 * 4 + 1) / 48), 0, ',' ,' ');
            }
        }

        $view->set_var('daily_burn_rate', $daily_burn_rate);
        $view->set_var('monthly_burn_rate', $monthly_burn_rate);

        $view->set_var('breadcrumbs', [
            [
                'href' => '/',
                'name' => 'Dashboard'
            ]
        ]);


        $q = $dbh->prepare("
            SELECT
              CONCAT(YEAR(t.dt), '-', MONTH(t.dt)) AS d,
              SUM(price) AS s
            FROM transactions AS t
            INNER JOIN accounts AS a_from
              ON t.from_account = a_from.id
            INNER JOIN accounts AS a_to
              ON t.to_account = a_to.id
            WHERE t.to_account = 0
            GROUP BY d
            ORDER BY d DESC
        ");
        $q->execute();
        $data = [];

        while ($r = $q->fetch()) {
            $data[$r['d']] = [
                'd' => $r['d'],
                'total' => floor($r['s'] / 100)
            ];
        }


        $q = $dbh->prepare("
            SELECT
                CONCAT(YEAR(t.dt), '-', MONTH(t.dt)) AS d,
                SUM(price) AS s
            FROM transactions AS t
                INNER JOIN accounts AS a_from
                ON t.from_account = a_from.id
                INNER JOIN accounts AS a_to
                ON t.to_account = a_to.id
            WHERE t.from_account <> 0
            AND a_from.is_external = 1
            GROUP BY d
        ");
        $q->execute();
        while ($r = $q->fetch()) {
            $data[$r['d']]['income'] = floor($r['s'] / 100);
        }


        // All foodies categories

        $q = $dbh->prepare("
            SELECT id
            FROM tree
            WHERE path REGEXP ',2,'
        ");
        $foodies = [2];
        $q->execute();
        while ($r = $q->fetch()) {
            $foodies[] = $r['id'];
        }

        $q = $dbh->prepare("
            SELECT
                CONCAT(YEAR(t.dt), '-', MONTH(t.dt)) AS d,
                SUM(price) AS s
            FROM transactions AS t
            INNER JOIN accounts AS a_from
              ON t.from_account = a_from.id
            INNER JOIN accounts AS a_to
              ON t.to_account = a_to.id
            WHERE t.to_account = 0
            AND t.catID IN (" . implode(',', $foodies) . ")
            GROUP BY d
        ");
        $q->execute();
        while ($r = $q->fetch()) {
            $data[$r['d']]['foodies'] = ceil(floor($r['s'] / 100) / $data[$r['d']]['income'] * 100);
        }



        // Food at workplace

        $q = $dbh->prepare("
            SELECT
                CONCAT(YEAR(t.dt), '-', MONTH(t.dt)) AS d,
                SUM(price) AS s
            FROM transactions AS t
            INNER JOIN accounts AS a_from
              ON t.from_account = a_from.id
            INNER JOIN accounts AS a_to
              ON t.to_account = a_to.id
            WHERE t.to_account = 0
            AND t.catID = 53
            GROUP BY d
        ");
        $q->execute();
        while ($r = $q->fetch()) {
            $data[$r['d']]['workplace'] = ceil(floor($r['s'] / 100) / $data[$r['d']]['income'] * 100);
        }


        // All services categories

        $q = $dbh->prepare("
            SELECT id
            FROM tree
            WHERE path REGEXP ',3,'
        ");
        $services = [3];
        $q->execute();
        while ($r = $q->fetch()) {
            $services[] = $r['id'];
        }

        $q = $dbh->prepare("
            SELECT
                CONCAT(YEAR(t.dt), '-', MONTH(t.dt)) AS d,
                SUM(price) AS s
            FROM transactions AS t
            INNER JOIN accounts AS a_from
              ON t.from_account = a_from.id
            INNER JOIN accounts AS a_to
              ON t.to_account = a_to.id
            WHERE t.to_account = 0
            AND t.catID IN (" . implode(',', $services) . ")
            GROUP BY d
        ");
        $q->execute();
        while ($r = $q->fetch()) {
            $data[$r['d']]['services'] = ceil(floor($r['s'] / 100) / $data[$r['d']]['income'] * 100);
        }




        foreach ($data as $date => &$d) {
            $d['all_expense'] = floor($d['total'] / $d['income'] * 100);
            $d['savings'] = 100 - $d['all_expense'];
            $d['rest'] = $d['all_expense']
                - (isset($d['foodies']) ? $d['foodies'] : 0)
                - (isset($d['workplace']) ? $d['workplace'] : 0)
                - (isset($d['services']) ? $d['services'] : 0);
        }




        $view->set_var('structure', $data);
//        var_dump($data);
//        die;

    }

}
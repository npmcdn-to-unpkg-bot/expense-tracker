<?php

namespace Controller;

use Core\DI;

class Stats
{

    public function IndexAction()
    {
        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');

        /** @var \Core\View $view */
        $view = DI::get('view');

        /** @var \Core\Dispatcher $dispatcher */
        $dispatcher = DI::get('dispatcher');

        $date_query =
            (is_null($dispatcher->get_param('year'))  ? '/' . $dispatcher->get_param('year')  : '') .
            (is_null($dispatcher->get_param('month')) ? '/' . $dispatcher->get_param('month') : '');
        $view->set_var('date_query', $date_query);

        $breadcrumbs = [
            [
                'href' => '/',
                'name' => 'Dashboard'
            ]
        ];
        $breadcrumbs[] = [
            'href' => '/stats/1' . $date_query,
            'name' => 'Stats'
        ];

        $category_id = intval($dispatcher->get_param('category_id'), 10);

        $q = $dbh->prepare("SELECT * FROM tree
                            WHERE id = :id");
        $q->bindValue(':id', $category_id, \PDO::PARAM_INT);
        $q->execute();

        $path = '';
        while ($r = $q->fetch()) {
            $path = $r['path'];
        }
        $path = explode(',', substr($path, 1, strlen($path) - 2));
        $path[] = $category_id;

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
                    'href' => '/stats/' . $item . $date_query,
                    'name' => $r['name']
                ];
            }
        }

        $view->set_var('catID', $category_id);


        // Find list of all first-level children of the current category
        $q = $dbh->prepare("SELECT * FROM tree
                            WHERE path REGEXP '," . $category_id . ",$'");
        $q->execute();
        $rootCats = [];
        while ($r = $q->fetch()) {
            $rootCats[] = [
                'id' => $r['id'],
                'name' => $r['name'],
                'sum' => 0,
                'leaves' => [$r['id']]
            ];
        }

        // Find all-level children for our categories


        foreach ($rootCats as &$cat) {
            $q = $dbh->prepare("SELECT * FROM tree
                                WHERE path REGEXP '," . $cat['id'] . ",'");
            $q->execute();
            while ($r = $q->fetch()) {
                $cat['leaves'][] = $r['id'];
            }
        }

        $totalSpent = 0;
        $maxSpent = 0;

        foreach ($rootCats as &$cat) {
            $cats = implode(',', $cat['leaves']);
            if ($cats == '') {
                continue;
            }
            $year_where  = !is_null($dispatcher->get_param('year'))  ? ' AND YEAR(dt)  = :year ' : '';
            $month_where = !is_null($dispatcher->get_param('month')) ? ' AND MONTH(dt) = :month ' : '';
            $q = $dbh->prepare("SELECT SUM(price) FROM transactions
                                WHERE catID in ({$cats}) " . $year_where . $month_where . "
                                AND type = '0'");
            if (!is_null($dispatcher->get_param('year'))) {
                $q->bindValue(':year', $dispatcher->get_param('year'), \PDO::PARAM_INT);
            }
            if (!is_null($dispatcher->get_param('month'))) {
                $q->bindValue(':month', $dispatcher->get_param('month'), \PDO::PARAM_INT);
            }
            $q->execute();
            while ($r = $q->fetch()) {
                $cat['sum'] = ceil(($r['SUM(price)'] > 0 ? $r['SUM(price)'] : 0) / 100);
                $totalSpent += $cat['sum'];
                if ($cat['sum'] > $maxSpent) {
                    $maxSpent = $cat['sum'];
                }
            }
        }
        foreach ($rootCats as &$cat) {
            $cat['width'] = $maxSpent > 0 ? ceil(700 * ($cat['sum'] / $maxSpent)) : 0;
        }


//        function cmp($a, $b)
//        {
//
//        }

        usort($rootCats, function($a, $b) {
            return $b["sum"] > $a["sum"];
        });


        $view->set_var('data', $rootCats);
        $view->set_var('totalSpent', $rootCats);

        $view->set_var('breadcrumbs', $breadcrumbs);

        //var_dump(App::$twigData['data']);

    }

}

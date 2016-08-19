<?php

namespace Spendings\Category;

use Core\DI;
use Spendings\Category\Factory as CategoryFactory;

class Tree
{

    private $categories = [];

    public function __construct()
    {
        /** @var \PDO $dbh */
        $dbh = DI::get('mysql');

        $query_sql = "
            SELECT * FROM tree ORDER BY id
        ";
        $query = $dbh->prepare($query_sql);
        $query->execute();
        while ($row = $query->fetch()) {
            /** @var \Spendings\Category $category */
            $category = CategoryFactory::make($row);
            $this->categories[$category->getId()] = $category;
        }
    }

}
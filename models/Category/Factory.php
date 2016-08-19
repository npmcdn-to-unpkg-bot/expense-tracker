<?php

namespace Spendings\Category;

use Spendings\Category;

class Factory
{

    public static function make($row)
    {
        $category = new Category();
        $category->setId($row['id']);
        $category->setName($row['name']);
        $category->setPath($row['path']);
        $category->setIcon($row['icon']);
        $category->setMonthlyAllowance($row['monthly_allowance']);
        return $category;
    }

}
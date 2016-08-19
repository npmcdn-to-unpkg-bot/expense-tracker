<?php

namespace Spendings;

class Conversion
{

    private static $_usd_to_rub_buy = 67.0;
    private static $_usd_to_rub_sell = 71.0;

    private static $_eur_to_rub_buy = 72.5;
    private static $_eur_to_rub_sell = 76.5;

    public function buy_usd($rub)
    {
        return $rub / self::$_usd_to_rub_sell;
    }

    public function sell_usd($usd)
    {
        return $usd * self::$_usd_to_rub_buy;
    }

    public function buy_eur($rub)
    {
        return $rub / self::$_eur_to_rub_sell;
    }

    public function sell_eur($eur)
    {
        return $eur * self::$_eur_to_rub_buy;
    }

}
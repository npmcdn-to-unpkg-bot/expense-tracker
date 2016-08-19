<?php

namespace Spendings\Transaction;

class Transaction
{

    private $id;
    private $receiptId;
    private $catId;
    private $date;
    private $price;
    private $quantity;
    private $toAccountId;
    private $fromAccountId;

    public function getCatId()
    {
        return $this->catId;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getFromAccountId()
    {
        return $this->fromAccountId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getReceiptId()
    {
        return $this->receiptId;
    }

    public function getToAccountId()
    {
        return $this->toAccountId;
    }

    public function setCatId($catId)
    {
        $this->catId = $catId;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function setFromAccountId($fromAccountId)
    {
        $this->fromAccountId = $fromAccountId;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function setReceiptId($receiptId)
    {
        $this->receiptId = $receiptId;
    }

    public function setToAccountId($toAccountId)
    {
        $this->toAccountId = $toAccountId;
    }

}
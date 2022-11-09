<?php

namespace App\Repository\Transaction;

interface TransactionRepository
{
    public function all($page, $size, $search);
    public function create(array  $data);
    public function update(array $data, $id);
    public function find($id);
    public function findInv($id);
    public function count($search);
    public function allHistory($page, $size, $id);
    public function countHistory($id);
}

<?php

namespace App\Repository\AuthLog;

interface AuthLogRepository
{
    public function all($page, $size, $search);
    public function create(array  $data);
    public function update(array $data, $id);
    public function find($id);
    public function count($search);
}

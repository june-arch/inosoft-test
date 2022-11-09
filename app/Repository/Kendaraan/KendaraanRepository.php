<?php

namespace App\Repository\Kendaraan;

interface KendaraanRepository
{
    public function all($page, $size, $search);
    public function allAvailable($page, $size);
    public function create(array  $data);
    public function update(array $data, $id);
    public function delete($id);
    public function find($id);
    public function findKodeKendaraan($id);
    public function count($search);
    public function countAvailable();
}

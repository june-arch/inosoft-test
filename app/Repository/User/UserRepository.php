<?php

namespace App\Repository\User;

interface UserRepository
{
    public function all($page, $size, $search);
    public function create(array  $data);
    public function update(array $data, $id);
    public function delete($id);
    public function find($id);
    public function findByEmail($email);
    public function count($search);
}

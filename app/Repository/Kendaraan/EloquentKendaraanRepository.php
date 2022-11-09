<?php

namespace App\Repository\Kendaraan;

use App\Models\Kendaraan;
use Illuminate\Http\Response;
use App\Helper\Wrapper;
use MongoDB\BSON\Regex;

class EloquentKendaraanRepository implements KendaraanRepository
{
    protected $model;
    protected $wrapper;
    public function __construct(Kendaraan $kendaraan, Wrapper $wrapper)
    {
        $this->model = $kendaraan;
        $this->wrapper = $wrapper;
    }

    public function all($page, $size, $search)
    {
        try {
            $find = $this->model->skip(($page-1)*$size)->take($size)->project(['_id'=>0]);
            if($search){
                $find = $this->model->where('name', 'like', "%{$search}%")->skip(($page-1)*$size)->take($size)->project(['_id'=>0]);
            }
            $result = $find->get();
            if (count($result) == 0) {
                return $this->wrapper->error([
                    "message" => "data not found",
                    "data" => null,
                    "code" => Response::HTTP_NOT_FOUND
                ]);
            }
            return $this->wrapper->data($result);
        } catch (\Throwable $th) {
            return $this->wrapper->error([
                "message" => "something went wrong",
                "data" => null,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR
            ]);
        }
    }

    public function allAvailable($page, $size)
    {
        try {
            $result = $this->model->where('status', 1)->skip(($page-1)*$size)->take($size)->project(['_id'=>0])->get();
            if (count($result) == 0) {
                return $this->wrapper->error([
                    "message" => "data not found",
                    "data" => null,
                    "code" => Response::HTTP_NOT_FOUND
                ]);
            }
            return $this->wrapper->data($result);
        } catch (\Throwable $th) {
            return $this->wrapper->error([
                "message" => "something went wrong",
                "data" => null,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR
            ]);
        }
    }

    public function count($search)
    {
        try {
            $result = $this->model->count();
            if($search){
                $result = $this->model->where('name', 'like', "%{$search}%")->count();
            }
            if ($result == 0) {
                return $this->wrapper->error([
                    "message" => "data not found",
                    "data" => null,
                    "code" => Response::HTTP_NOT_FOUND
                ]);
            }
            return $this->wrapper->data($result);
        } catch (\Throwable $th) {
            return $th;
            return $this->wrapper->error([
                "message" => "something went wrong",
                "data" => null,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR
            ]);
        }
    }

    public function countAvailable()
    {
        try {
            $result = $this->model->where('status', 1)->count();
            if ($result == 0) {
                return $this->wrapper->error([
                    "message" => "data not found",
                    "data" => null,
                    "code" => Response::HTTP_NOT_FOUND
                ]);
            }
            return $this->wrapper->data($result);
        } catch (\Throwable $th) {
            return $th;
            return $this->wrapper->error([
                "message" => "something went wrong",
                "data" => null,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR
            ]);
        }
    }

    public function create(array $data)
    {
        try {
            $result = $this->model->create($data);
            unset($result['_id']);
            return $this->wrapper->data($result);
        } catch (\Throwable $th) {
            return $this->wrapper->error([
                "message" => "something went wrong",
                "data" => null,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR
            ]);
        }
    }

    public function update(array $data, $id)
    {
        try {
            $result = $this->model->where('slug', $id)->update($data);
            return $this->wrapper->data($result);
        } catch (\Throwable $th) {
            return $this->wrapper->error([
                "message" => "something went wrong",
                "data" => null,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $result = $this->model->where('slug', $id)->delete();
            return $this->wrapper->data($result);
        } catch (\Throwable $th) {
            return $this->wrapper->error([
                "message" => "something went wrong",
                "data" => null,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR
            ]);
        }
    }

    public function find($id)
    {
        $kendaraan = $this->model->where('slug',$id)->project(['_id' => 0])->get();
        if (count($kendaraan) == 0) {
            return $this->wrapper->error([
                "message" => "slug not found",
                "data" => null,
                "code" => Response::HTTP_NOT_FOUND
            ]);
        }

        return $this->wrapper->data($kendaraan);
    }
    public function findKodeKendaraan($id)
    {
        $kendaraan = $this->model->where('kode_kendaraan',$id)->project(['_id' => 0])->get();
        if (count($kendaraan) == 0) {
            return $this->wrapper->error([
                "message" => "kode_kendaraan not found",
                "data" => null,
                "code" => Response::HTTP_NOT_FOUND
            ]);
        }

        return $this->wrapper->data($kendaraan);
    }
}

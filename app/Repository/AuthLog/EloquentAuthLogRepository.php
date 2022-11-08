<?php

namespace App\Repository\AuthLog;

use App\Models\AuthLog;
use Illuminate\Http\Response;
use App\Helper\Wrapper;
use MongoDB\BSON\Regex;

class EloquentAuthLogRepository implements AuthLogRepository
{
    protected $model;
    protected $wrapper;
    public function __construct(AuthLog $authLog, Wrapper $wrapper)
    {
        $this->model = $authLog;
        $this->wrapper = $wrapper;
    }

    public function all($page, $size, $search)
    {
        try {
            $find = $this->model->skip(($page-1)*$size)->take($size)->project(['_id'=>0]);
            if($search){
                $find = $this->model->where('user.email', 'like', "%{$search}%");
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

    public function count($search)
    {
        try {
            $result = $this->model->count();
            if($search){
                $result = $this->model->where('user.email', 'like', "%{$search}%")->count();
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

    public function create(array $data)
    {
        try {
            $result = $this->model->create($data);
            unset($result['_id']);
            unset($result['user']);
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
            $result = $this->model->where('auth_log_id', $id)->update($data);
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
        $user = $this->model->where('auth_log_id',$id)->project(['_id' => 0])->get();
        if (count($user) == 0) {
            return $this->wrapper->error([
                "message" => "id not found",
                "data" => null,
                "code" => Response::HTTP_NOT_FOUND
            ]);
        }

        return $this->wrapper->data($user);
    }
}

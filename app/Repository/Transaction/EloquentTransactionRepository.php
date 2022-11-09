<?php

namespace App\Repository\Transaction;

use App\Models\Transaction;
use Illuminate\Http\Response;
use App\Helper\Wrapper;
use MongoDB\BSON\Regex;

class EloquentTransactionRepository implements TransactionRepository
{
    protected $model;
    protected $wrapper;
    public function __construct(Transaction $transaction, Wrapper $wrapper)
    {
        $this->model = $transaction;
        $this->wrapper = $wrapper;
    }

    public function all($page, $size, $search)
    {
        try {
            $find = $this->model->skip(($page-1)*$size)->take($size)->project(['_id'=>0]);
            if($search){
                $find = $this->model->where('inv_number', 'like', "%{$search}%");
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

    public function allHistory($page, $size, $id)
    {
        try {
            $find = $this->model->where('user.user_id', $id)->skip(($page-1)*$size)->take($size)->project(['_id'=>0]);
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
                $result = $this->model->where('inv_number', 'like', "%{$search}%")->count();
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

    public function countHistory($id)
    {
        try {
            $result = $this->model->where('user.user_id', $id)->count();
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
            $result = $this->model->where('inv_number', $id)->update($data);
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
        $transaction = $this->model->where('transaction_id',$id)->project(['_id' => 0])->get();
        if (count($transaction) == 0) {
            return $this->wrapper->error([
                "message" => "id not found",
                "data" => null,
                "code" => Response::HTTP_NOT_FOUND
            ]);
        }

        return $this->wrapper->data($transaction);
    }

    public function findInv($id)
    {
        $transaction = $this->model->where('inv_number',$id)->project(['_id' => 0])->get();
        error_log($id);
        if (count($transaction) == 0) {
            return $this->wrapper->error([
                "message" => "inv not found",
                "data" => null,
                "code" => Response::HTTP_NOT_FOUND
            ]);
        }

        return $this->wrapper->data($transaction);
    }
}

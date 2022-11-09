<?php

namespace App\Repository\PaymentProduct;

use App\Models\PaymentProduct;
use Illuminate\Http\Response;
use App\Helper\Wrapper;
use MongoDB\BSON\Regex;

class EloquentPaymentProductRepository implements PaymentProductRepository
{
    protected $model;
    protected $wrapper;
    public function __construct(PaymentProduct $paymentProduct, Wrapper $wrapper)
    {
        $this->model = $paymentProduct;
        $this->wrapper = $wrapper;
    }

    public function find($id)
    {
        $result = $this->model->where('product_code',$id)->project(['_id' => 0])->get();
        if (count($result) == 0) {
            return $this->wrapper->error([
                "message" => "product_code not found",
                "data" => null,
                "code" => Response::HTTP_NOT_FOUND
            ]);
        }

        return $this->wrapper->data($result);
    }
}

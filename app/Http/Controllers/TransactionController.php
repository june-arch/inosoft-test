<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Helper\Wrapper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Webpatser\Uuid\Uuid;
use App\Repository\Transaction\EloquentTransactionRepository;
use App\Repository\Kendaraan\EloquentKendaraanRepository;
use App\Repository\User\EloquentUserRepository;
use App\Repository\PaymentProduct\EloquentPaymentProductRepository;

class TransactionController extends Controller
{
    private $wrapper;

    public function __construct(
        Wrapper $wrapper,
        EloquentTransactionRepository $eloquentTransaction,
        EloquentKendaraanRepository $eloquentKendaraan,
        EloquentUserRepository $eloquentUser,
        EloquentPaymentProductRepository $eloquentPaymentProduct)
    {
        $this->wrapper = $wrapper;
        $this->eloquentTransaction = $eloquentTransaction;
        $this->eloquentKendaraan = $eloquentKendaraan;
        $this->eloquentUser = $eloquentUser;
        $this->eloquentPaymentProduct = $eloquentPaymentProduct;
    }

    public function index(Request $request)
    {
        $query = $request->query();
        $page = $query["page"] ?? 1;
        $size = $query["size"] ?? 10;
        $search = $query["search"] ?? '';
        ["data"=>$data, "err"=>$err] = $this->eloquentTransaction->all((int)$page, (int)$size, $search);
        if($err){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $err;
            return $this->wrapper->response($data, $message, $code);
        }
        ["data"=>$dataCount, "err"=>$errCount] = $this->eloquentTransaction->count($search);
        if($errCount){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errCount;
            return $this->wrapper->response($data, $message, $code);
        }
        $meta = [
            "page" => $page,
            "totalPage" => ceil($dataCount/$size),
            "totalData" => $dataCount,
            "totalDataOnPage" => count($data),
        ];
        return $this->wrapper->responsePage($data, $meta, 'success get all transaction');
    }

    public function history(Request $request){
        $query = $request->query();
        $page = $query["page"] ?? 1;
        $size = $query["size"] ?? 10;
        $search = $query["search"] ?? '';
        $user = $request->user();
        ["data"=>$data, "err"=>$err] = $this->eloquentTransaction->allHistory((int)$page, (int)$size, $user->user_id);
        if($err){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $err;
            return $this->wrapper->response($data, $message, $code);
        }
        ["data"=>$dataCount, "err"=>$errCount] = $this->eloquentTransaction->countHistory($user->user_id);
        if($errCount){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errCount;
            return $this->wrapper->response($data, $message, $code);
        }
        $meta = [
            "page" => $page,
            "totalPage" => ceil($dataCount/$size),
            "totalData" => $dataCount,
            "totalDataOnPage" => count($data),
        ];
        return $this->wrapper->responsePage($data, $meta, 'success get all Transaction');
    }


    public function order(Request $request)
    {
        //Validate data
        $user = $request->user();
        $data = $request->only('kode_kendaraan','product_code');
        $validator = Validator::make($data, [
            'kode_kendaraan' => 'required|string',
            'product_code' => 'required|string'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->wrapper->response(null, $validator->messages(), Response::HTTP_BAD_REQUEST);
        }

        ['data' => $data, 'err' => $err] = $this->eloquentKendaraan->findKodeKendaraan($request->kode_kendaraan);
        if($err){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $err;
            return $this->wrapper->response($data, $message, $code);
        }
        ['data' => $dataPayment, 'err' => $errPayment] = $this->eloquentPaymentProduct->find($request->product_code);
        if($errPayment){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errPayment;
            return $this->wrapper->response($data, $message, $code);
        }

        $document = [
            "transaction_id" => (string) Uuid::generate(),
            "inv_number" => "inv".number_format(microtime(true),0,'.','').strtolower(substr($data[0]['kode_kendaraan'],0,4)),
            "user" => $user,
            "kendaraan" => [
                "name" => $data[0]['name'],
                "slug" => $data[0]['slug'],
                "tahun_keluaran" => $data[0]['tahun_keluaran'],
                "warna" => $data[0]['warna'],
                'harga' => $data[0]['harga'],
                'jenis_kendaraan' => $data[0]['jenis_kendaraan'],
                'tipe_suspensi' => $data[0]['tipe_suspensi'],
                'tipe_transmisi' => $data[0]['tipe_transmisi'],
                'mesin' => $data[0]['mesin'],
                'kapasitas_penumpang' => $data[0]['kapasitas_penumpang'],
                'tipe' => $data[0]['tipe'],
                'status' => $data[0]['status'],
                'stok' => $data[0]['stok'],
                'kode_kendaraan' => $data[0]['kode_kendaraan'],
            ],
            "payment_method" => [
                "product_code" => $dataPayment[0]['product_code'],
                "product_name" => $dataPayment[0]['product_name'],
            ],
            'expierd' => $request->harga,
            'status' => 'UNPAID',
            'total_price' => $data[0]['harga']
        ];

        ['data'=>$data, 'err'=>$errCreate] = $this->eloquentTransaction->create($document);
        if($errCreate){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errCreate;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success create order', Response::HTTP_CREATED);
    }


    public function show($id)
    {
        ['data'=>$data, 'err'=>$err] = $this->eloquentTransaction->find($id);
        if($err){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $err;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success find data');
    }

    public function callback(Request $request)
    {
        //Validate data
        $user = $request->user();
        $data = $request->only('inv_number','amount', 'status');
        $validator = Validator::make($data, [
            'inv_number' => 'required|string',
            'amount' => 'required|integer',
            'status' => 'required|in:PAID,UNPAID,CANCEL'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->wrapper->response(null, $validator->messages(), Response::HTTP_BAD_REQUEST);
        }
        ['err'=>$err, 'data' => $data] = $this->eloquentTransaction->findInv($request->inv_number);
        if($err){
            return $this->wrapper->response(null, 'transaction not found', Response::HTTP_CONFLICT);
        }
        ["data"=>$dataKendaraan, "err"=>$errKendaraan] = $this->eloquentKendaraan->update(["stok" => $data[0]["kendaraan"]["stok"] - 1], $data[0]["kendaraan"]["slug"]);
        if($errKendaraan){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errKendaraan;
            return $this->wrapper->response($data, $message, $code);
        }
        ['data'=>$data, 'err'=>$errUpdate] = $this->eloquentTransaction->update(
            ["kendaraan.stok" => $data[0]["kendaraan"]["stok"] - 1, "status" => $request->status], $request->inv_number);
        if($errUpdate){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errUpdate;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success callback transaction');
    }
}

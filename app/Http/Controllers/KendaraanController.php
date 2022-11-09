<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Kendaraan;
use App\Helper\Wrapper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Webpatser\Uuid\Uuid;
use App\Repository\Kendaraan\EloquentKendaraanRepository;

class KendaraanController extends Controller
{
    private $wrapper;

    public function __construct(Wrapper $wrapper, EloquentKendaraanRepository $eloquentKendaraan)
    {
        $this->wrapper = $wrapper;
        $this->eloquentKendaraan = $eloquentKendaraan;
    }

    public function index(Request $request)
    {
        $query = $request->query();
        $page = $query["page"] ?? 1;
        $size = $query["size"] ?? 10;
        $search = $query["search"] ?? '';
        ["data"=>$data, "err"=>$err] = $this->eloquentKendaraan->all((int)$page, (int)$size, $search);
        if($err){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $err;
            return $this->wrapper->response($data, $message, $code);
        }
        ["data"=>$dataCount, "err"=>$errCount] = $this->eloquentKendaraan->count($search);
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
        return $this->wrapper->responsePage($data, $meta, 'success get all kendaraan');
    }

    public function available(Request $request){
        $query = $request->query();
        $page = $query["page"] ?? 1;
        $size = $query["size"] ?? 10;
        $search = $query["search"] ?? '';
        ["data"=>$data, "err"=>$err] = $this->eloquentKendaraan->allAvailable((int)$page, (int)$size);
        if($err){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $err;
            return $this->wrapper->response($data, $message, $code);
        }
        ["data"=>$dataCount, "err"=>$errCount] = $this->eloquentKendaraan->countAvailable();
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
        return $this->wrapper->responsePage($data, $meta, 'success get all kendaraan');
    }


    public function store(Request $request)
    {
        //Validate data
        $data = $request->only(
            'name',
            'slug',
            'tahun_keluaran',
            'warna',
            'harga',
            'jenis_kendaraan',
            'tipe_suspensi',
            'tipe_transmisi',
            'mesin',
            'kapasitas_penumpang',
            'tipe',
            'status',
            'stok');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'slug' => 'required|string|unique:kendaraans',
            'tahun_keluaran' => 'required|integer|min:1900|max:2022',
            'warna' => 'required|string',
            'harga' => 'required|integer|min:0',
            'jenis_kendaraan' => 'required|in:MOBIL,MOTOR',
            'tipe_suspensi' => 'nullable|string',
            'tipe_transmisi' => 'nullable|string',
            'mesin' => 'required|string',
            'kapasitas_penumpang' => 'nullable|string',
            'tipe' => 'nullable|string',
            'status' => 'required|in:0,1',
            'stok' => 'required|integer|min:0'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->wrapper->response(null, $validator->messages(), Response::HTTP_BAD_REQUEST);
        }
        $document = [
            "kendaraan_id" => (string) Uuid::generate(),
            "name" => $request->name,
            "slug" => $request->slug,
            "tahun_keluaran" => $request->tahun_keluaran,
            "warna" => $request->warna,
            'harga' => $request->harga,
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'tipe_suspensi' => $request->tipe_suspensi,
            'tipe_transmisi' => $request->tipe_transmisi,
            'mesin' => $request->mesin,
            'kapasitas_penumpang' => $request->kapasitas_penumpang,
            'tipe' => $request->tipe,
            'status' => $request->status,
            'stok' => $request->stok,
            'kode_kendaraan' => "KD-".substr($request->jenis_kendaraan,0,1).number_format(microtime(true),0,'.','').$request->tahun_keluaran
        ];

        ['data'=>$data, 'err'=>$errCreate] = $this->eloquentKendaraan->create($document);
        if($errCreate){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errCreate;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success create kendaraan', Response::HTTP_CREATED);
    }


    public function show($id)
    {
        ['data'=>$data, 'err'=>$err] = $this->eloquentKendaraan->find($id);
        if($err){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $err;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success find data');
    }

    public function update($id, Request $request)
    {
        ['err'=>$err, 'data' => $data] = $this->eloquentKendaraan->find($id);
        if($err){
            return $this->wrapper->response(null, 'kendaraan not found', Response::HTTP_CONFLICT);
        }
        $document = [];
        $request->name ? $document['name'] = $request->nam: '';
        $request->slug ? $document['slug'] = $request->slug : '';
        $request->tahun_keluaran ? $document['tahun_keluaran'] = $request->tahun_keluaran : '';
        $request->warna ? $document['warna'] = $request->warna : '';
        $request->harga ? $document['harga'] = $request->harga : '';
        $request->jenis_kendaraan ? $document['jenis_kendaraan'] = $request->jenis_kendaraan : '';
        $request->tipe_suspensi ? $document['tipe_suspensi'] = $request->tipe_suspensi : '';
        $request->tipe_transmisi ? $document['tipe_transmisi'] = $request->tipe_transmisi : '';
        $request->mesin ? $document['mesin'] = $request->mesin : '';
        $request->kapasitas_penumpang ? $document['kapasitas_penumpang'] = $request->kapasitas_penumpang : '';
        $request->tipe ? $document['tipe'] = $request->tipe : '';
        $request->stok ? $document['stok'] = $request->stok : '';
        $request->status ? $document['status'] = $request->status : '';
        if(!$document){
            return $this->wrapper->response(null, 'no data to be update', Response::HTTP_BAD_REQUEST);
        }
        ['data'=>$data, 'err'=>$errUpdate] = $this->eloquentKendaraan->update($document, $id);
        if($errUpdate){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errUpdate;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success update kendaraan');
    }

    public function destroy($id)
    {
        ['err'=>$err, 'data' => $data] = $this->eloquentKendaraan->find($id);
        if($err){
            return $this->wrapper->response(null, 'kendaraan not found', Response::HTTP_CONFLICT);
        }
        ['data'=>$data, 'err'=>$errUpdate] = $this->eloquentKendaraan->delete($id);
        if($errUpdate){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errUpdate;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success delete kendaraan');
    }
}

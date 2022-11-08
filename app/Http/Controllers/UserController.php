<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\Hash;
use App\Helper\Wrapper;
use App\Repository\User\EloquentUserRepository;

class UserController extends Controller
{
    private $wrapper;

    public function __construct(Wrapper $wrapper, EloquentUserRepository $eloquentUser)
    {
        $this->wrapper = $wrapper;
        $this->eloquentUser = $eloquentUser;
    }

    public function index(Request $request)
    {
        $query = $request->query();
        $page = $query["page"] ?? 1;
        $size = $query["size"] ?? 10;
        $search = $query["search"] ?? '';
        ["data"=>$data, "err"=>$err] = $this->eloquentUser->all((int)$page, (int)$size, $search);
        if($err){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $err;
            return $this->wrapper->response($data, $message, $code);
        }
        ["data"=>$dataCount, "err"=>$errCount] = $this->eloquentUser->count($search);
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
        return $this->wrapper->responsePage($data, $meta, 'success get all user');
    }

    public function show($id)
    {
        ['data'=>$data, 'err'=>$err] = $this->eloquentUser->find($id);
        if($err){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $err;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success find data');
    }

   public function store(Request $request)
   {
        ['err'=>$err] = $this->eloquentUser->findByEmail($request->email);
        if(!$err){
            return $this->wrapper->response(null, 'email already registered', Response::HTTP_CONFLICT);
        }
        $document = [
            "user_id" => (string) Uuid::generate(),
            "fullname" => $request->fullname,
            "email" => $request->email,
            "no_hp" => $request->no_hp,
            "password" => Hash::make($request->password, ['rounds' => 12]),
        ];

        ['data'=>$data, 'err'=>$errCreate] = $this->eloquentUser->create($document);
        if($errCreate){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errCreate;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success register user', Response::HTTP_CREATED);
   }

   public function update($email, Request $request){
        ['err'=>$err, 'data' => $data] = $this->eloquentUser->findByEmail($email);
        if($err){
            return $this->wrapper->response(null, 'user not found', Response::HTTP_CONFLICT);
        }
        $document = [];
        $request->email ? $document["email"] = $request->email : '';
        $request->fullname ? $document["fullname"] = $request->fullname : '';
        $request->no_hp ? $document["no_hp"] = $request->no_hp : '';
        $request->password ? $document["password"] = Hash::make($request->password, ['rounds' => 12]) : '';
        if(!$document){
            return $this->wrapper->response(null, 'no data to be update', Response::HTTP_BAD_REQUEST);
        }
        ['data'=>$data, 'err'=>$errUpdate] = $this->eloquentUser->update($document, $data["0"]["user_id"]);
        if($errUpdate){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errUpdate;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success update user');
   }

   public function destroy($email){
        ['err'=>$err, 'data' => $data] = $this->eloquentUser->findByEmail($email);
        if($err){
            return $this->wrapper->response(null, 'user not found', Response::HTTP_CONFLICT);
        }
        ['data'=>$data, 'err'=>$errUpdate] = $this->eloquentUser->delete($data["0"]["user_id"]);
        if($errUpdate){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errUpdate;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response($data, 'success delete user');
    }
}

<?php

namespace App\Http\Controllers;

use JWTAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Webpatser\Uuid\Uuid;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use App\Helper\Wrapper;
use App\Repository\User\EloquentUserRepository;
use App\Repository\AuthLog\EloquentAuthLogRepository;

class UserController extends Controller
{
    private $wrapper;

    public function __construct(Wrapper $wrapper, EloquentUserRepository $eloquentUser, EloquentAuthLogRepository $eloquentAuthLog)
    {
        $this->wrapper = $wrapper;
        $this->eloquentUser = $eloquentUser;
        $this->eloquentAuthLog = $eloquentAuthLog;
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

    public function register(Request $request){
        //Validate data
        $data = $request->only('fullname', 'email', 'no_hp', 'password');
        $validator = Validator::make($data, [
            'fullname' => 'required|string',
            'email' => 'required|email|unique:users',
            'no_hp' => 'required|regex:/(08)[0-9]{9}/',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->wrapper->response(null, $validator->messages(), Response::HTTP_BAD_REQUEST);
        }

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

    public function authenticate(Request $request){
        //Validate data
        $credentials = $request->only('email', 'password');
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->wrapper->response(null, $validator->messages(), Response::HTTP_BAD_REQUEST);
        }

        ['err'=>$err, 'data'=> $data] = $this->eloquentUser->findByEmail($request->email);
        if($err){
            return $this->wrapper->response(null, 'email not registered', Response::HTTP_CONFLICT);
        }
        if(!Hash::check($request->password, $data[0]['password'])){
            return $this->wrapper->response(null, 'wrong password, please check again', Response::HTTP_BAD_REQUEST);
        }
        try {
            $exp = Carbon::now()->addDays(1)->timestamp;
            if (! $token = JWTAuth::attempt($credentials, ['exp' => $exp])) {
                return $this->wrapper->response(null, 'Login credentials are invalid.', Response::HTTP_BAD_REQUEST);
            }
        } catch (JWTException $e) {
            return $this->wrapper->response(null, 'Could not create token.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $this->eloquentAuthLog->create([
            "auth_log_id" => (string) Uuid::generate(),
            "user" => [
                "user_id" => $data[0]['user_id'],
                "fullname"=> $data[0]['fullname'],
                "email"=> $data[0]['email'],
                "no_hp"=> $data[0]['no_hp'],
                "is_login"=> $data[0]['is_login'],
                "token"=> $data[0]['token']
            ],
            "status" => "login",
        ]);
        ['data'=>$dataUpdate, 'err'=>$errUpdate] = $this->eloquentUser->update(["token"=>$token, "is_login" => true], $data["0"]["user_id"]);
        if($errUpdate){
            ['message'=>$message, 'code'=>$code, 'data' => $data] = $errUpdate;
            return $this->wrapper->response($data, $message, $code);
        }
        return $this->wrapper->response(["token" => $token, "expired" => $exp], 'success login', Response::HTTP_CREATED);
    }

    public function logout(Request $request){
        try {
            $user = $request->user();
            $this->eloquentAuthLog->create([
                "auth_log_id" => (string) Uuid::generate(),
                "user" => [
                    "user_id" => $user->user_id,
                    "fullname"=> $user->fullname,
                    "email"=> $user->email,
                    "no_hp"=> $user->no_hp,
                    "is_login"=> false,
                    "token"=> $user->token
                ],
                "status" => "logout"
            ]);
            ['data'=>$dataUpdate, 'err'=>$errUpdate] = $this->eloquentUser->update(["token"=>null, "is_login" => false], $user->user_id);
            if($errUpdate){
                ['message'=>$message, 'code'=>$code, 'data' => $data] = $errUpdate;
                return $this->wrapper->response($data, $message, $code);
            }
            JWTAuth::invalidate($user->token);
            return $this->wrapper->response(null, 'User has been logged out');
        } catch (JWTException $exception) {
            return $this->wrapper->response(null, 'Sorry, user cannot be logged out', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

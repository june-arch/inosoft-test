<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function show($slug)
   {
        $user = User::where('fullname',$slug)->project(['_id' => 0])->get();
        return response()->json(["result" => $user], 200);
   }

   public function store(Request $request)
   {
        $user = new User;

        $user->user_id = (string) Uuid::generate();
        $user->fullname = $request->fullname;
        $user->email = $request->email;
        $user->no_hp = $request->no_hp;
        $user->password = Hash::make($request->password, ['rounds' => 12]);
        $user->save();

        return response()->json(["result" => "ok"], 201);
   }
}

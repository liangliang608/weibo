<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    public function __construct() {
        $this->middleware('auth', [
            'except' =>['show', 'create','store','index']
        ]);
        $this->middleware('guest', [
            'only'=>['create']
        ]);
    }
    //用户注册
    public function create(){
        return view('users.create');
    }
    //个人信息页面
    public  function show(User $user) {
        return view('users.show', compact('user'));
    }

    public function store(Request $request){
        //验证用户信息
        $this->validate($request,[
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        //创建用户
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        //自动登录
        Auth::login($user);
        //登录成功
        session()->flash('success','欢迎，您将在这里开启一段新的旅程');
        return redirect()->route('users.show',[$user]);
    }

    //用户列表
    public  function index() {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function edit(User $user) {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request  $request){
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6',
        ]);
        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] =  bcrypt($request->password);
        }
        $user->update($data);
        session()->flash('success','个人资料更新成功');
//        $user->update([
//            'name'=>$request->name,
//            'password' => bcrypt($request->password),
//        ]);
        return redirect()->route('users.show', $user);
    }
    public function destroy(User  $user) {
        $user->delete();
        session()->flash('success','成功删除用户');
        return back();
    }

}

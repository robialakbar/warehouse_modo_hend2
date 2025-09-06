<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function users(Request $req){
        $search = $req->q;

        $users = DB::table('users')
                    ->leftJoin("city", "city.city_id", "=", "users.city_id");

        if(!empty($search)){
            $users = $users->where("username", "LIKE", "%".$search."%")
                        ->orWhere("name", "LIKE", "%".$search."%");
        }

        if($req->format == "json"){
            $users = $users->get();

            return response()->json($users);
        } else {
            $users = $users->paginate(50);

            return View::make("users")->with(compact("users"));
        }
    }

    public function user_save(Request $req){
        $user_id = $req->user_id;

        $req->validate([
            'fullname'      => 'required|min:3',
            'username'      => 'required|min:3|unique:users,username,'.$req->user_id,
            'password'      => 'required|min:6',
            'role'          => 'required',
            'city'       => 'required|exists:city,city_id',
            
        ],
        [
            'fullname.required'     => 'Fullname belum diisi!',
            'fullname.min'          => 'Fullname minimal 3 karakter!',
            'username.required'     => 'Username belum diisi!',
            'username.min'          => 'Username minimal 3 karakter!',
            'username.unique'       => 'Username telah digunakan!',
            'password.required'     => 'Password belum diisi!',
            'password.min'          => 'Password minimal 6 karakter!',
            'role.required'         => 'Role belum dipilih!',
            'city.required'         => 'Kota belum dipilih!',
            'city.exists'           => 'Kota tersedia!',
        ]);

        $data = [
            "name"      => $req->fullname,
            "username"  => $req->username,
            "password"  => Hash::make($req->password),
            "role"      => $req->role,
            "city_id"   => $req->city,
        ];

        if(empty($user_id)){
            $add = DB::table('users')->insertGetId($data);

            if($add){
                $req->session()->flash('success', "User baru berhasil ditambahkan.");
            } else {
                $req->session()->flash('error', "User baru gagal ditambahkan!");
            }
        } else {
            $edit = DB::table('users')->where("id", $user_id)->update($data);

            if($edit){
                $req->session()->flash('success', "User berhasil diubah.");
            } else {
                $req->session()->flash('error', "User gagal diubah!");
            }
        }
        
        return redirect()->back();
    }

    public function user_delete(Request $req){
        if($req->delete_role == 0){
            $sumAdmin = DB::table('users')->where("role", 0)->get()->count();

            if($sumAdmin == 1){
                $del = null;
            } else {
                $del = DB::table('users')->where("id", $req->delete_id)->delete();
            }
        } else {
            $del = DB::table('users')->where("id", $req->delete_id)->delete();
        }

        if($del){
            $req->session()->flash('success', "User berhasil dihapus.");
        } else {
            $req->session()->flash('error', "User gagal dihapus!");
        }

        return redirect()->back();
    }

    public function myaccount(){
        return View::make('my_account');
    }

    public function myaccount_update(Request $req){
        
        $req->validate([
            'fullname'      => 'required|min:3',
            
        ],
        [
            'fullname.required'     => 'Fullname belum diisi!',
            'fullname.min'          => 'Fullname minimal 3 karakter!',
        ]);

        $data = [
            "name"      => $req->fullname,
        ];

        if($req->fullname != Auth::user()->name){
            $update = DB::table('users')->where("id", Auth::user()->id)->update($data);

            if($update){
                $req->session()->flash("success", "Profile berhasil diperbarui.");
            } else {
                $req->session()->flash("error", "Profile gagal diperbarui!");
            }
        } else {
            $req->session()->flash("error", "Tidak ada perubahan!");
        }

        return redirect()->back();
    }

    public function myaccount_update_password(Request $req){
        
        $req->validate([
            'current_password'  => 'required',
            'new_password'      => 'required|confirmed|min:6'
            
        ],
        [
            'current_password.required' => 'Current Password belum diisi!',
            'new_password.required'     => 'New Password belum diisi!',
            'new_password.min'          => 'New Password minimal 6 karakter!',
            'new_password.confirmed'    => 'Confirm Password harus sama dengan New Password!',
        ]);

        if(!Hash::check($req->new_password, Auth::user()->password)){
            $data = [
                "password"      => Hash::make($req->new_password),
            ];

            $update = DB::table('users')->where("id", Auth::user()->id)->update($data);

            if($update){
                $req->session()->flash("success", "Password berhasil diganti.");
            } else {
                $req->session()->flash("error", "Password gagal diganti!");
            }
        } else {
            $req->session()->flash("error", "New Password tidak boleh sama dengan Current Password!");
        }

        return redirect()->back();
    }

    public function settings(){
        $settings = DB::table("settings")->get();
        return View::make('settings')->with(compact("settings"));
    }

    public function settings_update(Request $req){
        $data = [
            "invoice_footer"    => $req->invoice_footer,
            "invoice_show_logo" => $req->invoice_show_logo,
        ];

        foreach($data as $s => $val){
            $data = [
                "setting_value" => $val,
                
            ];
            $update = DB::table('settings')->where("setting_name", $s)->update($data);
            if($update){
                $req->session()->flash('success', $s." berhasil di-update.");
            }
        }

        return redirect()->back();
    }
}

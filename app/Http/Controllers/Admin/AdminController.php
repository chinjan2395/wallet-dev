<?php namespace App\Http\Controllers\admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Auth;
use App\Admin;
use DB;
use Validator;
use Redirect;
use IMS;

class AdminController extends Controller
{

    /*
    |------------------------------------------------------------------
    |Index page for login
    |------------------------------------------------------------------
    */
    public function index()
    {
        return View('admin.index');
    }

    /*
    |------------------------------------------------------------------
    |Login attempt,check username & password
    |------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (auth()->guard('admin')->attempt(['email' => $username, 'password' => $password])) {
            return Redirect::to(env('admin') . '/home')->with('message', 'Welcome ! Your are logged in now.');
        } else {
            return Redirect::to(env('admin') . '/login')->with('error', 'Username password not match')->withInput();
        }
    }

    /*
    |------------------------------------------------------------------
    |Homepage, Dashboard
    |------------------------------------------------------------------
    */
    public function home()
    {
        $admin = new Admin;

        $data = [

            'overviews' => $admin->overview()

        ];


        return View('admin.dashboard.home', $data);
    }

    /*
    |------------------------------------------------------------------
    |Logout
    |------------------------------------------------------------------
    */
    public function logout()
    {
        auth()->guard('admin')->logout();

        return Redirect::to(env('admin') . '/login')->with('message', 'Logout Successfully !');
    }

    /*
    |------------------------------------------------------------------
    |Account setting's page
    |------------------------------------------------------------------
    */
    public function setting()
    {
        $data = ['data' => auth()->guard('admin')->user()];

        return View('admin.dashboard.setting', $data);
    }

    /*
    |------------------------------------------------------------------
    |update account setting's
    |------------------------------------------------------------------
    */
    public function update(Request $Request)
    {
        //Validation
        $data = Admin::find(auth()->guard('admin')->user()->id);

        if ($data->validate($Request->all())) {
            return redirect(env('admin') . '/setting')->withErrors($data->validate($Request->all()))->withInput();
        } elseif ($data->matchPassword($Request->get('password'))) {
            return redirect(env('admin') . '/setting')->with('error', 'Sorry ! Your Current Password Not Match.');
        } else {
            $data->name = $Request->get('name');
            $data->email = $Request->get('email');
            $data->phone = $Request->get('phone');

            //if password changed
            if ($Request->get('new_password')) {
                $data->password = bcrypt($Request->get('new_password'));
            }

            $data->save();

            return Redirect::to(env('admin') . '/setting')->with('message', 'Account Setting Updated Successfully.');
        }
    }

}
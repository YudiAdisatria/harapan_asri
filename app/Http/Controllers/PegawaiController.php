<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Penghuni;
use App\Models\Role_user;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function __construct()
    {
        $this->User = new User();
        $this->Role_User = new Role_User();
    }

    public function kepegawaian()
    {
        $return =  [
            'role' => $this->Role_User->get_role(),
            'user' => $this->User->get_user()
        ];

        return view('pegawai.index')->with($return);
    }

    public function kepegawaianredirect()
    {
        return redirect('kepegawaian/');
    }

    public function tambahPegawai()
    {
        $role =  ['role' => $this->Role_User->get_role()];
        // DD($role);
        return view('pegawai.tambah')->with($role);
    }

    public function prosesTambahPegawai(Request $request)
    {
        // DD($request);
        // return $request->foto->getClientOriginalExtension();
        $message = [
            'required' => 'Harap isi :attribute',
            'same' => ':other tidak sesuai dengan :attribute',
            'min' => ':attribute minimal :min karakter',
            'max' => ':attribute minimal :max karakter',
            'integer' => ':attribute hanya boleh karakter angka saja',
            'date' => ':attribute tidak valid',
            'nik.regex' => ':attribute hanya boleh angka saja',
            'mimes' => ':attribute hanya boleh jpg, jpeg atau png'
        ];

        $this->validate($request, [
            // 'username' => 'required',
            'nama' => 'required',
            'nik' => 'required|regex:/^[0-9]+$/',
            'tgl_lahir' => 'required|date',
            'gender' => 'required',
            'agama' => 'required',
            'alamat' => 'required',
            'notelp' => 'required',
            'mulaimasuk' => 'required|date',
            // 'ijazah' => 'required',
            // 'title' => 'required',
            // 'status_kepegawaian' => 'required',
            // 'pelatihan' => 'required',
            // 'foto' => 'required|mimes:jpg,jpeg,png'

        ], $message);

        if (empty($request->foto)) {
            $request['foto'] = null;
        }

        if (empty($request->status_kepegawaian)) {
            $request['status_kepegawaian'] = null;
        }

        if (empty($request->pelatihan)) {
            $request['pelatihan'] = null;
        }

        $error_tambah = $this->User->tambah($request);
        if ($error_tambah['error_tambah'] != null) {
            $role =  ['role' => $this->Role_User->get_role()];
            $request->flash();
            return redirect()->back()->with($error_tambah, $role);
        } else {
            $message_success = ['message_success' => ['Data Pegawai Berhasil ditambahkan']];

            return redirect('/pegawai')->with($message_success);
        }

        // return $extension;
    }

    public function ubahpassword($id)
    {
        return view('pegawai.ubahpassword')->with(['data' => $this->User->get_ganti_password($id)]);
    }

    public function prosesUbahPassword(Request $request)
    {
        $message = [
            'required' => 'Harap isi :attribute',
            'same' => ':other tidak sesuai dengan :attribute',
            'min' => ':attribute minimal :min karakter',
        ];
        $this->validate($request, [
            'id' => 'required',
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ], $message);

        $error_ubahpassword = $this->User->ganti_password($request);
    }

    public function prosesUbahPassword1(Request $request)
    {
        $message = [
            'required' => 'Harap isi :attribute',
            'same' => ':other tidak sesuai dengan :attribute',
            'min' => ':attribute minimal :min karakter',
        ];
        $this->validate($request, [
            'id' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ], $message);

        $error_ubahpassword = $this->User->ganti_password($request);

        if ($error_ubahpassword['error_ubahpassword'] != null) {
            $username = ['username' => $this->User->get_ganti_password()];
            $request->flash();
            return redirect()->back()->with($error_ubahpassword, $username);
        } else {
            $u = session()->get('auth_wlha.0')->username;
            $i = session()->get('auth_wlha.0')->id;

            if ($u == $request->id_atau_username) {
                // return redirect('/auth/logout');
                return redirect('/login/error/2/null--');
            } else if ($i == $request->id_atau_username) {
                // return redirect('/auth/logout');
                return redirect('/login/error/2/null--');
            } else {
                $message_success = ['message_success' => ['Password Berhasil ditambahkan']];
                return redirect('/admin/kepegawaian')->with($message_success);
            }
        }
    }

    public function detail(Request $request)
    {
        $return =
            $this->User->get_detail($request->id);
        return $return;
    }

    public function ubahPegawai($id)
    {
        $data = [
            'role' => $this->Role_User->get_role(),
            'user' => $this->User->get_detail($id)
        ];

        return view('pegawai.ubah')->with($data);
    }

    public function prosesUbahPegawai(Request $request)
    {
        // return $request->foto->getClientOriginalExtension();
        $message = [
            'required' => 'Harap isi :attribute',
            'same' => ':other tidak sesuai dengan :attribute',
            'min' => ':attribute minimal :min karakter',
            'max' => ':attribute minimal :max karakter',
            'integer' => ':attribute hanya boleh karakter angka saja',
            'date' => ':attribute tidak valid',
            'nik.regex' => ':attribute hanya boleh angka saja',
            'mimes' => ':attribute hanya boleh jpg, jpeg atau png'
        ];

        $this->validate($request, [
            'nama' => 'required',
            'nik' => 'required|regex:/^[0-9]+$/',
            'tgl_lahir' => 'required|date',
            'gender' => 'required',
            'agama' => 'required',
            'alamat' => 'required',
            'notelp' => 'required',
            'mulaimasuk' => 'required|date',
            // 'ijazah' => 'required',
            // 'status_kepegawaian' => 'required',
            // 'pelatihan' => 'required',
            'foto' => 'mimes:jpg,jpeg,png'

        ], $message);

        if (empty($request->foto)) {
            $imagename = null;
            $request['foto'] = null;
        } else {
            $imagename = $request['id'] . '.' . $request->foto->extension();
            $request->foto->move(public_path('photos'), $imagename);
        }


        if (empty($request->status_kepegawaian)) {
            $request['status_kepegawaian'] = null;
        }

        if (empty($request->pelatihan)) {
            $request['pelatihan'] = null;
        }


        $data = $request->except(['_token']);
        $data['foto'] = $imagename;

        $error_update = $this->User->edit($data);
        if ($error_update['error_update'] != null) {
            $role =  ['role' => $this->Role_User->get_role()];
            $request->flash();
            return redirect()->back()->with($error_update, $role);
        } else {
            $message_success = ['message_success' => ['Data Pegawai Berhasil diubah']];

            return redirect('/pegawai')->with($message_success);
        }

        // return $extension;
    }

    public function getEdit(Request $request)
    {
        $return =  [
            'role' => $this->Role_User->get_role(),
            'user' => $this->User->get_user_detail($request->id)
        ];
        return $return;
    }

    public function prosesEdit(Request $request)
    {
        $update = $this->User->edit($request);
        return $update;
    }
}

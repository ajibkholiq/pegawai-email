<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;

class AdminCntrl extends Controller
{
    public function index(request $request){
        $nama = $request->session()->get('nama');
        if ($nama == null ){return redirect('/')->with(['gagal'=>'ANDA BELUM LOGIN']);}
        $admin = DB::table('pegawais')->where('email',$nama)->first();
        $count = DB::table('pesans')->where('penerima',$nama)->where('read','=','1')->count();

        $data = Pegawai::all();
        return view('admin.dashboard',compact('admin','data','count'));
        }

    public function create(request $request){
        $nama = $request->session()->get('nama');
        if ($nama == null ){return redirect('/')->with(['gagal'=>'ANDA BELUM LOGIN']);}
        $admin = DB::table('pegawais')->where('email',$nama)->first();
        $count = DB::table('pesans')->where('penerima',$nama)->where('read','=','1')->count();

        return view('admin.tambahPegawai',compact('admin','count'));
    }
    public function store( request $request){
        $nama = $request->session()->get('nama');
        if ($nama == null ){return redirect('/')->with(['gagal'=>'ANDA BELUM LOGIN']);}
        
        $this->validate($request,[
            'nip' => 'required',
            'email' => 'required',
            'password' => 'required',
            'nama' => 'required',
            'alamat' => 'required',
            'nohp' => 'required',
            'jenkel' => 'required',
        ]);
        $level= 3;
        $tambah = Pegawai::create([
            'nip'=> $request->nip,
            'email'=> $request->email,
            'password'=> $request->password,
            'nama'=> $request->nama,
            'jenisKelamin'=> $request->jenkel,
            'alamat'=> $request->alamat,
            'nohp'=> $request->nohp
        
        ]);

        if ($tambah){
            return redirect('admin');
        }
    }
    public function edit ($id){
        $data = Pegawai::findOrFail($id);
        return view('admin.edit',compact('data'));
    }
    public function update(Request $request ,$id){
        $data = Pegawai::findOrFail($id);
        $update = $data->update([
            'nip'=> $request->nip,
            'email'=> $request->email,
            'password'=> $request->password,
            'nama'=> $request->nama,
            'jenisKelamin'=> $request->jenkel,
            'alamat'=> $request->alamat,
            'nohp'=> $request->nohp ]);

        if ($update){
            return redirect('admin');
        }
    }
    public function verify($id){
        $verify = DB::table('pegawais')
              ->where('id', $id)
              ->update(['level' => 2]);
        if($verify){
        return redirect('admin');}

    }
    public function destroy($id){
        $data = Pegawai::findOrFail($id);
        $data->delete();
        return redirect()->route('admin.index');
    }

}

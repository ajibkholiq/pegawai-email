<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\controler;
use App\Http\Controllers\PegawaiCntrl;
use App\Http\Controllers\AdminCntrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\Pesan;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('login');
    });
Route::get('/register', function (){
    return view('pegawai.register');
    });
Route::post('/regist', function (request $request){
    $tambah = Pegawai::create([
        'nip'=> $request->nip,
        'email'=> $request->email,
        'password'=> $request->password,
        'nama'=> $request->nama,
        'jenisKelamin'=> $request->jenkel,
        'alamat'=> $request->alamat,
        'nohp'=> $request->nohp,
    
    ]);

    if ($tambah){
        return redirect('/')->with(['pesan'=>'berhasil registrasi hubungi admin untuk memverifikasi']);
    }});
Route::post('/validate', function (Request $request){
    $email = $request->email;
    $password = $request->password;
    $data = DB::table('pegawais')->where('email',$email)->first();
    if (!$data) {
        return redirect('/')->with(['gagal'=>'USERNAME DAN PASSWORD TIDAK ADA']);;
    }
    else{
    if ($data->level==1 && $data->password == $password ){
        $request->session()->put('nama', $data->email);
        return redirect()->route('admin.index');
    }
    else if ($data->level==2 && $data->password == $password ){
        $request->session()->put('nama', $data->email);
        return redirect('pegawai');
    }
    else{
        $request->session()->put('nama', $data->email);
        return redirect('/')->with(['gagal'=>'AKUN BELUM DIVERIFIKASI ' ,
    'pesan'=>'silahkan hubungi admin']);}
    }});

Route::get('/admin/{id}/verify',[AdminCntrl::class , 'verify']);
Route::get('/pegawai', function (request $request){
    $nama = $request->session()->get('nama');
    if ($nama == null ){return redirect('/')->with(['gagal'=>'ANDA BELUM LOGIN']);}
    $pegawai = DB::table('pegawais')->where('email',$nama)->first();
    $count = DB::table('pesans')->where('penerima',$nama)->where('read','=','1')->count();
    return view('pegawai.profile',compact('pegawai','count'));});
Route::get('/compose',function (request $request){
    $nama = $request->session()->get('nama');
    if ($nama == null ){return redirect('/')->with(['gagal'=>'ANDA BELUM LOGIN']);}
    $pegawai = DB::table('pegawais')->where('email',$nama)->first();
    $count = DB::table('pesans')->where('penerima',$nama)->where('read','=','1')->count();

    return view('pegawai.compose',compact('pegawai','count'));});
Route::post('kirim',function (request $request){
    $nama = $request->session()->get('nama');
    if ($nama == null ){return redirect('/')->with(['gagal'=>'ANDA BELUM LOGIN']);}
    $result = Pesan::create([
        'pengirim'=>$nama,
        'penerima'=>$request->penerima,
        'subject'=> $request->subject,
        'content'=> $request->content
    ]);
    $pegawai = DB::table('pegawais')->where('email',$nama)->first();
    if ($pegawai->level != '1'){
       return redirect('pegawai');
    }
    return redirect('admin');
    

    }
    );
Route::get('/inbox', function(request $request){
    $nama = $request->session()->get('nama');
    if ($nama == null ){return redirect('/')->with(['gagal'=>'ANDA BELUM LOGIN']);}
    $inbox = DB::table('pesans')->where('penerima',$nama)->get();
    $pegawai = DB::table('pegawais')->where('email',$nama)->first();
    $count = DB::table('pesans')->where('penerima',$nama)->where('read','=','1')->count();
    return view ('pegawai.inbox' , compact('pegawai','count','inbox'));
    
    });
Route::get('/sent',function(request $request){
    $nama = $request->session()->get('nama');
    if ($nama == null ){return redirect('/')->with(['gagal'=>'ANDA BELUM LOGIN']);}
    $inbox = DB::table('pesans')->where('pengirim',$nama)->get();
    $pegawai = DB::table('pegawais')->where('email',$nama)->first();
    $count = DB::table('pesans')->where('penerima',$nama)->where('read','=','1')->count();
    return view ('pegawai.sent' , compact('pegawai','count','inbox'));});
Route::get('/read/{id}',function(request $request , $id){
    $nama = $request->session()->get('nama');
    if ($nama == null ){return redirect('/')->with(['gagal'=>'ANDA BELUM LOGIN']);}
    $inbox = DB::table('pesans')->where('id',$id)->first();
    $pegawai = DB::table('pegawais')->where('email',$nama)->first();
    $count = DB::table('pesans')->where('penerima',$nama)->where('read','=','1')->count();
    $read= Pesan::find($id);
    $read->update(['read'=> '0']);
    return view ('pegawai.read' , compact('pegawai','inbox','count'));});

Route::delete('/delete/{id}',function ( request $request ,$id){
    $nama = $request->session()->get('nama');
    $pesan = Pesan::find($id);
    $pesan->delete();
    $pegawai = DB::table('pegawais')->where('email',$nama)->first();

    if ($pegawai->level != '1'){
       return redirect('pegawai');
    }
    return redirect('admin');

});
Route::get('/logout', function (request $request){
    $request->session()->flush();
    return redirect('/');});
Route::resource('admin',AdminCntrl::class);



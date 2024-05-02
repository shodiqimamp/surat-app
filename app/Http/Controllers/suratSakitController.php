<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use SnappyImage;
use PDF;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class suratSakitController extends Controller
{
    public function getDataSakit($no_rawat) {
        $sql = "SELECT reg_periksa.kd_dokter,reg_periksa.no_rkm_medis, reg_periksa.no_rkm_medis, pasien.nm_pasien, dokter.nm_dokter,
                pasien.tgl_lahir, pasien.jk, pasien.pnd, pasien.pekerjaan, pasien.alamatpj, reg_periksa.status_lanjut,
                concat(pasien.alamat, ', ',kelurahan.nm_kel,', KEC ',kecamatan.nm_kec, ', ', kabupaten.nm_kab) as alamat,
                suratsakit.tanggalawal, suratsakit.tanggalakhir, suratsakit.lamasakit,
                COALESCE((SELECT dokter.nm_dokter FROM dokter WHERE dokter.kd_dokter = dpjp_ranap.kd_dokter), '-') AS dpjp
                FROM reg_periksa
                INNER JOIN pasien ON pasien.no_rkm_medis = reg_periksa.no_rkm_medis
                INNER JOIN dokter ON dokter.kd_dokter = reg_periksa.kd_dokter
                INNER JOIN suratsakit ON suratsakit.no_rawat = reg_periksa.no_rawat
                LEFT JOIN dpjp_ranap ON dpjp_ranap.no_rawat = reg_periksa.no_rawat
                inner join kelurahan on kelurahan.kd_kel = pasien.kd_kel
                inner join kecamatan on kecamatan.kd_kec = pasien.kd_kec
                inner join kabupaten on kabupaten.kd_kab = pasien.kd_kab
                WHERE reg_periksa.no_rawat = :no_rawat";
        $bindings = ['no_rawat' => $no_rawat];
        $result = DB::select($sql, $bindings);

        $data = [];

        // Pastikan ada hasil yang ditemukan sebelum mengakses kolom kd_dokter
        if (!empty($result)) {
            $no_rm = $result[0]->no_rkm_medis;
            $nm_dokter = $result[0]->nm_dokter;
            $nm_pasien = $result[0]->nm_pasien;
            $dpjp_ranap = $result[0]->dpjp;
            $status = $result[0]->status_lanjut;

            if($dpjp_ranap === "-"){
                $dpjp = $nm_dokter;
            }else{
                $dpjp = $dpjp_ranap;
            }

            $tanggal_lahir = strtotime($result[0]->tgl_lahir);
            $tahun_lahir = date('Y', $tanggal_lahir);
            $umur_pasien = date('Y') - $tahun_lahir;

            $jk = $result[0]->jk;
            $pekerjaan = $result[0]->pekerjaan;
            $pendidikan = $result[0]->pnd;

            $alamat = $result[0]->alamat;

            $tglawal = new DateTime($result[0]->tanggalawal);
            $tglakhir = new DateTime($result[0]->tanggalakhir);
            $lama = $tglawal->diff($tglakhir)->days + 1;

            $tgl_terbit = Carbon::now()->format('d M Y');

            $data = [
                'no_rawat' => $no_rawat,
                'no_rm' => $no_rm,
                'nm_dokter' => $nm_dokter,
                'nm_pasien' => $nm_pasien,
                'umur' => $umur_pasien,
                'jk' => $jk,
                'pekerjaan' => $pekerjaan,
                'pendidikan' => $pendidikan,
                'status' => $status,
                'alamat' => $alamat,
                'tglawal' => $tglawal->format('d-m-Y'),
                'tglakhir' => $tglakhir->format('d-m-Y'),
                'lama' => $lama,
                'tgl_terbit' => $tgl_terbit,
                'dpjp' => $dpjp
            ];
        } else {
            // Handle jika data tidak ditemukan
            $data['error'] = 'Data not found';
        }

        return $data;
    }


    public function getDataRanap($no_rawat) {
        $sql = "SELECT reg_periksa.kd_dokter, reg_periksa.no_rkm_medis, pasien.nm_pasien, dokter.nm_dokter, pasien.tgl_lahir, pasien.jk,
                pasien.pnd, pasien.pekerjaan, pasien.alamatpj, reg_periksa.status_lanjut, CONCAT(pasien.alamat, ' ', kecamatan.nm_kec, ' ', kabupaten.nm_kab) AS alamat,
                kamar_inap.tgl_masuk, kamar_inap.tgl_keluar, IFNULL(surat_keterangan_rawat_inap.tanggalawal, '') AS tanggalawal,IFNULL(surat_keterangan_rawat_inap.tanggalakhir, '') AS tanggalakhir,
                COALESCE((SELECT billing.tgl_byr FROM billing WHERE billing.no_rawat = reg_periksa.no_rawat LIMIT 1), '-') AS tgl_bayar,
                COALESCE((SELECT dokter.nm_dokter FROM dokter WHERE dokter.kd_dokter = dpjp_ranap.kd_dokter), '') AS dpjp
                FROM reg_periksa
                INNER JOIN pasien ON pasien.no_rkm_medis = reg_periksa.no_rkm_medis
                INNER JOIN dokter ON dokter.kd_dokter = reg_periksa.kd_dokter
                INNER JOIN kamar_inap ON kamar_inap.no_rawat = reg_periksa.no_rawat
                left JOIN surat_keterangan_rawat_inap ON surat_keterangan_rawat_inap.no_rawat = reg_periksa.no_rawat
                LEFT JOIN dpjp_ranap ON dpjp_ranap.no_rawat = reg_periksa.no_rawat
                INNER JOIN kecamatan ON kecamatan.kd_kec = pasien.kd_kec
                INNER JOIN kabupaten ON kabupaten.kd_kab = pasien.kd_kab
                WHERE reg_periksa.no_rawat = :no_rawat";
        $bindings = ['no_rawat' => $no_rawat];
        $result = DB::select($sql, $bindings);

        $data = [];

        // Pastikan ada hasil yang ditemukan sebelum mengakses kolom kd_dokter
        if (!empty($result)) {
            $no_rm = $result[0]->no_rkm_medis;
            $nm_dokter = $result[0]->nm_dokter;
            $nm_pasien = $result[0]->nm_pasien;
            $dpjp_ranap = $result[0]->dpjp;
            $status = $result[0]->status_lanjut;

            if($dpjp_ranap === "-"){
                $dpjp = $nm_dokter;
            }else{
                $dpjp = $dpjp_ranap;
            }

            $tanggal_lahir = strtotime($result[0]->tgl_lahir);
            $tahun_lahir = date('Y', $tanggal_lahir);
            $umur_pasien = date('Y') - $tahun_lahir;

            $jk = $result[0]->jk;
            $pekerjaan = $result[0]->pekerjaan;
            $pendidikan = $result[0]->pnd;

            $alamat = $result[0]->alamat;

            $tglawal = new DateTime($result[0]->tanggalawal);
            $tglakhir = new DateTime($result[0]->tanggalakhir);

            $tgl_terbit = Carbon::now()->format('d M Y');
            $tgl_billing = $result[0]->tgl_bayar;
            $tgl_masuk = $result[0]->tgl_masuk;
            $tgl_keluar = $result[0]->tgl_keluar;

            $data = [
                'no_rawat' => $no_rawat,
                'no_rm' => $no_rm,
                'nm_dokter' => $nm_dokter,
                'nm_pasien' => $nm_pasien,
                'umur' => $umur_pasien,
                'jk' => $jk,
                'pekerjaan' => $pekerjaan,
                'pendidikan' => $pendidikan,
                'status' => $status,
                'alamat' => $alamat,
                'tglawal' => $tglawal->format('d-m-Y'),
                'tglakhir' => $tglakhir->format('d-m-Y'),
                'tgl_terbit' => $tgl_terbit,
                'dpjp' => $dpjp,
                'tgl_bayar' => $tgl_billing,
                'tgl_masuk' => $tgl_masuk,
                'tgl_keluar' => $tgl_keluar
            ];
        } else {
            // Handle jika data tidak ditemukan
            $data['error'] = 'Data not found';
        }

        return $data;
    }

    public function showView($tgl_bayar, $data, $no_surat){

        if ($tgl_bayar === '-') {
            $html = view('surat.suratKetRanap', compact('data'))->render();
        } else {
            $html = view('surat.suratKetDiRawat', compact('data'))->render();

            DB::table('surat_keterangan_rawat_inap')
            ->where('no_surat', $no_surat)
            ->update(['tanggalakhir' => $tgl_bayar]);

        }

        return $html;

    }
    public function getNoRawat($no_rm){
        $no_rawat_object = DB::table('reg_periksa')
            ->select('no_rawat')
            ->where('no_rkm_medis', $no_rm)
            ->where('status_lanjut', 'Ranap')
            ->orderByDesc('tgl_registrasi')
            ->first();

        return $no_rawat_object ? $no_rawat_object->no_rawat : null;
    }

    public function cekDoubleData($no_rawat, $tglawal, $tgl_akhir)
    {
        $no_surat = 'SKR' . str_replace('/', '', $no_rawat);

        $sql = "SELECT * FROM surat_keterangan_rawat_inap WHERE no_surat LIKE ? AND no_rawat = ? AND tanggalawal = ?";

        // Menggunakan DB::select dengan placeholder
        $result = DB::select($sql, [$no_surat ,$no_rawat, $tglawal]);

        // Menghitung jumlah baris yang dikembalikan
        $count = count($result);

        return $count;
    }


    public function cekDoubleDataSakit($no_rawat)
    {
        $no_surat = 'SKS' . str_replace('/', '', $no_rawat);

        $sql = "SELECT * FROM suratsakit WHERE no_surat LIKE ? AND no_rawat = ?";

        // Menggunakan DB::select dengan placeholder
        $result = DB::select($sql, [$no_surat ,$no_rawat]);

        // Menghitung jumlah baris yang dikembalikan
        $count = count($result);

        return $count;
    }

    public function SuratSakit($no_rw)
    {
        $no_rawat = str_replace('&', '/', $no_rw);

        // Panggil fungsi getData untuk mendapatkan data pasien
        $data = $this->getDataSakit($no_rawat);
        try {

            $no_surat = 'SKS' . str_replace('/', '', $no_rawat);

            $cek_data = $this->cekDoubleDataSakit($no_rawat);

            if ($cek_data === 0) {
                try {
                    DB::table('suratsakit')
                        ->where('no_rawat', $no_rawat)
                        ->update(['no_surat' => $no_surat]);

                } catch (\Throwable $th) {
                    // Tangani exception dengan memberikan pesan yang jelas
                    throw new \Exception('Error rendering HTML: ' . $th->getMessage());
                }

            }
            // Pastikan variabel $data diteruskan ke dalam view

            $html = view('surat.suratSakit', compact('data'));

            $image = SnappyImage::loadHTML($html)
                                ->setOption('format', 'jpg') // Format gambar
                                ->setOption('encoding', 'utf-8') // Encoding karakter
                                ->setOption('quality', 100) // Kualitas gambar (0-100)
                                ->setOption('enable-local-file-access', true)
                                ->inline(); // Tampilkan gambar langsung di browser

            // Mengembalikan gambar JPG sebagai response
            return $image;

        } catch (\Throwable $th) {
            throw new \Exception('Error rendering HTML: ' . $th->getMessage());
        }

    }

    public function SuratKetRanap($no_rw)
    {
        $no_rawat = str_replace('&', '/', $no_rw);

        // Panggil fungsi getData untuk mendapatkan data pasien
        $data = $this->getDataRanap($no_rawat);

        $no_surat = 'SKR' . str_replace('/', '', $no_rawat);

        try {
            $tgl_bayar = $data['tgl_bayar'];
            $tgl_awal = $data['tgl_masuk'];
            // $tgl_akhir = ($tgl_bayar === '-') ? null : $tgl_bayar;
            try {
                $html = $this->showView($tgl_bayar, $data, $no_surat);

            } catch (\Throwable $th) {
                // Tangani exception dengan memberikan pesan yang jelas
                throw new \Exception('Error rendering HTML: ' . $th->getMessage());
            }

        } catch (\Throwable $th) {
            // Tangani exception dengan memberikan pesan yang jelas
            throw new \Exception('Error rendering HTML: ' . $th->getMessage());
        }

        // Konversi HTML menjadi gambar JPG dengan SnappyImage
        $image = SnappyImage::loadHTML($html)
                            ->setOption('format', 'jpg') // Format gambar
                            ->setOption('encoding', 'utf-8') // Encoding karakter
                            ->setOption('quality', 100) // Kualitas gambar (0-100)
                            ->setOption('enable-local-file-access', true)
                            ->inline(); // Tampilkan gambar langsung di browser

        // Mengembalikan gambar JPG sebagai response
        return $image;
    }

    public function SuratKetDiRawat($no_rm)
    {
        $no_rawat = $this->getNoRawat($no_rm);

        // Panggil fungsi getData untuk mendapatkan data pasien
        $data = $this->getDataRanap($no_rawat);

        try {
            $null_date = new DateTime('0000-00-00');
            $tgl_bayar = $data['tgl_bayar'];
            $tgl_awal = $data['tgl_masuk'];
            $tgl_akhir = ($tgl_bayar === '-') ? $null_date : $tgl_bayar;
            $no_surat = 'SKR' . str_replace('/', '', $no_rawat);

            $cek_data = $this->cekDoubleData($no_rawat, $tgl_awal, $tgl_akhir);

            if ($cek_data === 0) {
                try {

                    DB::table('surat_keterangan_rawat_inap')->insert([
                        'no_surat' => $no_surat,
                        'no_rawat' => $data['no_rawat'],
                        'tanggalawal' => $tgl_awal,
                        'tanggalakhir' => $tgl_akhir,
                    ]);

                    $html = $this->showView($tgl_bayar, $data, $no_surat);

                } catch (\Throwable $th) {
                    // Tangani exception dengan memberikan pesan yang jelas
                    throw new \Exception('Error rendering HTML: ' . $th->getMessage());
                }

            } else {
                $html = $this->showView($tgl_bayar, $data, $no_surat);
            }

        } catch (\Throwable $th) {
            // Tangani exception dengan memberikan pesan yang jelas
            throw new \Exception('Error rendering HTML: ' . $th->getMessage());
        }

        $image = SnappyImage::loadHTML($html)
        ->setOption('format', 'jpg') // Format gambar
        ->setOption('encoding', 'utf-8') // Encoding karakter
        ->setOption('quality', 100) // Kualitas gambar (0-100)
        ->setOption('enable-local-file-access', true)
        ->inline(); // Tampilkan gambar langsung di browser

        // Mengembalikan gambar JPG sebagai response
        return $image;
    }


}

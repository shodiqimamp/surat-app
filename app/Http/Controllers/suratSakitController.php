<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use SnappyImage;
use PDF;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class suratSakitController extends Controller
{
    public function getData($no_rawat) {
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

            $tgl_terbit = date('d F Y');

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

    public function SuratSakit($no_rw)
    {
        $no_rawat = str_replace('&', '/', $no_rw);

        // Panggil fungsi getData untuk mendapatkan data pasien
        $data = $this->getData($no_rawat);

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
    }

    public function SuratKetRanap($no_rw)
    {
        $no_rawat = str_replace('&', '/', $no_rw);

        // Panggil fungsi getData untuk mendapatkan data pasien
        $data = $this->getData($no_rawat);

        // Pastikan variabel $data diteruskan ke dalam view
        $html = view('surat.suratKetRanap', compact('data'));

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
}

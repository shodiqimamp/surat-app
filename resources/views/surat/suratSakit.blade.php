<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div style="width: 100%; margin-top: 50px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 148px;">
                    <img style="width: 138%;" src="{{ public_path('Logo-RSPW.png') }}">
                </td>
                <td style="">
                    <div style="font-size: 19px; font-weight: bold; margin-bottom: 5px; text-align: center;">
                        RUMAH SAKIT PUTRA WASPADA
                    </div>
                    <div style="font-size: 16px; text-align: center;">
                        Jl. Jayengkusuma No. 66 RT. 02 RW. 06 Ds. Ngujang <br>Kec. Kedungwaru Tulungagung - 66228<br>
                        Telp (0355) 335550, Fax. 322522 <br>
                        email : rsputrawaspada@yahoo.com
                    </div>
                </td>
                <td style="width: 130px;">
                    <img style="width: 100%;" src="{{ public_path('paripurna.png') }}">
                </td>
            </tr>
        </table>
    </div>
    <hr class="garis1"/>
    <div style="display: flex; align-items: center; justify-content: center;">
        <div class="content">
            <h3 style="text-align: center;">SURAT KETERANGAN DOKTER</h3>
            <p>Yang bertanda tangan dibawah ini : </p>
            <p>Nama&nbsp; &nbsp; &nbsp;<span style="margin-left: 35px;">: {{ $data['dpjp'] }} </span></p>
            <p>Dengan ini menerangkan bahwa telah melakukan pemeriksaan kesehatan pada:</p>
                <table>
                    <tr class="data-pasien">
                        <td>Nama</td>
                        <td style="padding-left: 10px;">:</td>
                        <td>{{$data['nm_pasien']}}</td>
                    </tr>
                    <tr class="data-pasien">
                        <td>Umur</td>
                        <td style="padding-left: 10px;">:</td>
                        <td>{{ $data['umur'] == 0 ? '< 1' : $data['umur'] }} Tahun <span style="padding-left: 150px;">Jenis Kelamin : {{ $data['jk'] === 'L' ? 'Laki-Laki' : 'Perempuan' }}</span></td>
                    </tr>
                    <tr class="data-pasien">
                        <td>Pendidikan</td>
                        <td style="padding-left: 10px;">:</td>
                        <td>{{$data['pendidikan']}}</td>
                    </tr>
                    <tr class="data-pasien">
                        <td>Pekerjaan</td>
                        <td style="padding-left: 10px;">:</td>
                        <td>{{$data['pekerjaan']}}</td>
                    </tr>
                    <tr class="data-pasien">
                        <td>Alamat</td>
                        <td style="padding-left: 10px;">:</td>
                        <td>{{$data['alamat']}}</td>
                    </tr>
                </table>
            <p style="line-height: 2; margin-top: 1px;">Setelah dilakukan pemeriksaan, yang bersangkutan dinyatakan <span style="font-weight: bold;">SAKIT</span> dan tidak bisa untuk melakukan kegiatan sesuai dengan pekerjaan/pendidikannya.
                Sehubungan dengan hal tersebut perlu istirahat selama <span style="border-bottom: 2px solid black;">{{$data['lama']}} hari</span>. Mulai tanggal <span style="border-bottom: 2px solid black;";>{{$data['tglawal']}}</span>
                s/d <span style="border-bottom: 2px solid black;">{{$data['tglakhir']}}</span>.</p>
            <p style="margin-bottom: 1px;">Demikian surat keterangan ini dibuat untuk diketahui dan dipergunakan sebagaimana mestinya.</p>
            <br>
        </div>
        <div style=" float:right">
            <div style="width: 300px;text-align: center;">
                <p>Tulungagung, {{$data['tgl_terbit']}}</p>
                <p style="margin: 0;">Dokter Pemeriksa</p>
                <img style="margin: 5px;" src="data:image/png;base64,{{ base64_encode(QrCode::format('png')->size(160)->generate('Dikeluarkan di Rumah Sakit Putra Waspada Telp (0355)335550, Fax. 322522 ditandatangani secara elektronik oleh ' . $data["dpjp"] . ' pada tanggal ' . $data["tgl_terbit"])) }}" />
                <p style="margin: 0;">{{ $data['dpjp'] }}</p>
            </div>
        </div>
    </div>
</body>

<style type="text/css">
    body {
        font-family: Arial;
        width: 210mm;
        height: 140mm;
        margin: 0;
        padding: 0px 0px 0px 120px;
    }

    .content p{
        font-size: 16px;
        text-align: justify;
    }
    .kwitansi tr td {
        padding: 5px;
    }
    .nm_rs {
        font-size: 17px;
        font-weight: bold;
        text-align: center;
    }

    .almt_rs {
        font-size: 11px;
        text-align: center;
    }

    .row{
        margin-top: 20px;
        align-items: end;
    }

    .row p{
        text-align: right;
    }

    #tls{
        text-align:right;
    }
    .alamat-tujuan{
        margin-left:50%;
    }
    .garis1{
        border-top:3px solid black;
        height: 2px;
        border-bottom:1px solid black;
    }
    #logo{
        margin: auto;
        margin-left: 50%;
        margin-right: auto;
    }
    .data-pasien td {
        padding-bottom: 5px; /* Atur jarak antara baris */
    }

</style>
</html>

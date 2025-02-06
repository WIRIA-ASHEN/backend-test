<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserData;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all(), 200);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->update($request->all());
        // dd($user);
        return response()->json(['message' => 'User berhasil diperbarui'], 200);

    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus'], 200);
    }

    public function fetchExternalData()
    {
        $response = file_get_contents('https://bit.ly/48ejMhW');
        $data = json_decode($response, true);

        // Memisahkan data menjadi baris
        $rows = explode("\n", $data['DATA']);
        $parsedData = [];

        // Mengabaikan baris pertama yang berisi "NAMA|NIM|YMD" atau header lainnya
        // Misalnya jika baris pertama adalah header, kita bisa langsung mulai dari baris kedua
        $headerRow = true;

        foreach ($rows as $row) {
            // Skip baris header yang berisi "NAMA|NIM|YMD"
            if ($headerRow) {
                $headerRow = false;  // Set flag untuk baris header ke false
                continue;  // Lewati baris ini
            }

            // Pisahkan setiap baris berdasarkan delimiter '|'
            $columns = explode('|', $row);

            // Pastikan ada data yang valid (jumlah kolom yang benar)
            if (count($columns) > 1) {
                $rowData = [];

                // Loop untuk memeriksa setiap kolom berdasarkan jenis data
                foreach ($columns as $value) {
                    // Mencocokkan kolom berdasarkan isi
                    if (is_numeric($value) && strlen($value) == 8) {
                        // Jika nilai memiliki format tanggal (YYYY-MM-DD), anggap sebagai YMD
                        $rowData['YMD'] = $value;
                    } elseif (is_numeric($value) && strlen($value) == 10) {
                        // Jika nilai adalah angka dengan panjang 10 karakter, anggap sebagai NIM
                        $rowData['NIM'] = $value;
                    } else {
                        // Jika tidak sesuai dengan pola di atas, anggap sebagai NAMA
                        $rowData['NAMA'] = $value;
                    }
                }
                $parsedData[] = $rowData;  // Masukkan data yang sudah diformat ke dalam array
            }
        }

        return $parsedData;  // Mengembalikan data yang telah diproses dengan format yang konsisten
    }




    public function searchByName($name)
    {
        $data = $this->fetchExternalData();

        $filteredData = array_filter($data, function ($item) use ($name) {
            return strpos(strtolower($item['NAMA']), strtolower($name)) !== false;
        });

        return response()->json(array_values($filteredData), 200);
    }

    public function searchByNIM($nim)
    {
        $data = $this->fetchExternalData();

        $filteredData = array_filter($data, function ($item) use ($nim) {
            return $item['NIM'] === $nim;
        });

        return response()->json(array_values($filteredData), 200);
    }

    public function searchByYMD($ymd)
    {
        $data = $this->fetchExternalData();

        $filteredData = array_filter($data, function ($item) use ($ymd) {
            return $item['YMD'] === $ymd;
        });

        return response()->json(array_values($filteredData), 200);
    }

    public function fetchExternaltoDatabese()
    {
        $response = file_get_contents('https://bit.ly/48ejMhW');
        $data = json_decode($response, true);

        // Memisahkan data menjadi baris
        $rows = explode("\n", $data['DATA']);
        $parsedData = [];

        // Mengabaikan baris pertama yang berisi "NAMA|NIM|YMD"
        $headerRow = true;

        foreach ($rows as $row) {
            if ($headerRow) {
                $headerRow = false;
                continue;
            }

            // Pisahkan setiap baris berdasarkan delimiter '|'
            $columns = explode('|', $row);

            if (count($columns) > 1) {
                $rowData = [];

                foreach ($columns as $value) {
                    if (is_numeric($value) && strlen($value) == 8) {
                        $rowData['YMD'] = $value;
                    } elseif (is_numeric($value) && strlen($value) == 10) {
                        $rowData['NIM'] = (int) $value;
                    } else {
                        $rowData['NAMA'] = $value;
                    }
                }

                $parsedData[] = $rowData;

                // Simpan data ke database
                UserData::create([
                    'NAMA' => $rowData['NAMA'],
                    'YMD' => $rowData['YMD'],
                    'NIM' => $rowData['NIM']
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data imported successfully',
            'count' => count($parsedData)
        ], 200);
    }

}

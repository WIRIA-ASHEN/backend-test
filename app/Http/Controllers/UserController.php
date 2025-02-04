<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        // Memisahkan data menjadi baris dan kolom terpisah (YMD, NAMA, NIM)
        $rows = explode("\n", $data['DATA']);
        $parsedData = [];

        foreach ($rows as $row) {
            // Pisahkan setiap baris dengan '|' untuk memisahkan YMD, NAMA, dan NIM
            $columns = explode('|', $row);
            if (count($columns) === 3) {
                $parsedData[] = [
                    'YMD' => $columns[0],
                    'NAMA' => $columns[1],
                    'NIM' => $columns[2]
                ];
            }
        }

        return $parsedData;  // Mengembalikan data yang diurai untuk digunakan dalam fungsi pencarian
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

}

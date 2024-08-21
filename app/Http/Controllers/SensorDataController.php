<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorData;
use Illuminate\Http\Request;
use PhpMqtt\Client\Facades\MQTT;

class SensorDataController extends Controller
{
    // Menampilkan semua data sensor dalam format tampilan web (untuk keperluan tampilan web)
    public function indexWeb()
    {
        $sensorData = SensorData::with('sensor')
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Menambahkan pagination
        $sensors = Sensor::all();

        return view('pages.sensordata', compact('sensorData', 'sensors'));
    }

    // Menampilkan semua data sensor dalam format JSON untuk API
    public function index()
    {
        $sensorData = SensorData::with('sensor')
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Menambahkan pagination
        return response()->json($sensorData); // Kembalikan data dengan format pagination
    }

    // Menyimpan data sensor baru melalui API dan publish ke MQTT
    public function store(Request $request)
    {
        $request->validate([
            'sensor_id' => 'required|exists:sensors,id',
            'value' => 'required',
        ]);

        // Simpan data sensor ke database
        $sensorData = SensorData::create([
            'sensor_id' => $request->sensor_id,
            'value' => $request->value,
        ]);

        // Ambil nama sensor berdasarkan sensor_id
        $sensor = Sensor::find($request->sensor_id);
        $sensorName = $sensor->name;

        // Publish data sensor ke broker MQTT dengan topik berdasarkan nama sensor
        $data = [
            'sensor_id' => $sensor->id,
            'sensor_name' => $sensorName,
            'value' => $request->value,
            'created_at' => $sensorData->created_at,
        ];

        MQTT::connection()->publish("sensors/{$sensorName}", json_encode($data));

        return response()->json(['success' => 'Data sensor berhasil disimpan.', 'data' => $sensorData], 201);
    }

    // Menampilkan data sensor tertentu dalam format JSON
    public function show($id)
    {
        $sensorData = SensorData::findOrFail($id);
        return response()->json($sensorData);
    }

    // Memperbarui data sensor melalui API
    public function update(Request $request, $id)
    {
        $request->validate([
            'sensor_id' => 'required|exists:sensors,id',
            'value' => 'required',
        ]);

        $sensorData = SensorData::findOrFail($id);
        $sensorData->update([
            'sensor_id' => $request->sensor_id,
            'value' => $request->value,
        ]);

        // Ambil nama sensor berdasarkan sensor_id
        $sensor = Sensor::find($request->sensor_id);
        $sensorName = $sensor->name;

        // Publish data sensor yang diperbarui ke broker MQTT dengan topik berdasarkan nama sensor
        $data = [
            'sensor_id' => $sensor->id,
            'sensor_name' => $sensorName,
            'value' => $request->value,
            'updated_at' => $sensorData->updated_at,
        ];

        MQTT::connection()->publish("sensors/{$sensorName}", json_encode($data));

        return response()->json(['success' => 'Data sensor berhasil diperbarui.', 'data' => $sensorData]);
    }

    // Menghapus data sensor melalui API
    public function destroy($id)
    {
        $sensorData = SensorData::findOrFail($id);
        $sensorData->delete();

        return response()->json(['success' => 'Data sensor berhasil dihapus.']);
    }
}

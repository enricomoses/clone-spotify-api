<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConcertController extends Controller
{
    public function index()
    {
        $concerts = Concert::with('artists')->get();
        return response()->json(['message' => 'Data konser', 'data' => $concerts], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string',
            'location_url' => 'nullable|url',
            'event_at' => 'required|date',
            'ticket_url' => 'required|url'
        ]);

        $validated['id_concert'] = Str::uuid()->toString();
        $concert = Concert::create($validated);

        return response()->json(['message' => 'Konser berhasil ditambahkan', 'data' => $concert], 201);
    }

    public function show($id)
    {
        $concert = Concert::with('artists')->find($id);
        if (!$concert) return response()->json(['message' => 'Konser tidak ditemukan'], 404);
        return response()->json(['message' => 'Detail konser', 'data' => $concert], 200);
    }

    public function update(Request $request, $id)
    {
        $concert = Concert::query()->find($id);
        if (!$concert) return response()->json(['message' => 'Konser tidak ditemukan'], 404);

        $validated = $request->validate([
            'location' => 'sometimes|required|string',
            'location_url' => 'nullable|url',
            'event_at' => 'sometimes|required|date',
            'ticket_url' => 'sometimes|required|url'
        ]);

        $concert->update($validated);
        return response()->json(['message' => 'Konser berhasil diupdate', 'data' => $concert], 200);
    }

    public function destroy($id)
    {
        if (!Concert::destroy($id)) {
            return response()->json(['message' => 'Konser tidak ditemukan'], 404);
        }
        return response()->json(['message' => 'Konser berhasil dihapus'], 200);
    }
}

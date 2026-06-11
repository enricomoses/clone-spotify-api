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

    public function show($id)
    {
        // Perbaikan Intelephense dengan where()->first()
        $concert = Concert::with('artists')->where('id_concert', $id)->first();
        if (!$concert) return response()->json(['message' => 'Konser tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail konser', 'data' => $concert], 200);
    }

    // --- CRUD BASIC ---
    public function store(Request $request)
    {
        // PERBAIKAN: Menambahkan validasi array artist_ids wajib dari frontend
        $validated = $request->validate([
            'location' => 'required|string',
            'location_url' => 'nullable|url',
            'event_at' => 'required|date',
            'ticket_url' => 'required|url',
            'artist_ids' => 'required|array',
            'artist_ids.*' => 'exists:artist,id_artist'
        ]);

        $concertData = [
            'id_concert' => Str::uuid()->toString(),
            'location' => $validated['location'],
            'location_url' => $validated['location_url'] ?? null,
            'event_at' => $validated['event_at'],
            'ticket_url' => $validated['ticket_url'],
        ];

        // 1. Simpan data ke tabel konser
        $concert = Concert::create($concertData);

        // 2. Simpan relasi Many-to-Many ke tabel pivot artist_concert
        $concert->artists()->attach($validated['artist_ids']);

        // 3. Load relasi agar data yang dikirim balik langsung memuat daftar artisnya
        $concert->load('artists');

        return response()->json(['message' => 'Konser berhasil ditambahkan', 'data' => $concert], 201);
    }

    public function update(Request $request, $id)
    {
        $concert = Concert::query()->where('id_concert', $id)->first();
        if (!$concert) return response()->json(['message' => 'Konser tidak ditemukan'], 404);

        $validated = $request->validate([
            'location' => 'sometimes|required|string',
            'location_url' => 'nullable|url',
            'event_at' => 'sometimes|required|date',
            'ticket_url' => 'sometimes|required|url',
            'artist_ids' => 'sometimes|required|array',
            'artist_ids.*' => 'exists:artist,id_artist'
        ]);

        // Update data dasar konser
        $concert->update($request->only(['location', 'location_url', 'event_at', 'ticket_url']));

        // PERBAIKAN: Sinkronisasi data tabel pivot jika frontend mengirimkan array artist_ids yang baru
        if ($request->has('artist_ids')) {
            $concert->artists()->sync($validated['artist_ids']);
        }

        $concert->load('artists');
        return response()->json(['message' => 'Konser berhasil diupdate', 'data' => $concert], 200);
    }

    public function destroy($id)
    {
        $concert = Concert::query()->where('id_concert', $id)->first();
        if (!$concert) {
            return response()->json(['message' => 'Konser tidak ditemukan'], 404);
        }

        // PERBAIKAN: Putus relasi di tabel pivot artist_concert terlebih dahulu
        $concert->artists()->detach();

        // Hapus data konser utama
        $concert->delete();

        return response()->json(['message' => 'Konser berhasil dihapus'], 200);
    }

    // --- MANAJEMEN ARTIS PADA KONSER SECARA MANUAL (ENDPOINT TAMBAHAN) ---
    public function addArtist(Request $request, $id)
    {
        $concert = Concert::query()->where('id_concert', $id)->first();
        if (!$concert) return response()->json(['message' => 'Konser tidak ditemukan'], 404);

        $request->validate(['artist_id' => 'required|exists:artist,id_artist']);

        $concert->artists()->syncWithoutDetaching([$request->artist_id]);
        return response()->json(['message' => 'Artis berhasil ditambahkan ke konser'], 200);
    }

    public function removeArtist($id, $artist_id)
    {
        $concert = Concert::query()->where('id_concert', $id)->first();
        if (!$concert) return response()->json(['message' => 'Konser tidak ditemukan'], 404);

        // Cek agar concert tidak menjadi 0 artist (minimal 1 artist)
        $currentArtistCount = $concert->artists()->count();
        if ($currentArtistCount <= 1) {
            return response()->json([
                'message' => 'Tidak bisa menghapus artis terakhir dari konser. Konser harus punya minimal 1 artist.'
            ], 400);
        }

        $concert->artists()->detach($artist_id);
        return response()->json(['message' => 'Artis dihapus dari konser'], 200);
    }
}

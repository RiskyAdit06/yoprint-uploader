<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Upload;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $upload;

    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    public function handle()
    {
        $this->upload->update(['status' => 'processing']);
        $filePath = storage_path('app/uploads/' . $this->upload->filename);

        if (!file_exists($filePath)) {
            throw new Exception("File tidak ditemukan: {$filePath}");
        }

        // Baca seluruh isi file
        $content = file_get_contents($filePath);

        // Deteksi encoding dan konversi ke UTF-8 jika perlu
        $encoding = mb_detect_encoding($content, ['UTF-8','UTF-16LE','UTF-16BE','ASCII'], true);
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            Log::info("File CSV dikonversi ke UTF-8 dari: $encoding");
        }

        // Simpan di memory stream agar fgetcsv bisa baca
        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        try {
            // Baca baris pertama untuk deteksi delimiter
            $firstLine = fgets($handle);
            rewind($handle);

            $delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ",")) ? "\t" : ",";
            Log::info("Delimiter terdeteksi: " . ($delimiter === "\t" ? "TAB" : "COMMA"));

            // Baca header
            $headersRaw = fgetcsv($handle, 0, $delimiter);
            if (!$headersRaw) {
                throw new Exception("Header CSV tidak valid atau kosong.");
            }

            // Log header asli untuk debugging
            Log::info('Header CSV asli:', $headersRaw);

            // Bersihkan BOM dan karakter tak terlihat, normalisasi header
            $headers = array_map(function($h) {
                $h = preg_replace('/[\x00-\x1F\x7F\x{FEFF}]/u', '', $h); // hapus BOM + control chars
                $h = trim($h);
                // Normalisasi: hapus spasi ganda, ubah spasi/underscore/hyphen menjadi underscore, uppercase
                $h = preg_replace('/[\s_\-]+/', '_', $h);
                return strtoupper($h);
            }, $headersRaw);

            Log::info('Header CSV setelah bersih:', $headers);

            // Fungsi helper untuk mencari kolom dengan fleksibel
            $findColumn = function($searchName, $headers, $headersRaw) {
                // Normalisasi nama yang dicari
                $searchNormalized = strtoupper(preg_replace('/[\s_\-]+/', '_', trim($searchName)));
                
                // Variasi nama yang mungkin
                $searchVariations = [
                    $searchNormalized,
                    str_replace('_', '', $searchNormalized), // UNIQUEKEY
                    str_replace('_', ' ', $searchNormalized), // UNIQUE KEY
                    str_replace('_', '-', $searchNormalized), // UNIQUE-KEY
                    preg_replace('/[^A-Z0-9]/', '', $searchNormalized), // Hanya huruf dan angka
                ];
                
                // Cari di header yang sudah dinormalisasi
                foreach ($searchVariations as $variation) {
                    foreach ($headers as $index => $header) {
                        if ($header === $variation) {
                            Log::info("Kolom ditemukan: '{$searchName}' -> index {$index} (header: '{$headersRaw[$index]}')");
                            return $index;
                        }
                    }
                }
                
                // Coba case-insensitive match
                foreach ($searchVariations as $variation) {
                    foreach ($headers as $index => $header) {
                        if (strcasecmp($header, $variation) === 0) {
                            Log::info("Kolom ditemukan (case-insensitive): '{$searchName}' -> index {$index} (header: '{$headersRaw[$index]}')");
                            return $index;
                        }
                    }
                }
                
                // Coba partial match (contains)
                foreach ($searchVariations as $variation) {
                    $variationClean = preg_replace('/[^A-Z0-9]/', '', $variation);
                    foreach ($headers as $index => $header) {
                        $headerClean = preg_replace('/[^A-Z0-9]/', '', $header);
                        if ($headerClean === $variationClean && strlen($variationClean) > 0) {
                            Log::info("Kolom ditemukan (partial match): '{$searchName}' -> index {$index} (header: '{$headersRaw[$index]}')");
                            return $index;
                        }
                    }
                }
                
                return false;
            };

            // Cari semua kolom yang diperlukan dengan fleksibel
            $columnMap = [
                'UNIQUE_KEY' => $findColumn('UNIQUE_KEY', $headers, $headersRaw),
                'PRODUCT_TITLE' => $findColumn('PRODUCT_TITLE', $headers, $headersRaw),
                'PRODUCT_DESCRIPTION' => $findColumn('PRODUCT_DESCRIPTION', $headers, $headersRaw),
                'STYLE#' => $findColumn('STYLE#', $headers, $headersRaw),
                'COLOR_NAME' => $findColumn('COLOR_NAME', $headers, $headersRaw),
                'SANMAR_MAINFRAME_COLOR' => $findColumn('SANMAR_MAINFRAME_COLOR', $headers, $headersRaw),
                'SIZE' => $findColumn('SIZE', $headers, $headersRaw),
                'PIECE_PRICE' => $findColumn('PIECE_PRICE', $headers, $headersRaw),
            ];

            // UNIQUE_KEY wajib ada
            if ($columnMap['UNIQUE_KEY'] === false) {
                $availableHeadersRaw = implode(', ', array_map(function($h) {
                    return "'" . addslashes($h) . "'";
                }, $headersRaw));
                $availableHeaders = implode(', ', $headers);
                throw new Exception("Kolom UNIQUE_KEY tidak ditemukan di CSV. Header asli: [{$availableHeadersRaw}]. Header setelah normalisasi: [{$availableHeaders}]");
            }

            $count = 0;
            DB::beginTransaction();

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (count($row) !== count($headers)) {
                    Log::warning("Baris tidak valid, dilewati", ['row' => $row, 'expected' => count($headers), 'actual' => count($row)]);
                    continue;
                }

                // Ambil data berdasarkan index kolom
                $getValue = function($columnName) use ($columnMap, $row) {
                    $index = $columnMap[$columnName];
                    return ($index !== false && isset($row[$index])) ? trim($row[$index]) : '';
                };

                $uniqueKey = $getValue('UNIQUE_KEY');

                if ($uniqueKey === '') {
                    continue;
                }

                // UPSERT / Idempotent
                Product::updateOrCreate(
                    ['UNIQUE_KEY' => $uniqueKey],
                    [
                        'PRODUCT_TITLE' => $getValue('PRODUCT_TITLE') ?: null,
                        'PRODUCT_DESCRIPTION' => $getValue('PRODUCT_DESCRIPTION') ?: null,
                        'STYLE#' => $getValue('STYLE#') ?: null,
                        'COLOR_NAME' => $getValue('COLOR_NAME') ?: null,
                        'SANMAR_MAINFRAME_COLOR' => $getValue('SANMAR_MAINFRAME_COLOR') ?: null,
                        'SIZE' => $getValue('SIZE') ?: null,
                        'PIECE_PRICE' => $getValue('PIECE_PRICE') ?: null,
                    ]
                );

                $count++;
            }

            fclose($handle);
            DB::commit();

            $this->upload->update([
                'status' => 'completed',
                'error_message' => null
            ]);

            Log::info("✅ CSV upload #{$this->upload->id} berhasil: {$count} baris diproses.");
        } catch (Exception $e) {
            DB::rollBack();
            fclose($handle);

            $this->upload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            Log::error("CSV upload gagal", [
                'upload_id' => $this->upload->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
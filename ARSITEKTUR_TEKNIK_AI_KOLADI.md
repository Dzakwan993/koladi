# Arsitektur Teknik & Rekayasa AI Engine (Koladi)

Dokumen ini menjelaskan **arsitektur rekayasa perangkat lunak (software engineering architecture)** dari AI Engine pada aplikasi Koladi. Berbeda dengan dokumen alur fungsional AI, dokumen ini fokus pada aspek **modularitas, integrasi, skalabilitas, ketahanan (resilience)**, serta cara memperluas (*scale/extend*) engine untuk mendukung berbagai penyedia AI (*AI Providers*) seperti OpenAI, Anthropic Claude, atau LLM Lokal (Ollama/DeepSeek).

---

## 1. Prinsip & Pola Desain Perangkat Lunak (Design Patterns)

Sistem AI Engine Koladi dirancang mengacu pada prinsip **SOLID** dan pola desain enterprise modern:

```
                  +-----------------------+
                  |      AIService        | (Orchestrator)
                  +-----------+-----------+
                              |
     +------------------------+------------------------+
     |                        |                        |
     v                        v                        v
+----+-----------------+ +----+------------------+ +---+-------------------+
| PromptBuilderService | |   AIProvider (Interface) | | JsonValidatorService  |
+----------------------+ +------------+-----------+ +-----------------------+
                                      |
                         +------------+------------+
                         |                         |
                         v                         v
               +---------+---------+     +---------+---------+
               |  GeminiProvider   |     |  OpenAIProvider   | (Future Provider)
               +-------------------+     +-------------------+
```

### A. Strategy Pattern (`AIProvider` Interface)
- **Konsep:** `AIService` tidak terikat (*tightly coupled*) pada vendor AI tertentu (misal Google Gemini). Sebaliknya, `AIService` bergantung pada abstraksi interface `App\Services\AI\AIProvider`.
- **Manfaat:** Penyedia AI dapat diganti atau ditukar kapan saja (*plug-and-play*) cukup dengan mengubah pembacaan konfigurasi atau binding di Laravel Service Container.

### B. Dependency Inversion & Inversion of Control (IoC)
- Komponen tingkat tinggi (Controller/Job) bergantung pada Service (`AIService`), dan `AIService` bergantung pada kontrak interface (`AIProvider`).
- Pengisian instansiasi dilakukan secara otomatis melalui **Laravel Dependency Injection**.

### C. Single Responsibility Principle (SRP)
Sistem memecah proses besar menjadi modul-modul kecil yang memiliki 1 tanggung jawab spesifik:
- `PromptBuilderService`: Menangani pembentukan dan perakitan prompt.
- `AIProvider`: Menangani komunikasi HTTP, autentikasi, rotasi key, dan fault tolerance ke API AI.
- `JsonValidatorService`: Menangani pembersihan sintaks, parsing JSON, dan validasi skema keluaran.
- `BriefMapperService`: Menangani pemetaan data JSON ke Model database (Eloquent).
- `AIService`: Bertindak sebagai koordinator (*Orchestrator*) seluruh alur kerja.

---

## 2. Modularitas & Struktur Komponen

Seluruh kode AI Engine berada di namespace `App\Services\AI`:

| Komponen | File Path | Tanggung Jawab Utama |
| :--- | :--- | :--- |
| **Interface** | [AIProvider.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/AIProvider.php) | Kontrak umum metode `generate(string $prompt): string`. |
| **Orchestrator** | [AIService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/AIService.php) | Menghubungkan Builder, Provider, dan Validator dalam satu workflow. |
| **Provider** | [GeminiProvider.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/GeminiProvider.php) | Implementasi konkret untuk Google Gemini API (Rotasi Key, Fallback, Retry). |
| **Prompt Builder** | [PromptBuilderService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/PromptBuilderService.php) | Penggabungan konteks dokumen dan instruksi prompt. |
| **JSON Validator** | [JsonValidatorService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/JsonValidatorService.php) | Sanitasi keluaran markdown/JSON & penanganan error parsing. |
| **DB Mapper** | [BriefMapperService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/BriefMapperService.php) | Transformasi array terstruktur menjadi record DB (`Project`, `Task`, dll). |

---

## 3. Skalabilitas & Panduan Integrasi Provider Baru

Salah satu keunggulan utama arsitektur ini adalah **Kemudahan Skalabilitas (*Extensibility*)**. Jika di kemudian hari sistem ingin mendukung penyedia AI lain (misalnya **OpenAI GPT-4o**, **Claude 3.5 Sonnet**, atau **DeepSeek/Ollama**), kita **tidak perlu mengubah kode aplikasi utama (`AIService` atau Controller)**.

### Langkah Menambahkan Provider Baru (Contoh: OpenAI)

#### Langkah 1: Buat Class Provider Baru
Buat class yang mengimplementasikan `AIProvider`:

```php
namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class OpenAIProvider implements AIProvider
{
    public function generate(string $prompt): string
    {
        $response = Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'response_format' => ['type' => 'json_object']
            ]);

        return $response->json('choices.0.message.content');
    }
}
```

#### Langkah 2: Binding di Service Provider Laravel
Buka `AppServiceProvider.php` dan sesuaikan binding berdasarkan konfigurasi `.env`:

```php
use App\Services\AI\AIProvider;
use App\Services\AI\GeminiProvider;
use App\Services\AI\OpenAIProvider;

public function register(): void
{
    $this->app->bind(AIProvider::class, function ($app) {
        $driver = config('services.ai.default_driver', 'gemini');

        return match ($driver) {
            'openai' => $app->make(OpenAIProvider::class),
            'gemini' => $app->make(GeminiProvider::class),
            default  => throw new \InvalidArgumentException("AI Driver [{$driver}] tidak didukung."),
        };
    });
}
```

#### Langkah 3: Ubah Konfigurasi `.env`
Cukup ubah nilai driver di `.env`:
```env
AI_DEFAULT_DRIVER=openai
OPENAI_API_KEY=sk-...
```

---

## 4. Aspek Rekayasa & Ketahanan Sistem (Fault Tolerance)

Pada tingkat penyedia konkret ([GeminiProvider.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/GeminiProvider.php)), diterapkan mekanisme ketahanan tingkat tinggi untuk menjamin ketersediaan layanan (*high availability*):

### A. API Key Rotation (Rotasi Key Otomatis)
- **Masalah:** API Key publik/gratisan rawan terkena HTTP 429 (*Rate Limit / Quota Exceeded*).
- **Solusi Rekayasa:** `GeminiProvider` menampung multiple API Keys (`GEMINI_API_KEY`, `GEMINI_API_KEY_2`, dst). Ketika mendeteksi HTTP 429, sistem langsung beralih (*failover*) ke key berikutnya secara seketika tanpa membatalkan proses pengguna.

### B. Retry with Backoff (Penanganan Overload Server)
- **Masalah:** Server AI pihak ketiga sewaktu-waktu mengalami kelebihan beban sementara (HTTP 503 / Timeout).
- **Solusi Rekayasa:** Metode `callApiWithBackoff()` menangkap HTTP 503 dan secara otomatis menunda eksekusi selama `2 detik` sebelum melakukan percobaan ulang (*retry*).

### C. Model Fallback (Penurunan Mutu Layanan Terkendali)
- **Masalah:** Model utama (misal `gemini-3.5-flash`) down total atau kehabisan kuota di seluruh API Key.
- **Solusi Rekayasa:** Sistem otomatis menurunkan permintaan ke model cadangan (`gemini-3.1-flash-lite`) agar transaksi bisnis pengguna tetap berjalan.

### D. Asynchronous & Queue Readiness
- Eksekusi AI yang membutuhkan waktu relatif panjang (5-30 detik) disiapkan untuk berjalan secara independen di latar belakang (*background job*) memanfaatkan **Laravel Queue**, sehingga tidak memblokir HTTP Request utama pengguna.

---

## 5. Kesimpulan

Arsitektur AI Engine Koladi dibangun dengan memisahkan **Logika Bisnis**, **Orkestrasi AI**, dan **Integrasi Vendor AI**.

- **Integrasi Mudah:** Penyedia AI baru dapat ditambah tanpa mengubah kode yang ada.
- **Modularitas Tinggi:** Komponen pembawa prompt, pemanggil API, dan pemvalidasi JSON berdiri sendiri secara rapi.
- **Skalabilitas & Reliability:** Tahan terhadap rate limit, server downtime, serta siap ditingkatkan ke pemrosesan asinkron skala besar.

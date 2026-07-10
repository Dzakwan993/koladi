# **Koladi AI Brief Parser - Pipeline Documentation**

Dokumen ini menjelaskan alur lengkap (*pipeline*) pengolahan dokumen brief proyek, mulai dari unggahan berkas oleh pengguna hingga persiapan integrasi dengan model bahasa besar (Large Language Model/LLM).

---

## **1. Gambaran Umum Alur (Pipeline Flow)**

```mermaid
graph TD
    A[User Uploads Files] --> B[BriefController@upload]
    B --> C[DocumentParserService]
    C --> D[ParserFactory]
    D -->|Instantiates Parser| E[PdfParser / DocxParser / TxtParser]
    E -->|Extracts Raw Text| F[DocumentCleaningService]
    F -->|Cleans Text & Spacing| G[DocumentNormalizationService]
    G -->|Formats Markdown & Adds Metadata| H[Normalized Document Array]
    H --> I[AIService]
    I --> J[PromptBuilderService]
    J -->|Assembles Prompt| K[GeminiProvider / AIProvider]
    K -->|Calls Gemini API| L[JsonValidatorService]
    L -->|Validates JSON Schema| M[BriefMapperService]
    M -->|Maps to Domain Models| N[Human Review UI]
```

---

## **2. Detail Langkah & Tanggung Jawab Berkas**

### **A. Tahap Upload & Route**
*   **Berkas Terkait:** 
    *   [routes/web.php](file:///Users/pinkman/Documents/project/koladi-laravel/routes/web.php) (Mendefinisikan route `/brief` dan `/brief/upload`).
    *   [BriefController.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Http/Controllers/BriefController.php) (Controller utama penerima request berkas dari user).
    *   [upload-brief.blade.php](file:///Users/pinkman/Documents/project/koladi-laravel/resources/views/upload-brief.blade.php) (Halaman UI upload awal).
*   **Alur:** 
    1. User mengunggah satu atau beberapa berkas (PDF, DOCX, TXT) melalui form di `upload-brief.blade.php`.
    2. Request diterima oleh method `BriefController@upload` yang memvalidasi keberadaan berkas sebelum memanggil `DocumentParserService`.

---

### **B. Tahap Parsing (Ekstraksi Teks)**
*   **Berkas Terkait:**
    *   [DocumentParserService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/Document/DocumentParserService.php) (Orkestrator proses ekstraksi).
    *   [ParserFactory.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/Document/ParserFactory.php) (Factory untuk memilih parser berdasarkan ekstensi file).
    *   [PdfParser.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/Document/PdfParser.php) (Membaca file PDF menggunakan library `Smalot\PdfParser`).
    *   [DocxParser.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/Document/DocxParser.php) (Membaca file DOCX via XML parser & ZipArchive).
    *   [TxtParser.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/Document/TxtParser.php) (Membaca plain text file).
*   **Alur:** 
    1. `DocumentParserService` menerima kumpulan berkas dari Controller.
    2. `ParserFactory` menentukan objek parser yang tepat berdasarkan ekstensi berkas.
    3. Parser masing-masing mengekstrak teks mentah dari berkas tersebut.

---

### **C. Tahap Cleaning & Normalisasi**
*   **Berkas Terkait:**
    *   [DocumentCleaningService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/Document/DocumentCleaningService.php)
    *   [DocumentNormalizationService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/Document/DocumentNormalizationService.php)
*   **Alur:**
    1. **Cleaning:** Mengoreksi *line endings* (`\r\n` ke `\n`), memangkas spasi/tab berlebih, membatasi baris kosong beruntun (maksimal 2 *newline* berturut-turut), dan memastikan konversi UTF-8 yang bersih.
    2. **Normalisasi:** Mengubah simbol bullet beraneka ragam (`•`, `◦`, `▪`) menjadi satu standar `-`, merapikan spasi pada titik dua (`:`), menghapus BOM, menghapus pembatas garis horizontal (`---`), dan menyematkan metadata dokumen di awal teks (nama berkas & tipe ekstensi).

---

### **D. Tahap Prompt Building & AI Service**
*   **Berkas Terkait:**
    *   [AIService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/AIService.php) (Orkestrator pipeline AI).
    *   [PromptBuilderService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/PromptBuilderService.php) (Penyusun naskah prompt akhir untuk dikirim ke LLM).
    *   **Kumpulan Prompt Template:**
        *   [system.md](file:///Users/pinkman/Documents/project/koladi-laravel/resources/prompts/brief-parser/system.md) (Instruksi persona AI).
        *   [guardrails.md](file:///Users/pinkman/Documents/project/koladi-laravel/resources/prompts/brief-parser/guardrails.md) (Batasan keamanan & instruksi anti-halusinasi).
        *   [output-rules.md](file:///Users/pinkman/Documents/project/koladi-laravel/resources/prompts/brief-parser/output-rules.md) (Aturan kembalian JSON murni).
        *   [schema.md](file:///Users/pinkman/Documents/project/koladi-laravel/resources/prompts/brief-parser/schema.md) (Skema format JSON yang diharapkan).
*   **Alur:**
    1. `AIService` menerima array dokumen yang telah dinormalisasi.
    2. `PromptBuilderService` menggabungkan instruksi sistem, guardrails, format JSON target, dan isi dokumen-dokumen yang diunggah ke dalam satu string prompt utuh dengan pembatas `DOCUMENT START` dan `DOCUMENT END`.
    3. Ini memastikan AI tidak mengeksekusi instruksi yang sengaja disisipkan di dalam dokumen brief (*Prompt Injection*).

---

### **E. Tahap Provider, Validasi & Pemetaan (Mapping)**
*   **Berkas Terkait:**
    *   [AIProvider.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/AIProvider.php) (Interface agar aplikasi modular dan tidak terikat vendor tertentu).
    *   [GeminiProvider.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/GeminiProvider.php) (Implementasi API panggilan ke Google Gemini).
    *   [JsonValidatorService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/JsonValidatorService.php) (Memastikan respon dari AI adalah string JSON valid).
    *   [BriefMapperService.php](file:///Users/pinkman/Documents/project/koladi-laravel/app/Services/AI/BriefMapperService.php) (Memetakan data JSON hasil validasi ke struktur data internal/model Laravel).
*   **Alur:**
    1. Prompt utuh dikirim ke model AI melalui `AIProvider`.
    2. Hasil mentah divalidasi oleh `JsonValidatorService` untuk memastikan format JSON-nya terurai dengan benar.
    3. JSON yang valid kemudian diubah menjadi object/array model yang konsisten melalui `BriefMapperService` agar siap ditampilkan di halaman Human Review sebelum di-import menjadi Project & Tasks resmi.

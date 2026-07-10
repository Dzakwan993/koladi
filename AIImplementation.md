# **Koladi AI Brief Engine \- Implementation Summary**

## **Current Progress (Completed)**

### **1\. Document Upload**

Sudah tersedia halaman upload sederhana sebagai MVP.

Flow:

User Upload  
    │  
    ▼  
BriefController

Tujuan tahap ini hanya memastikan backend pipeline dapat berjalan terlebih dahulu tanpa bergantung pada UI final.

---

### **2\. Document Parser**

Sudah mendukung:

* PDF (Smalot PDF Parser)  
* DOCX (ZipArchive \+ XML Parsing)  
* TXT

Struktur folder:

Services/  
└── Document/  
    ├── DocumentParserService.php  
    ├── ParserFactory.php  
    ├── PdfParser.php  
    ├── DocxParser.php  
    └── TxtParser.php

ParserFactory bertugas memilih parser berdasarkan extension file sehingga parser dapat ditambah di masa depan tanpa mengubah DocumentParserService.

---

### **3\. Document Cleaning**

Sudah dipisahkan menjadi service sendiri.

DocumentCleaningService

Tugas:

* menghapus karakter tidak perlu  
* merapikan whitespace  
* menghapus empty line berlebih  
* membersihkan noise hasil parser

Cleaning hanya bertugas membersihkan text mentah tanpa mengubah makna.

---

### **4\. Document Normalization**

Sudah dipisahkan menjadi:

DocumentNormalizationService

Saat ini melakukan:

* normalisasi bullet  
* normalisasi spacing  
* penambahan metadata dokumen  
* remove BOM  
* remove horizontal line tertentu

Output akhir menjadi format yang konsisten seperti:

\=== DOCUMENT \===  
Filename: Meeting.docx  
Type: docx

...  
isi dokumen...

Semua dokumen sekarang menghasilkan format yang seragam.

---

### **5\. Multi Document Upload**

Sudah berhasil.

Output parser sekarang berupa array.

\[  
    {  
        filename,  
        extension,  
        mime\_type,  
        content  
    },  
    {  
        ...  
    }  
\]

Sehingga AI dapat menerima beberapa dokumen sekaligus.

---

### **6\. Edge Case Testing**

Sudah dilakukan pengujian terhadap:

✓ PDF

✓ DOCX

✓ TXT

✓ Bullet

✓ Numbering

✓ Emoji

✓ URL

✓ Metadata

✓ Multiple documents

✓ Deadline conflict

✓ Mixed Indonesian & English

✓ Garis horizontal

✓ Table sederhana

Hasil parser saat ini dinilai sudah cukup stabil untuk MVP.

---

## **Current Pipeline**

Upload

↓

Parser

↓

Cleaning

↓

Normalization

↓

Output Document Array

Contoh output:

\[  
    {  
        filename,  
        extension,  
        mime\_type,  
        content  
    }  
\]

Output ini dianggap sebagai "Real World Dataset" dan dapat digunakan oleh tim AI untuk melakukan Prompt Engineering di Google AI Studio.

---

# **Next Architecture**

Tahap berikutnya bukan lagi memperbaiki parser.

Fokus berpindah ke AI Pipeline.

Arsitektur yang akan digunakan:

Upload  
    │  
    ▼  
Parser  
    │  
    ▼  
Cleaning  
    │  
    ▼  
Normalization  
    │  
    ▼  
Prompt Builder  
    │  
    ▼  
AI Service  
    │  
    ▼  
AI Provider  
    │  
    ▼  
Gemini API  
    │  
    ▼  
JSON Validation  
    │  
    ▼  
Human Review  
    │  
    ▼  
Kanban  
Decision Log  
Deliverables

---

# **Prompt Builder**

Prompt Builder bukan AI.

Tugasnya hanya menyusun prompt final.

Input:

Normalized Documents

Output:

System Prompt

\+

Guardrail

\+

JSON Schema

\+

Normalized Documents

Prompt Builder tidak mengetahui Gemini maupun provider AI.

---

# **AI Service**

AI Service bertugas menjadi penghubung antara aplikasi dan AI.

Controller hanya memanggil:

AIService

Controller tidak mengetahui Gemini ataupun OpenAI.

---

# **AI Provider Layer**

Supaya aplikasi tidak bergantung pada Gemini.

Struktur yang direncanakan:

Services/  
└── AI/  
    ├── AIProvider.php  
    ├── AIService.php  
    ├── GeminiProvider.php  
    ├── OpenAIProvider.php  
    ├── ClaudeProvider.php  
    └── GroqProvider.php

Interface:

AIProvider

generate(prompt)

Setiap provider hanya mengimplementasikan fungsi tersebut.

Contoh:

GeminiProvider

↓

generate(prompt)

↓

Gemini API

Jika suatu hari ingin pindah ke OpenAI atau provider lain, hanya provider tersebut yang diganti.

Pipeline lainnya tetap sama.

---

# **Prompt Engineering Workflow**

Google AI Studio digunakan hanya saat development.

Flow:

Developer

↓

Google AI Studio

↓

Prompt Engineering

↓

Prompt Final

↓

resources/prompts/

↓

Prompt Builder

Laravel tidak melakukan training model.

Laravel hanya:

* membaca prompt  
* menggabungkan prompt dengan hasil normalization  
* mengirim ke AI melalui API

---

# **JSON Validation**

Setelah AI mengembalikan hasil.

Gemini

↓

JSON

↓

JSON Validation

Validator memastikan:

* JSON valid  
* semua field wajib tersedia  
* tipe data sesuai  
* tidak ada text tambahan selain JSON

Jika gagal:

AI dapat diminta regenerate atau hasil ditolak.

---

# **Human Review**

AI tidak langsung membuat task.

Flow:

AI Output

↓

Human Review

↓

Approve

↓

Kanban

Decision Log

Deliverables

Pendekatan ini sesuai konsep Human-in-the-Loop yang telah direncanakan sejak awal.

---

# **Current Status**

Parser Layer

✅ Selesai (MVP)

Cleaning

✅ Selesai

Normalization

✅ Selesai

Edge Case Testing

✅ Selesai

Prompt Builder

⬜ Belum

AI Service

⬜ Belum

AI Provider

⬜ Belum

Gemini Integration

⬜ Belum

JSON Validation

⬜ Belum

Human Review

⬜ Belum

Kanban Integration

⬜ Belum

---

# **Notes**

Output hasil Normalization saat ini dianggap sudah final untuk MVP dan dapat digunakan sebagai dataset nyata untuk tim AI melakukan Prompt Engineering di Google AI Studio. Fokus pengembangan berikutnya bukan lagi menyempurnakan parser, melainkan membangun AI Layer yang modular menggunakan AI Provider Pattern sehingga aplikasi tidak bergantung pada satu vendor AI (Gemini) dan dapat dengan mudah berpindah ke OpenAI, Claude, Groq, atau provider lain di masa depan.


<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CompaniesExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    WithColumnWidths,
    WithTitle
{
    /**
     * Ambil data perusahaan dari database
     */
    public function collection()
    {
        // Ambil semua perusahaan dengan relasi yang dibutuhkan
        $companies = Company::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Tambahkan computed properties
        foreach ($companies as $company) {
            // Hitung member count
            $company->member_count = DB::table('user_companies')
                ->where('company_id', $company->id)
                ->whereNull('deleted_at')
                ->count();
            
            // Ambil subscription
            $subscription = Subscription::where('company_id', $company->id)
                ->whereNull('deleted_at')
                ->with('plan')
                ->first();
            
            // Set package type
            $company->package_type = 'Trial';
            if ($subscription && $subscription->plan) {
                $company->package_type = $subscription->plan->plan_name;
            }
            
            // Set addons
            $company->addons_user = $subscription ? $subscription->addons_user_count : 0;
            $company->addons_storage = 0; // Dummy data
        }

        return $companies;
    }

    /**
     * Header kolom Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Perusahaan',
            'Email',
            'Tanggal Daftar',
            'Total Member',
            'Add-ons User',
            'Add-ons Storage (GB)',
            'Jenis Paket',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    /**
     * Format setiap baris data
     */
    public function map($company): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $company->name,
            $company->email ?? 'N/A',
            $company->created_at->format('d M Y'),
            $company->member_count,
            $company->addons_user > 0 ? $company->addons_user : '0',
            $company->addons_storage > 0 ? $company->addons_storage . ' GB' : '0 GB',
            $company->package_type,
            ucfirst($company->status),
            $company->created_at->format('d M Y H:i:s'),
        ];
    }

    /**
     * Styling Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header (baris 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'], // Biru
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Style untuk semua cell
            'A:J' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Nama Perusahaan
            'C' => 30,  // Email
            'D' => 15,  // Tanggal Daftar
            'E' => 15,  // Total Member
            'F' => 15,  // Add-ons User
            'G' => 20,  // Add-ons Storage
            'H' => 20,  // Jenis Paket
            'I' => 12,  // Status
            'J' => 20,  // Tanggal Dibuat
        ];
    }

    /**
     * Nama sheet
     */
    public function title(): string
    {
        return 'Daftar Perusahaan';
    }
}
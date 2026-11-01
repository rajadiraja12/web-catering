<?php
// app/Imports/UsersImport.php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;

    protected $successCount = 0;
    protected $failedCount = 0;
    protected $errors = [];
    protected $rowNumber = 0;

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->rowNumber++;

        // Check if username already exists
        $existingUser = User::where('username', $row['username'])->first();
        if ($existingUser) {
            $this->failedCount++;
            $this->errors[] = "Username {$row['username']} sudah terdaftar (baris " . 
                             ($this->rowNumber + 1) . ")";
            return null;
        }

        // Check if HP already exists
        $existingHp = User::where('no_hp', $row['no_hp'])->first();
        if ($existingHp) {
            $this->failedCount++;
            $this->errors[] = "No HP {$row['no_hp']} sudah terdaftar (baris " . 
                             ($this->rowNumber + 1) . ")";
            return null;
        }

        // Validasi format kelas
        if (!isset($row['kelas']) || empty($row['kelas'])) {
            $this->failedCount++;
            $this->errors[] = "Kelas tidak boleh kosong (baris " . 
                             ($this->rowNumber + 1) . ")";
            return null;
        }

        $kelasParts = explode('-', $row['kelas']);
        if (count($kelasParts) != 2) {
            $this->failedCount++;
            $this->errors[] = "Format kelas '{$row['kelas']}' tidak valid (baris " . 
                             ($this->rowNumber + 1) . 
                             "). Gunakan format: 'kelas-nama_kelas' (contoh: 1-Alpukat)";
            return null;
        }

        // Validasi role hanya customer
        $role = $row['role'] ?? 'customer';
        if (!in_array($role, ['customer'])) {
            $this->failedCount++;
            $this->errors[] = "Role hanya boleh 'customer' untuk import (baris " . 
                             ($this->rowNumber + 1) . ")";
            return null;
        }

        // Validasi status
        $status = $row['status'] ?? 'Active';
        if (!in_array($status, ['Active', 'Non Aktif'])) {
            $status = 'Active';
        }

        // Validasi hutang_limit
        $hutangLimit = $row['hutang_limit'] ?? 0;
        if (!is_numeric($hutangLimit) || $hutangLimit < 0) {
            $hutangLimit = 0;
        }

        // Hash password
        $password = isset($row['password']) && !empty($row['password']) 
            ? $row['password'] 
            : 'password123';

        $this->successCount++;
        
        return new User([
            'username' => $row['username'],
            'name' => $row['name'],
            'kelas' => $row['kelas'],
            'no_hp' => $row['no_hp'],
            'alergi' => $row['alergi'] ?? '-',
            'password' => Hash::make($password),
            'role' => 'customer', // Force role customer
            'status' => $status,
            'hutang_limit' => (int) $hutangLimit,
            'hutang_used' => 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'kelas' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|in:customer',
            'status' => 'nullable|in:Active,Non Aktif',
            'hutang_limit' => 'nullable|numeric|min:0',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->failedCount++;
            $this->errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getFailedCount()
    {
        return $this->failedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
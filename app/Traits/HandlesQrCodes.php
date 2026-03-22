<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

trait HandlesQrCodes
{
    protected function generateFoundItemQrCode(string $referenceCode): string
    {
        $directory = 'qrcodes';
        $fileName = $referenceCode . '.svg';
        $filePath = $directory . '/' . $fileName;

        $qrContent = url('/api/scan/' . $referenceCode);

        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($qrContent);

        Storage::disk('public')->put($filePath, $qrSvg);

        return $filePath;
    }

    protected function replaceFoundItemQrCode(?string $oldPath, string $referenceCode): string
    {
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $this->generateFoundItemQrCode($referenceCode);
    }

    protected function deleteQrCode(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
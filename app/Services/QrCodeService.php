<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    /**
     * Generate QR code for wallet address
     *
     * @param string $walletAddress
     * @param string $network
     * @return string Base64 encoded PNG data
     */
    public function generateWalletQrCode(string $walletAddress, string $network): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($walletAddress)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(200)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        return base64_encode($result->getString());
    }

    /**
     * Generate QR code with network label
     *
     * @param string $walletAddress
     * @param string $network
     * @return string Base64 encoded PNG data
     */
    public function generateWalletQrCodeWithLabel(string $walletAddress, string $network): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($walletAddress)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(200)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->labelText($network . ' Wallet')
            ->labelFontPath(public_path('assets/fonts/roboto.ttf'))
            ->labelFontSize(12)
            ->build();

        return base64_encode($result->getString());
    }
}

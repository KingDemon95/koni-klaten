<?php

namespace App\Support\QrCode;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use InvalidArgumentException;

final class QrCodeSvg
{
    public static function render(string $content): string
    {
        if ($content === '') {
            throw new InvalidArgumentException(
                'Konten QR Code tidak boleh kosong.'
            );
        }

        $renderer = new ImageRenderer(
            new RendererStyle(420, 4),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($content);
    }
}

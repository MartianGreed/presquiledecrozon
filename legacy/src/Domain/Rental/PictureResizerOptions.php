<?php

namespace App\Domain\Rental;

/**
 * @phpstan-type ResizeOption array{h: int, w: int, crop: bool}|array<string, mixed>
 */
final class PictureResizerOptions
{
    /**
     * @return ResizeOption
     */
    public static function thumbnail(): array
    {
        return [
            'h' => 150,
            'w' => 150,
            'crop' => true,
        ];
    }

    /**
     * @return ResizeOption
     */
    public static function listing(): array
    {
        return [
            'h' => 290,
            'w' => 270,
            'crop' => true,
        ];
    }

    /**
     * @return ResizeOption
     */
    public static function lightbox(): array
    {
        return [
            'h' => 250,
            'w' => 350,
            'crop' => true,
        ];
    }

    /**
     * @return ResizeOption
     */
    public static function cover(): array
    {
        return [
            'h' => 600,
            'w' => 600,
            'crop' => true,
        ];
    }

    /**
     * @return array<ResizeOption>
     */
    public static function getOptions(): array
    {
        return [
            self::listing(),
            self::lightbox(),
            self::thumbnail(),
        ];
    }
}

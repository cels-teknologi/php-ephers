<?php

namespace Ephers\Helpers;

use Mdanter\Ecc\Curves\CurveFactory;
use Mdanter\Ecc\Curves\SecgCurve;
use Mdanter\Ecc\Primitives\PointInterface;

final class Secp256k1
{
    public static function pointToHex(
        PointInterface $point,
        bool $compressed
    ): BinaryString {
        $prefix = '0x04';
        if ($compressed) {
            $prefix = \gmp_cmp(\gmp_and($point->getY(), 1), 0) === 0
                ? '0x02'  // Y is even
                : '0x03'; // Y is odd
        }
        $x = \gmp_strval($point->getX(), 16);
        return BinaryString::fromHex("{$prefix}{$x}" . (
            $compressed ? '' : \gmp_strval($point->getY(), 16)
        ));
    }

    public static function hexToPoint(string $hex): PointInterface
    {
        $g = CurveFactory::getGeneratorByName(SecgCurve::NAME_SECP_256K1);
        $mode = \substr($hex, 0, 4);
        $rest = \substr($hex, 4);
        $coords = match ($mode) {
            '0x02', '0x03' => [
                \gmp_init($rest, 16),
                $g->getCurve()->recoverYfromX(
                    $mode === '0x03',
                    \gmp_init($rest, 16),
                ),
            ],
            '0x04' => [
                \gmp_init(\substr($rest, 0, 64), 16),
                \gmp_init(\substr($rest, 64), 16),
            ],
            default => null, //[\gmp_init(0, 10), \gmp_init(0, 10)],
        };
        if (!$coords) {
            throw new \Exception($hex);
        }
        return $g->getPublicKeyFrom(...$coords)->getPoint();
    }
}

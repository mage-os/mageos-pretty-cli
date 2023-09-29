<?php
declare(strict_types=1);

namespace MageOS\PrettyCli\Color;

class ColorGradient implements ColorInterface
{
    /**
     * @var array<ColorInterface>
     */
    private array $colors = [];
    public function __construct(ColorInterface $from, ColorInterface ...$to)
    {
        $this->colors = [$from, ...$to];
    }

    public function getColors(int $steps): iterable
    {
        $stepsPerColor = (int)ceil($steps / (count($this->colors) - 1));
        $remainingSteps = $steps;
        $gradient = [];
        for ($i = 0; $i < count($this->colors) - 1; ++$i) {
            array_pop($gradient);
            $currentSteps   = min($remainingSteps, $stepsPerColor + 1);
            $gradient       = array_merge(
                $gradient,
                self::generateGradient(
                    $this->colors[$i]->toRgb(),
                    $this->colors[$i + 1]->toRgb(),
                    $currentSteps
                )
            );
            $remainingSteps -= $stepsPerColor;
        }
        return $gradient;
    }

    // . . . . . . . . . . . .
    // R         G        (B)B

    public function toColorString(): string
    {
        throw new \BadMethodCallException('not implemented, use getColors() iterator instead');
    }

    public function toRgb(): array
    {
        throw new \BadMethodCallException('not implemented, use getColors() iterator instead');
    }

    /**
     * @param $colorA
     * @param $colorB
     * @param $steps
     * @return array<ColorInterface>
     */
    private static function generateGradient($colorA, $colorB, $steps): array {
        $gradient = [];
        for ($i = 0; $i < $steps; $i++) {
            $r = $colorA[0] + $i * ($colorB[0] - $colorA[0]) / ($steps - 1);
            $g = $colorA[1] + $i * ($colorB[1] - $colorA[1]) / ($steps - 1);
            $b = $colorA[2] + $i * ($colorB[2] - $colorA[2]) / ($steps - 1);
            $gradient[] = HexColor::fromRgb((int)$r, (int)$g, (int)$b);
        }
        return $gradient;
    }

}

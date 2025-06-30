<?php



namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class StringToTimeTransformer implements DataTransformerInterface
{
    public function transform($value): ?string
    {
        return $value instanceof \DateTimeInterface ? $value->format('H:i:s') : '';
    }

    public function reverseTransform($value): ?\DateTimeInterface
    {
        return new \DateTime($value);
    }
}


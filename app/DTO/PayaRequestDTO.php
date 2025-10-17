<?php

namespace App\DTO;

class PayaRequestDTO
{
    public float $price;
    public string $fromShebaNumber;
    public string $toShebaNumber;
    public ?string $note;

    public function __construct(float $price, string $fromShebaNumber, string $toShebaNumber, ?string $note = null)
    {
        $this->price = $price;
        $this->fromShebaNumber = $fromShebaNumber;
        $this->toShebaNumber = $toShebaNumber;
        $this->note = $note;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            price: (float) $data['price'],
            fromShebaNumber: $data['fromShebaNumber'],
            toShebaNumber: $data['ToShebaNumber'],
            note: $data['note'] ?? null
        );
    }

    public function validate(): void
    {
        if (!preg_match('/^IR[0-9]{24}$/', $this->fromShebaNumber)) {
            throw new \InvalidArgumentException('Invalid fromShebaNumber format', 400);
        }
        if (!preg_match('/^IR[0-9]{24}$/', $this->toShebaNumber)) {
            throw new \InvalidArgumentException('Invalid toShebaNumber format', 400);
        }
        if ($this->price < 1) {
            throw new \InvalidArgumentException('Price must be at least 1', 400);
        }
    }
}

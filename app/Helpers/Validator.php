<?php
declare(strict_types=1);

namespace App\Helpers;

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label): self
    {
        if (empty(trim($this->data[$field] ?? ''))) {
            $this->errors[$field] = "{$label} wajib diisi.";
        }
        return $this;
    }

    public function email(string $field, string $label): self
    {
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} tidak valid.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        $value = trim($this->data[$field] ?? '');
        if (mb_strlen($value) > $max) {
            $this->errors[$field] = "{$label} maksimal {$max} karakter.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return array_values($this->errors)[0] ?? null;
    }
}
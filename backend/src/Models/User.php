<?php

declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

final class User implements JsonSerializable
{
    public function __construct(
        public string $name,
        public string $surname,
        public string $email,
        public ?int $id = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'surname' => $this->surname,
            'email'   => $this->email,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

<?php

namespace Nilnice\Translate\Contracts;

interface Translation
{
    public function translate(string $text, string $translator): array;
}
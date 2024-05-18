<?php

namespace Library\Input;

use Laminas\Filter\FilterInterface;
use Laminas\Validator\ValidatorInterface;

interface InputInterface
{
    public function attachValidator(ValidatorInterface $validator): self;
    public function attachFilter(FilterInterface $filter): self;
    public function isValid(mixed $input): bool;
    public function getValue(): mixed;
    public function getName(): string;
    public function getMessages(): array;
}

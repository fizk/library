<?php

namespace Library\Input;

interface InputChainInterface
{
    public function addInput(InputInterface $input, mixed $value): self;

    public function isValid(): bool;

    public function getValues(): array;

    public function getMessages(): array;
}

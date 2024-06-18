<?php

namespace Library\Input;

use Laminas\Filter\FilterInterface;
use Laminas\Validator\ValidatorInterface;

class Input implements InputInterface
{
    private array $validators = [];
    private array $filters = [];
    private mixed $value;
    private array $messages = [];
    private ?bool $valid = null;

    public function __construct(private string $name, private $allowEmpty = false)
    {
    }

    public function attachValidator(ValidatorInterface $validator): self
    {
        $this->validators[] = $validator;
        return $this;
    }

    public function attachFilter(FilterInterface $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function isValid(mixed $input): bool
    {
        if ($this->valid !== null) {
            return $this->valid;
        }

        // Run all filters
        $this->value = array_reduce($this->filters, function (mixed $previous, FilterInterface $current) {
            return $current->filter($previous);
        }, $input);

        // If empty is allowed
        if ($this->value === null && $this->allowEmpty === true) {
            return $this->valid = true;
        }

        // Run all validators
        $validationResults = array_map(function (ValidatorInterface $validator) {
            return $validator->isValid($this->value);
        }, $this->validators);

        // Collect error messages
        $this->messages = array_reduce($this->validators, function (array $previous, ValidatorInterface $current) {
            $messages = $current->getMessages();
            return count($messages) > 0 ? [...$previous, $messages] : $previous;
        }, []);

        return $this->valid = !in_array(false, $validationResults);
    }

    public function getValue(): mixed
    {
        if ($this->valid === null) {
            throw new InputException("::isValid() has not been called");
        }

        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessages(): array
    {
        if ($this->valid === null) {
            throw new InputException("::isValid() has not been called");
        }

        return $this->messages;
    }
}

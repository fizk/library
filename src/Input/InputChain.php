<?php

namespace Library\Input;

class InputChain implements InputChainInterface
{
    private array $input = [];
    private array $messages = [];
    private array $values = [];
    private ?bool $valid = null;

    public function addInput(InputInterface $input, mixed $value): self
    {
        $this->input[] = [$input, $value];
        return $this;
    }

    public function isValid(): bool
    {
        if ($this->valid !== null) {
            return $this->valid;
        }

        // Validate chain
        $validationResults = array_map(function ($item) {
            [$input, $value] = $item;
            return $input->isValid($value);
        }, $this->input);

        // Extract values
        $this->values = array_reduce($this->input, function ($previous, $next) {
            [$input, ] = $next;
            $previous[$input->getName()] = $input->getValue();
            return $previous;
        }, []);

        // Extract error messages
        $this->messages = array_reduce($this->input, function ($previous, $next) {
            [$input, ] = $next;
            $messages = $input->getMessages();
            $previous = count($messages) > 0
                ? [...$previous, ...[$input->getName() => $messages]]
                : $previous;
            return $previous;
        }, []);

        return $this->valid = !in_array(false, $validationResults);
    }

    public function getValues(): array
    {
        if ($this->valid === null) {
            throw new InputException('::isValid has not been called');
        }
        return $this->values;
    }

    public function getMessages(): array
    {
        if ($this->valid === null) {
            throw new InputException('::isValid has not been called');
        }
        return $this->messages;
    }
}
